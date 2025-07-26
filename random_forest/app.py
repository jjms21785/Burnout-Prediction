from flask import Flask, render_template, request, jsonify
import joblib
import numpy as np
import pandas as pd

app = Flask(__name__)

# Load the trained model
model = joblib.load("olbi_model.joblib")

# Label mapping to match PHP controller/model
label_reverse_map = {0: "Low", 1: "Disengaged", 2: "Exhausted", 3: "High"}

# Exhaustion: Q2, Q4, Q6, Q8, Q10, Q12, Q14, Q16 (indices 1,3,5,7,9,11,13,15)
exhaustion_indices = [1, 3, 5, 7, 9, 11, 13, 15]
# Disengagement: Q1, Q3, Q5, Q7, Q9, Q11, Q13, Q15 (indices 0,2,4,6,8,10,12,14)
disengagement_indices = [0, 2, 4, 6, 8, 10, 12, 14]

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/predict', methods=['POST'])
def predict():
    try:
        if request.is_json:
            data = request.get_json()
            features = data['input']
            # Use features directly since PHP controller already handles reverse scoring
            features_array = np.array([features])
            prediction = model.predict(features_array)[0]
            proba = model.predict_proba(features_array)[0]
            predicted_label = label_reverse_map.get(prediction, str(prediction))
            total_score = sum(features)
            exhaustion = sum([features[i] for i in exhaustion_indices])
            disengagement = sum([features[i] for i in disengagement_indices])
            return jsonify({
                'label': predicted_label,
                'confidence': proba.tolist(),
                'total_score': total_score,
                'exhaustion': exhaustion,
                'disengagement': disengagement
            })
        # Web form (not used by PHP)
        data = request.form
        responses = []
        for i in range(1, 17):
            key = f"Q{i}"
            val = int(data[key])
            responses.append(val)
        features = responses
        features_array = np.array([features])
        prediction = model.predict(features_array)[0]
        proba = model.predict_proba(features_array)[0]
        predicted_label = label_reverse_map.get(prediction, str(prediction))
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
