import pandas as pd
import joblib
import numpy as np
import json

# -----------------------------
# Load trained model
# -----------------------------
model_path = 'random_forest/random_forest_burnout_model.pkl'
rf_model = joblib.load(model_path)
print(f"Loaded model: {model_path}")

# -----------------------------
# Define response (Q1–Q30)
# -----------------------------
single_response = {
    'Q1': 4, 'Q2': 3, 'Q3': 3, 'Q4': 2, 'Q5': 3, 'Q6': 4, 'Q7': 3, 'Q8': 2, 'Q9': 2, 'Q10': 3,
    'Q11': 2, 'Q12': 1, 'Q13': 2, 'Q14': 2, 'Q15': 4, 'Q16': 4, 'Q17': 3, 'Q18': 3, 'Q19': 3,
    'Q20': 4, 'Q21': 3, 'Q22': 3, 'Q23': 2, 'Q24': 4, 'Q25': 4, 'Q26': 3, 'Q27': 4, 'Q28': 4,
    'Q29': 3, 'Q30': 4
}

features = [f'Q{i}' for i in range(1, 31)]
single_df = pd.DataFrame([single_response], columns=features)

# -----------------------------
# Predict category (optional)
# -----------------------------
predicted_category = rf_model.predict(single_df)[0]
probabilities = rf_model.predict_proba(single_df)[0]

category_labels = {
    0: "Non-Burnout",
    1: "Disengaged",
    2: "Exhausted",
    3: "BURNOUT"
}

# Predicted Result interpretation
predicted_result_map = {
    0: "Low Exhaustion and Low Disengagement",
    1: "Low Exhaustion and High Disengagement",
    2: "High Exhaustion and Low Disengagement",
    3: "High Exhaustion and High Disengagement"
}

predicted_result = predicted_result_map.get(predicted_category, "Unknown")

print("\n📋 PREDICTED RESULT")
print("-----------------------------")
print(f"Predicted Category: {category_labels[predicted_category]} → {predicted_result}")

# =========================================================
# RESPONSE RESULT: SCORING CALCULATIONS
# =========================================================

# --- Academic Performance (Q1–Q2)
academic_score = single_response['Q1'] + single_response['Q2']
academic_label = "D1: Academic Performance - Good/High" if academic_score >= 5 else "D2: Academic Performance - Struggling/Low"

# --- Stress (Q3–Q6)
stress_items = {'Q3': 1, 'Q4': -1, 'Q5': -1, 'Q6': 1}
stress_score = 0
for q, direction in stress_items.items():
    val = single_response[q]
    val = 4 - val if direction == -1 else val
    stress_score += val

stress_label = (
    "D3: Stress Level - Low" if stress_score <= 4 else
    "D4: Stress Level - Moderate" if 5 <= stress_score <= 8 else
    "D5: Stress Level - High"
)

# --- Sleep Quality (Q7–Q14)
sleep_items = [f"Q{i}" for i in range(7, 15)]
sleep_score = sum(single_response[q] for q in sleep_items)
sleep_label = (
    "D6: Sleep Quality - Good" if sleep_score >= 24 else
    "D7: Sleep Quality - Fair/Moderate" if 16 <= sleep_score < 24 else
    "D8: Sleep Quality - Poor"
)

# --- Exhaustion and Disengagement (Q15–Q30)
exhaustion_items = ['Q16','Q17','Q20','Q21','Q23','Q25','Q28','Q29']
disengagement_items = ['Q15','Q18','Q19','Q22','Q24','Q26','Q27','Q30']

exhaustion_score = np.mean([single_response[q] for q in exhaustion_items])
disengagement_score = np.mean([single_response[q] for q in disengagement_items])

# Cutoffs
ex_high = exhaustion_score >= 2.25
dis_high = disengagement_score >= 2.1

# Interpretations (A/B format)
exhaustion_label = "A2: High Exhaustion interpretation" if ex_high else "A1: Low Exhaustion interpretation"
disengagement_label = "B2: High Disengagement interpretation" if dis_high else "B1: Low Disengagement interpretation"

# =========================================================
# Normalize for Bar Graph (%)
# =========================================================
bar_data = {
    "Academic Performance": round((academic_score / 8) * 100, 2),
    "Stress": round((stress_score / 16) * 100, 2),
    "Sleep": round((sleep_score / 32) * 100, 2),
    "Exhaustion": round((exhaustion_score / 4) * 100, 2),
    "Disengagement": round((disengagement_score / 4) * 100, 2),
}

# =========================================================
# COMPILE RESULTS (Laravel JSON)
# =========================================================
result_payload = {
    "PredictedResult": {
        "predicted_category": int(predicted_category),
        "label": category_labels[predicted_category],
        "interpretation": predicted_result
    },
    "ResponseResult": {
        "Interpretations": {
            "Academic": academic_label,
            "Stress": stress_label,
            "Sleep": sleep_label,
            "Exhaustion": exhaustion_label,
            "Disengagement": disengagement_label
        }
    },
    "BarGraph": bar_data
}

# =========================================================
# Display Results
# =========================================================
print("\n📊 RESPONSE RESULT")
print("-----------------------------")
for k, v in result_payload["ResponseResult"]["Interpretations"].items():
    print(f"{k}: {v}")

print("\n📈 BAR GRAPH DATA (Percentages)")
for k, v in bar_data.items():
    print(f"  {k}: {v}%")

json_output = json.dumps(result_payload, indent=4)
print("\n📦 JSON Output for Laravel:")
print(json_output)
