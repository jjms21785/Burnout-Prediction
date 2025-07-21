from flask import Flask, request, jsonify
import joblib
import pandas as pd
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Load the trained model
model = joblib.load("burnout_model.pkl")

# Indices for MBI-HSS subscales (0-based)
EE_INDICES = [0, 1, 3, 4, 5, 8]
DP_INDICES = [6, 7, 21]
PA_INDICES = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19]

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json

        # Validate input
        questions = [f"Q{i}" for i in range(1, 23)]
        if not all(k in data for k in questions):
            return jsonify({'error': 'Missing questions'}), 400

        # Convert to DataFrame
        df = pd.DataFrame([data])

        # Make prediction
        prediction = model.predict(df)[0]
        prediction_lower = prediction.lower()

        # Prediction confidence
        if hasattr(model, "predict_proba"):
            proba = model.predict_proba(df)[0]
            class_index = list(model.classes_).index(prediction)
            confidence = round(proba[class_index] * 100, 2)
        else:
            confidence = None

        # MBI-HSS Score Breakdown
        answers = [data[f"Q{i}"] for i in range(1, 23)]
        ee_score = sum(answers[i] for i in EE_INDICES)
        dp_score = sum(answers[i] for i in DP_INDICES)
        pa_score = sum(answers[i] for i in PA_INDICES)

        return jsonify({
            'prediction': prediction_lower,
            'confidence': confidence,
            'mbi_hss': {
                'emotional_exhaustion': ee_score,
                'depersonalization': dp_score,
                'personal_accomplishment': pa_score
            }
        })

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, port=5000)
