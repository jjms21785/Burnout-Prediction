from flask import Flask, render_template, request, jsonify
import joblib
import numpy as np
import pandas as pd
from sklearn.metrics import accuracy_score

app = Flask(__name__)

# Load the trained model - try both possible filenames
try:
    model = joblib.load("random_forest_burnout_model.pkl")
except FileNotFoundError:
    try:
        model = joblib.load("olbi_model.pkl")
    except FileNotFoundError:
        raise FileNotFoundError("Model file not found. Please ensure random_forest_burnout_model.pkl or olbi_model.pkl exists.")

# Load test dataset and calculate actual accuracy
try:
    test_df = pd.read_csv("olbi_dataset.csv")
    # Assume the last column is the label, first 16 columns are answers
    X_test = test_df.iloc[:, :16].values
    y_test = test_df.iloc[:, -1].values
    y_pred = model.predict(X_test)
    actual_accuracy = accuracy_score(y_test, y_pred)
except Exception as e:
    actual_accuracy = None

# Define mappings used during training
# Negatively worded items (to be reverse scored) - OLBI questions only:
# For the 16 OLBI items in order (D1-D8, E1-E8):
# D2 (Q18): index 1, D3 (Q19): index 2, D5 (Q24): index 4, D6 (Q26): index 5
# E2 (Q17): index 9, E3 (Q20): index 10, E5 (Q23): index 12, E7 (Q28): index 14
reverse_scored_indices = [1, 2, 4, 5, 9, 10, 12, 14]
label_reverse_map = {0: "Low", 1: "Moderate", 2: "High"}

# Category labels mapping (matching basic api flask.py)
category_labels = {
    0: "Non-Burnout",
    1: "Disengaged",
    2: "Exhausted",
    3: "BURNOUT"
}

# Indices for breakdown (0-based within 16 OLBI items)
# Exhaustion items: E1-E8 (indices 8-15)
exhaustion_indices = [8, 9, 10, 11, 12, 13, 14, 15]
# Disengagement items: D1-D8 (indices 0-7)
disengagement_indices = [0, 1, 2, 3, 4, 5, 6, 7]

def reverse_score(val):
    return 3 - val

@app.route('/')
def home():
    return render_template('index.html')  # your assessment form

@app.route('/predict', methods=['POST'])
def predict():
    """
    Expects JSON payload with all 30 answers as array.
    Example: { "all_answers": [4, 3, 2, 1, ..., 4] }
    Returns: PredictedResult, ResponseResult (with codes), BarGraph
    """
    try:
        data = request.get_json() or {}
        
        # Get all 30 answers
        if 'all_answers' in data:
            all_responses = data['all_answers']
            if len(all_responses) != 30:
                return jsonify({'error': 'Expected 30 answers, got ' + str(len(all_responses))}), 400
        else:
            return jsonify({'error': 'Missing all_answers field'}), 400
        
        # Convert 0-indexed array to Q1-Q30 dictionary format
        responses_dict = {}
        for i in range(30):
            responses_dict[f'Q{i+1}'] = all_responses[i]
        
        # --- PREDICTION ---
        # The model was trained on ALL 30 features (Q1-Q30), so we need to send all 30
        # Create features array with all 30 answers in order (Q1, Q2, ..., Q30)
        # The model expects the features in the same order as training: Q1, Q2, ..., Q30
        features_array = np.array([all_responses])  # All 30 answers as features
        
        # Run model prediction with all 30 features
        predicted_category = int(model.predict(features_array)[0])
        predicted_label = category_labels.get(predicted_category, "Unknown")
        
        # Extract OLBI responses for ResponseResult calculations (separate from prediction)
        # Map to OLBI order: Disengagement (Q15, Q18, Q19, Q22, Q24, Q26, Q27, Q30), 
        #                    Exhaustion (Q16, Q17, Q20, Q21, Q23, Q25, Q28, Q29)
        olbi_mapping = {
            'disengagement': [14, 17, 18, 21, 23, 25, 26, 29],  # Q15, Q18, Q19, Q22, Q24, Q26, Q27, Q30
            'exhaustion': [15, 16, 19, 20, 22, 24, 27, 28]     # Q16, Q17, Q20, Q21, Q23, Q25, Q28, Q29
        }
        
        # Extract OLBI values in model order (Disengagement then Exhaustion) for ResponseResult
        olbi_responses = []
        for idx in olbi_mapping['disengagement']:
            olbi_responses.append(all_responses[idx])
        for idx in olbi_mapping['exhaustion']:
            olbi_responses.append(all_responses[idx])
        
        # Apply reverse scoring to OLBI responses (for ResponseResult calculations only)
        scored_responses = []
        for i in range(16):
            val = olbi_responses[i]
            if i in reverse_scored_indices:
                val = reverse_score(val)
            scored_responses.append(val)
        
        # --- RESPONSE RESULT ---
        # Academic Performance (Q1-Q2)
        academic_score = responses_dict['Q1'] + responses_dict['Q2']
        academic_code = "D1" if academic_score >= 5 else "D2"
        academic_label = f"{academic_code}: Academic Performance - {'Good/High' if academic_code == 'D1' else 'Struggling/Low'}"
        
        # Stress (Q3-Q6)
        # Q4 and Q5 need reverse scoring
        stress_items = {
            'Q3': 1,   # Direct
            'Q4': -1,  # Reverse
            'Q5': -1,  # Reverse
            'Q6': 1    # Direct
        }
        stress_score = sum((4 - responses_dict[q] if direction == -1 else responses_dict[q]) 
                          for q, direction in stress_items.items())
        if stress_score <= 4:
            stress_code = "D3"
            stress_label = "D3: Stress Level - Low"
        elif 5 <= stress_score <= 8:
            stress_code = "D4"
            stress_label = "D4: Stress Level - Moderate"
        else:
            stress_code = "D5"
            stress_label = "D5: Stress Level - High"
        
        # Sleep (Q7-Q14)
        sleep_items = [f"Q{i}" for i in range(7, 15)]
        sleep_score = sum(responses_dict[q] for q in sleep_items)
        if sleep_score >= 24:
            sleep_code = "D6"
            sleep_label = "D6: Sleep Quality - Good"
        elif 16 <= sleep_score < 24:
            sleep_code = "D7"
            sleep_label = "D7: Sleep Quality - Fair/Moderate"
        else:
            sleep_code = "D8"
            sleep_label = "D8: Sleep Quality - Poor"
        
        # Exhaustion & Disengagement
        exhaustion_items = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29']
        disengagement_items = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30']
        
        exhaustion_score_sum = sum(responses_dict[q] for q in exhaustion_items)
        disengagement_score_sum = sum(responses_dict[q] for q in disengagement_items)
        
        exhaustion_score = exhaustion_score_sum / len(exhaustion_items)
        disengagement_score = disengagement_score_sum / len(disengagement_items)
        
        ex_high = exhaustion_score >= 2.25
        dis_high = disengagement_score >= 2.1
        
        exhaustion_code = "A2" if ex_high else "A1"
        exhaustion_label = f"{exhaustion_code}: {'High' if ex_high else 'Low'} Exhaustion interpretation"
        
        disengagement_code = "B2" if dis_high else "B1"
        disengagement_label = f"{disengagement_code}: {'High' if dis_high else 'Low'} Disengagement interpretation"
        
        # --- Bar Graph (%) ---
        bar_data = {
            "Academic Performance": round((academic_score / 8) * 100, 2),
            "Stress": round((stress_score / 16) * 100, 2),
            "Sleep": round((sleep_score / 32) * 100, 2),
            "Exhaustion": round((exhaustion_score / 4) * 100, 2),
            "Disengagement": round((disengagement_score / 4) * 100, 2),
        }
        
        # --- Prepare JSON for Laravel (matching basic api flask.py format) ---
        result_payload = {
            "PredictedResult": {
                "predicted_category": predicted_category,
                "label": predicted_label,
                "interpretation": f"{predicted_category}: {predicted_label}"
            },
            "ResponseResult": {
                "Interpretations": {
                    "Academic": academic_label,
                    "Stress": stress_label,
                    "Sleep": sleep_label,
                    "Exhaustion": exhaustion_label,
                    "Disengagement": disengagement_label
                },
                "Codes": {
                    "Academic": academic_code,
                    "Stress": stress_code,
                    "Sleep": sleep_code,
                    "Exhaustion": exhaustion_code,
                    "Disengagement": disengagement_code
                }
            },
            "BarGraph": bar_data
        }
        
        return jsonify(result_payload)
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
