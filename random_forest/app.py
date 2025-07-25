from flask import Flask, render_template, request, jsonify
import joblib
import numpy as np
import pandas as pd
from sklearn.metrics import accuracy_score

app = Flask(__name__)

# Load the trained model
model = joblib.load("olbi_model.pkl")

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
# Negatively worded items (to be reverse scored):
# Q2: index 1
# Q3: index 2
# Q5: index 4
# Q6: index 5
# Q9: index 8
# Q10: index 9
# Q12: index 11
# Q14: index 13
reverse_scored_indices = [1, 2, 4, 5, 8, 9, 11, 13]
label_reverse_map = {0: "Low", 1: "Moderate", 2: "High"}

# Indices for breakdown (0-based)
# Exhaustion items: Q9-Q16 (indices 8-15)
exhaustion_indices = [8, 9, 10, 11, 12, 13, 14, 15]
# Disengagement items: Q1-Q8 (indices 0-7)
disengagement_indices = [0, 1, 2, 3, 4, 5, 6, 7]

def reverse_score(val):
    return 3 - val

@app.route('/')
def home():
    return render_template('index.html')  # your assessment form

@app.route('/predict', methods=['POST'])
def predict():
    try:
        # JSON API for Laravel integration
        if request.is_json:
            data = request.get_json()
            features = data['input']
            # Reverse scoring logic
            responses = []
            for i in range(16):
                val = features[i]
                if i in reverse_scored_indices:
                    val = reverse_score(val)
                responses.append(val)
            features_array = np.array([responses])
            prediction = model.predict(features_array)[0]
            proba = model.predict_proba(features_array)[0]
            predicted_label = label_reverse_map[prediction]
            accuracy = actual_accuracy if actual_accuracy is not None else None
            total_score = sum(responses)
            exhaustion = sum([responses[i] for i in exhaustion_indices])
            disengagement = sum([responses[i] for i in disengagement_indices])
            return jsonify({
                'label': predicted_label,
                'confidence': proba.tolist(),
                'accuracy': accuracy,
                'total_score': total_score,
                'exhaustion': exhaustion,
                'disengagement': disengagement
            })
        # Web form 
        data = request.form
        responses = []
        for i in range(1, 17):
            key = f"Q{i}"
            val = int(data[key])
            if (i-1) in reverse_scored_indices:
                val = reverse_score(val)
            responses.append(val)
        features = responses
        features_array = np.array([features])
        prediction = model.predict(features_array)[0]
        proba = model.predict_proba(features_array)[0]
        predicted_label = label_reverse_map[prediction]
        confidence = round(np.max(proba) * 100, 2)
        total_score = sum(responses)
        exhaustion = sum([responses[i] for i in exhaustion_indices])
        disengagement = sum([responses[i] for i in disengagement_indices])
        return render_template(
            'result.html',
            predicted_label=predicted_label,
            confidence=confidence,
            total_score=total_score,
            answers=responses,
            exhaustion=exhaustion,
            disengagement=disengagement
        )
    except Exception as e:
        if request.is_json:
            return jsonify({'error': str(e)}), 500
        return f"Error occurred: {str(e)}"

if __name__ == '__main__':
    app.run(debug=True)
