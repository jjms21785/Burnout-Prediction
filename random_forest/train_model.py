# ============================================
# train_random_forest.py
# ============================================

import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
import joblib

# -----------------------------
# Load dataset
# -----------------------------
file_path = 'random_forest/dataset/training_data.csv'
df = pd.read_csv(file_path)

print(f"✅ Loaded dataset: {file_path}")
print(f"Total samples: {len(df)}")

# -----------------------------
# Identify label column
# -----------------------------
if 'Category' in df.columns:
    target_col = 'Category'
elif 'Burnout_Category' in df.columns:
    target_col = 'Burnout_Category'
else:
    raise ValueError("No category column found! (Expected 'Category' or 'Burnout_Category')")

# -----------------------------
# Feature selection
# -----------------------------
# Use only Q1–Q30 as features
features = [f'Q{i}' for i in range(1, 31)]

# Check that all required columns exist
missing = [col for col in features if col not in df.columns]
if missing:
    raise ValueError(f"Missing feature columns in CSV: {missing}")

X = df[features]
y = df[target_col]

# -----------------------------
# Split into train/test
# -----------------------------
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

print(f"\n📊 Train size: {len(X_train)} | Test size: {len(X_test)}")

# -----------------------------
# Train Random Forest model
# -----------------------------
print("\n🌲 Training Random Forest model...")
rf = RandomForestClassifier(
    n_estimators=200,
    max_depth=None,
    min_samples_split=2,
    min_samples_leaf=1,
    random_state=42,
    n_jobs=-1
)
rf.fit(X_train, y_train)

# -----------------------------
# Evaluate model
# -----------------------------
y_pred = rf.predict(X_test)

print("\n✅ Model evaluation:")
print("Accuracy:", round(accuracy_score(y_test, y_pred), 4))
print("\nClassification Report:\n", classification_report(y_test, y_pred))
print("\nConfusion Matrix:\n", confusion_matrix(y_test, y_pred))

# -----------------------------
# Feature importances
# -----------------------------
importances = pd.Series(rf.feature_importances_, index=features).sort_values(ascending=False)
print("\n🔥 Top 10 important features:")
print(importances.head(10))

# -----------------------------
# Save trained model
# -----------------------------
model_filename = 'random_forest/random_forest_burnout_model.pkl'
joblib.dump(rf, model_filename)
print(f"\n💾 Model saved as: {model_filename}")
