from flask import Flask, request, jsonify
import joblib
import pandas as pd
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Load the trained model
model = joblib.load("random_forest_olbi_model.pkl")

# OLBI-S subscale indices (update as per your scoring key)
EXHAUSTION_INDICES = [1, 2, 8, 9, 11, 12, 13, 15]
DISENGAGEMENT_INDICES = [0, 3, 4, 5, 6, 7, 10, 14]

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        questions = [f"Q{i}" for i in range(1, 17)]
        if not all(k in data for k in questions):
            return jsonify({'error': 'Missing questions'}), 400
        df = pd.DataFrame([data])
        prediction = model.predict(df)[0]
        prediction_lower = str(prediction).lower()
        if hasattr(model, "predict_proba"):
            proba = model.predict_proba(df)[0]
            class_index = list(model.classes_).index(prediction)
            confidence = round(proba[class_index] * 100, 2)
        else:
            confidence = None
        answers = [int(data[f"Q{i}"]) for i in range(1, 17)]
        exhaustion_score = sum(answers[i] for i in EXHAUSTION_INDICES)
        disengagement_score = sum(answers[i] for i in DISENGAGEMENT_INDICES)
        return jsonify({
            'prediction': prediction_lower,
            'confidence': confidence,
            'olbi_s': {
                'exhaustion': exhaustion_score,
                'disengagement': disengagement_score
            }
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(debug=True, port=5000)
