from flask import Flask, render_template, request, jsonify
import joblib
import numpy as np

app = Flask(__name__)

try:
    model = joblib.load("random_forest_burnout_model.pkl")
except FileNotFoundError:
    raise FileNotFoundError("Model file not found. Please ensure random_forest_burnout_model.pkl exists.")

category_labels = {
    0: "Non-Burnout",
    1: "Disengaged",
    2: "Exhausted",
    3: "BURNOUT"
}

reverse_scored_indices = [1, 2, 4, 5, 9, 10, 12, 14]

def reverse_score(val):
    return 3 - val

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json() or {}
        
        if 'all_answers' not in data:
            return jsonify({'error': 'Missing all_answers field'}), 400
        
        all_responses = data['all_answers']
        if len(all_responses) != 30:
            return jsonify({'error': 'Expected 30 answers, got ' + str(len(all_responses))}), 400
        
        responses_dict = {}
        for i in range(30):
            responses_dict[f'Q{i+1}'] = all_responses[i]
        
        features_array = np.array([all_responses])
        predicted_category = int(model.predict(features_array)[0])
        predicted_label = category_labels.get(predicted_category, "Unknown")
        
        olbi_mapping = {
            'disengagement': [14, 17, 18, 21, 23, 25, 26, 29],
            'exhaustion': [15, 16, 19, 20, 22, 24, 27, 28]
        }
        
        olbi_responses = []
        for idx in olbi_mapping['disengagement']:
            olbi_responses.append(all_responses[idx])
        for idx in olbi_mapping['exhaustion']:
            olbi_responses.append(all_responses[idx])
        
        scored_responses = []
        for i in range(16):
            val = olbi_responses[i]
            if i in reverse_scored_indices:
                val = reverse_score(val)
            scored_responses.append(val)
        
        academic_score = responses_dict['Q1'] + responses_dict['Q2']
        academic_code = "D1" if academic_score >= 5 else "D2"
        academic_label = f"{academic_code}: Academic Performance - {'Good/High' if academic_code == 'D1' else 'Struggling/Low'}"
        
        stress_items = {
            'Q3': 1,
            'Q4': -1,
            'Q5': -1,
            'Q6': 1
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
        
        bar_data = {
            "Academic Performance": round((academic_score / 8) * 100, 2),
            "Stress": round((stress_score / 16) * 100, 2),
            "Sleep": round((sleep_score / 32) * 100, 2),
            "Exhaustion": round((exhaustion_score / 4) * 100, 2),
            "Disengagement": round((disengagement_score / 4) * 100, 2),
        }
        
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
