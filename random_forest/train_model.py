import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
import joblib
import matplotlib.pyplot as plt
import seaborn as sns
import os

# ------------------------
# Create output directory
# ------------------------
plot_dir = "random_forest/plots"
os.makedirs(plot_dir, exist_ok=True)

# Load dataset
file_path = 'random_forest/dataset/training_data.csv'
df = pd.read_csv(file_path)

print(f"Loaded dataset: {file_path}")
print(f"Total samples: {len(df)}")

# Identify label column
if 'Category' in df.columns:
    target_col = 'Category'
elif 'Burnout_Category' in df.columns:
    target_col = 'Burnout_Category'
else:
    raise ValueError("No category column found! (Expected 'Category' or 'Burnout_Category')")

# Feature selection
features = [f'Q{i}' for i in range(1, 31)]

missing = [col for col in features if col not in df.columns]
if missing:
    raise ValueError(f"Missing feature columns in CSV: {missing}")

X = df[features]
y = df[target_col]

# Split
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

print(f"\n Train size: {len(X_train)} | Test size: {len(X_test)}")

# Train Random Forest
print("\n Training Random Forest model...")
rf = RandomForestClassifier(
    n_estimators=200,
    max_depth=None,
    min_samples_split=2,
    min_samples_leaf=1,
    random_state=42,
    n_jobs=-1
)
rf.fit(X_train, y_train)

# Evaluate
y_pred = rf.predict(X_test)

print("\nModel evaluation:")
print("Accuracy:", round(accuracy_score(y_test, y_pred), 4))
print("\nClassification Report:\n", classification_report(y_test, y_pred))
cm = confusion_matrix(y_test, y_pred)
print("\nConfusion Matrix:\n", cm)

# ------------------------------
# SAVE CONFUSION MATRIX AS PNG
# ------------------------------
plt.figure(figsize=(7, 6))
sns.heatmap(cm, annot=True, fmt="d", cmap="Blues",
            xticklabels=rf.classes_,
            yticklabels=rf.classes_)
plt.title("Confusion Matrix")
plt.xlabel("Predicted")
plt.ylabel("Actual")

conf_matrix_path = f"{plot_dir}/confusion_matrix.png"
plt.savefig(conf_matrix_path, dpi=300, bbox_inches="tight")
plt.close()
print(f"Confusion matrix saved as: {conf_matrix_path}")

# ------------------------------
# FEATURE IMPORTANCE GRAPH
# ------------------------------
importances = pd.Series(rf.feature_importances_, index=features).sort_values(ascending=False)

plt.figure(figsize=(10, 8))
sns.barplot(x=importances.values, y=importances.index, palette="viridis")
plt.title("Feature Importances")
plt.xlabel("Importance Score")
plt.ylabel("Feature (Questions Q1–Q30)")

feature_plot_path = f"{plot_dir}/feature_importances.png"
plt.savefig(feature_plot_path, dpi=300, bbox_inches="tight")
plt.close()
print(f"Feature importance chart saved as: {feature_plot_path}")

# ------------------------------
# Save trained model
# ------------------------------
model_filename = 'random_forest/random_forest_burnout_model.pkl'
joblib.dump(rf, model_filename)
print(f"\n Model saved as: {model_filename}")
