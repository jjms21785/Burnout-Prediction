from flask import Flask, render_template, request, jsonify
import joblib
import numpy as np
import pandas as pd

app = Flask(__name__)

# Load the trained model
model = joblib.load("olbi_model.pkl")

# Define mappings used during training
reverse_scored_items = ['Q1', 'Q4', 'Q7', 'Q8', 'Q11', 'Q13', 'Q15', 'Q16']
gender_map = {"Female": 0, "Male": 1}
program_map = {
    "BSA": 0, "BSBA": 1, "BSENT": 2, "BSHM": 3, "BEED": 4,
    "BSED-ENG": 5, "BSED-FIL": 6, "BSED-MATH": 7, "BA-PSYCH": 8,
    "BSCS": 9, "BSIT": 10, "BSECE": 11, "BSN": 12
}
label_reverse_map = {0: "Low", 1: "Moderate", 2: "High"}

# Indices for breakdown (0-based)
exhaustion_indices = [1, 4, 5, 9, 11, 13]  # Q2, Q5, Q6, Q10, Q12, Q14
# Q1, Q3, Q4, Q7, Q8, Q9, Q11, Q13, Q15, Q16
# Indices: 0, 2, 3, 6, 7, 8, 10, 12, 14, 15
disengagement_indices = [0, 2, 3, 6, 7, 8, 10, 12, 14, 15]

def reverse_score(val):
    return 5 - val

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
                key = f'Q{i+1}'
                if key in reverse_scored_items:
                    val = reverse_score(val)
                responses.append(val)
            age = features[16]
            gender = features[17]
            program = features[18]
            features_array = np.array([responses + [age, gender, program]])
            prediction = model.predict(features_array)[0]
            proba = model.predict_proba(features_array)[0]
            predicted_label = label_reverse_map[prediction]
            accuracy = 0.92  # Replace with your model's test accuracy if known
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
        # Web form logic (manual testing)
        data = request.form
        responses = []
        for i in range(1, 17):
            key = f"Q{i}"
            val = int(data[key])
            if key in reverse_scored_items:
                val = reverse_score(val)
            responses.append(val)
        age = int(data['Age'])
        gender = gender_map[data['Gender']]
        program = program_map[data['Program']]
        features = responses + [age, gender, program]
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
            age=age,
            gender=data['Gender'],
            program=data['Program'],
            exhaustion=exhaustion,
            disengagement=disengagement
        )
    except Exception as e:
        if request.is_json:
            return jsonify({'error': str(e)}), 500
        return f"Error occurred: {str(e)}"

if __name__ == '__main__':
    app.run(debug=True)
