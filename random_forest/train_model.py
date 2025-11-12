# ============================================
# train_random_forest.py
# ============================================

import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
import joblib
import os
import matplotlib.pyplot as plt
import seaborn as sns
import numpy as np

# -----------------------------
# Load dataset
# -----------------------------
# Get the directory where this script is located
script_dir = os.path.dirname(os.path.abspath(__file__))
file_path = os.path.join(script_dir, 'dataset', 'training_data.csv')
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
cm = confusion_matrix(y_test, y_pred)
print("\nConfusion Matrix:\n", cm)

# -----------------------------
# Visualize Confusion Matrix
# -----------------------------
plt.figure(figsize=(10, 8))
# Get unique class labels for proper labeling
classes = sorted(y_test.unique())
sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', 
            xticklabels=classes, yticklabels=classes,
            cbar_kws={'label': 'Count'})
plt.title('Confusion Matrix', fontsize=16, fontweight='bold', pad=20)
plt.xlabel('Predicted Label', fontsize=12, fontweight='bold')
plt.ylabel('True Label', fontsize=12, fontweight='bold')
plt.tight_layout()

# Save confusion matrix plot
cm_path = os.path.join(script_dir, 'confusion_matrix.png')
plt.savefig(cm_path, dpi=300, bbox_inches='tight')
print(f"\n📊 Confusion matrix visualization saved: {cm_path}")
plt.close()

# -----------------------------
# Feature importances
# -----------------------------
importances = pd.Series(rf.feature_importances_, index=features).sort_values(ascending=False)
print("\n🔥 Top 10 important features:")
print(importances.head(10))

# -----------------------------
# Visualize Feature Importance
# -----------------------------
# Plot top 15 features for better readability
top_n = 15
top_features = importances.head(top_n)

plt.figure(figsize=(12, 8))
colors = plt.cm.viridis(np.linspace(0, 1, len(top_features)))
bars = plt.barh(range(len(top_features)), top_features.values, color=colors)
plt.yticks(range(len(top_features)), top_features.index)
plt.xlabel('Feature Importance', fontsize=12, fontweight='bold')
plt.ylabel('Features', fontsize=12, fontweight='bold')
plt.title(f'Top {top_n} Feature Importances', fontsize=16, fontweight='bold', pad=20)
plt.gca().invert_yaxis()  # Highest importance at top

# Add value labels on bars
for i, (idx, val) in enumerate(zip(top_features.index, top_features.values)):
    plt.text(val + 0.001, i, f'{val:.4f}', va='center', fontsize=9)

plt.grid(axis='x', alpha=0.3, linestyle='--')
plt.tight_layout()

# Save feature importance plot
fi_path = os.path.join(script_dir, 'feature_importance.png')
plt.savefig(fi_path, dpi=300, bbox_inches='tight')
print(f"\n📊 Feature importance visualization saved: {fi_path}")
plt.close()

# -----------------------------
# Save trained model
# -----------------------------
model_filename = os.path.join(script_dir, 'random_forest_burnout_model.pkl')
joblib.dump(rf, model_filename)
print(f"\n💾 Model saved as: {model_filename}")
