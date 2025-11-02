# ============================================
# test_single_response.py
# ============================================

import pandas as pd
import joblib
import numpy as np
import matplotlib.pyplot as plt

# -----------------------------
# Load trained model
# -----------------------------
model_path = 'random_forest/random_forest_model.pkl'
rf_model = joblib.load(model_path)
print(f"✅ Loaded model: {model_path}")

# -----------------------------
# Define the single test response (Q1–Q30)
# -----------------------------
single_response = {
    'Q1': 4, 'Q2': 3, 'Q3': 4, 'Q4': 2, 'Q5': 3, 'Q6': 4, 'Q7': 1, 'Q8': 2, 'Q9': 2, 'Q10': 1,
    'Q11': 2, 'Q12': 1, 'Q13': 2, 'Q14': 2, 'Q15': 4, 'Q16': 4, 'Q17': 3, 'Q18': 3, 'Q19': 3, 'Q20': 4,
    'Q21': 3, 'Q22': 3, 'Q23': 2, 'Q24': 4, 'Q25': 4, 'Q26': 3, 'Q27': 4, 'Q28': 4, 'Q29': 3, 'Q30': 4
}

# Convert to DataFrame with same column order as training
features = [f'Q{i}' for i in range(1, 31)]
single_df = pd.DataFrame([single_response], columns=features)

# -----------------------------
# Predict category & probabilities
# -----------------------------
predicted_category = rf_model.predict(single_df)[0]
probabilities = rf_model.predict_proba(single_df)[0]

# Category labels
category_labels = {
    0: "Non-Burnout",
    1: "Disengaged",
    2: "Exhausted",
    3: "BURNOUT"
}

# -----------------------------
# Display results
# -----------------------------
print("\n📋 Single Response Prediction")
print("-----------------------------")
print(f"Predicted Category: {predicted_category} → {category_labels.get(predicted_category, 'Unknown')}")

# Map probabilities to actual classes
model_probs = {cls: prob for cls, prob in zip(rf_model.classes_, probabilities)}
print("\nPrediction Probabilities (per category):")
for i, label in category_labels.items():
    prob = model_probs.get(i, 0.0)  # Default to 0 if class wasn't trained
    print(f"  {label} ({i}): {prob:.4f}")

# Model confidence
confidence = model_probs.get(predicted_category, 0.0)
print(f"\nModel confidence: {confidence:.2%}")

# -----------------------------
# Input summary: Exhaustion & Disengagement
# -----------------------------
exhaustion_items = ['Q16','Q17','Q20','Q21','Q23','Q25','Q28','Q29']
disengagement_items = ['Q15','Q18','Q19','Q22','Q24','Q26','Q27','Q30']

exhaustion_score = single_df[exhaustion_items].mean(axis=1)[0]
disengagement_score = single_df[disengagement_items].mean(axis=1)[0]

print("\nInput Summary:")
print(f"  Exhaustion score: {exhaustion_score:.2f}")
print(f"  Disengagement score: {disengagement_score:.2f}")
print(f"  Questions responses: {list(single_response.values())}")

# -----------------------------
# Top 10 important features
# -----------------------------
feature_importances = rf_model.feature_importances_
importance_df = pd.DataFrame({
    'Feature': features,
    'Importance': feature_importances
})
importance_df = importance_df.sort_values(by='Importance', ascending=False).head(10)

print("\nTop contributing features:")
print(importance_df.to_string(index=False))

# -----------------------------
# Probability bar chart
# -----------------------------
plt.figure(figsize=(6,4))
plt.bar(category_labels.values(),
        [model_probs.get(i, 0.0) for i in range(4)],
        color='skyblue')
plt.ylabel("Probability")
plt.title("Prediction Probabilities per Category")
plt.ylim(0, 1)
plt.show()
