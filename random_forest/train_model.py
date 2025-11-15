import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.svm import SVC
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix, precision_score, recall_score, f1_score
from sklearn.preprocessing import StandardScaler
import xgboost as xgb
import joblib
import matplotlib.pyplot as plt
import seaborn as sns
import numpy as np
import json

# Load dataset
file_path = 'random_forest/training_data.csv'
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
# Use only Q1–Q30 as features
features = [f'Q{i}' for i in range(1, 31)]

# Check that all required columns exist
missing = [col for col in features if col not in df.columns]
if missing:
    raise ValueError(f"Missing feature columns in CSV: {missing}")

X = df[features]
y = df[target_col]

# Split into train/test
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

print(f"\n Train size: {len(X_train)} | Test size: {len(X_test)}")

# Category labels mapping
category_labels = {
    0: "Non-Burnout",
    1: "Exhausted",
    2: "Disengaged",
    3: "BURNOUT"
}

# Store results for comparison
results = {}

# ============================================================================
# 1. RANDOM FOREST (Bagging Method)
# ============================================================================
print("\n" + "="*70)
print("1. TRAINING RANDOM FOREST CLASSIFIER (Bagging Method)")
print("="*70)
rf = RandomForestClassifier(
    n_estimators=200,
    max_depth=None,
    min_samples_split=2,
    min_samples_leaf=1,
    random_state=42,
    n_jobs=-1
)
rf.fit(X_train, y_train)
y_pred_rf = rf.predict(X_test)

rf_accuracy = accuracy_score(y_test, y_pred_rf)
rf_precision = precision_score(y_test, y_pred_rf, average='weighted')
rf_recall = recall_score(y_test, y_pred_rf, average='weighted')
rf_f1 = f1_score(y_test, y_pred_rf, average='weighted')

results['Random Forest'] = {
    'accuracy': rf_accuracy,
    'precision': rf_precision,
    'recall': rf_recall,
    'f1_score': rf_f1,
    'predictions': y_pred_rf.tolist(),
    'confusion_matrix': confusion_matrix(y_test, y_pred_rf).tolist()
}

print(f"Accuracy: {rf_accuracy:.4f}")
print(f"Precision: {rf_precision:.4f}")
print(f"Recall: {rf_recall:.4f}")
print(f"F1-Score: {rf_f1:.4f}")
print("\nClassification Report:\n", classification_report(y_test, y_pred_rf))
print("\nConfusion Matrix:\n", confusion_matrix(y_test, y_pred_rf))

# Save Random Forest model
rf_model_filename = 'random_forest/random_forest_burnout_model.pkl'
joblib.dump(rf, rf_model_filename)
print(f"\nRandom Forest model saved as: {rf_model_filename}")

# ============================================================================
# 2. XGBOOST (Boosting Method)
# ============================================================================
print("\n" + "="*70)
print("2. TRAINING XGBOOST CLASSIFIER (Boosting Method)")
print("="*70)
xgb_model = xgb.XGBClassifier(
    n_estimators=200,
    max_depth=6,
    learning_rate=0.1,
    subsample=0.8,
    colsample_bytree=0.8,
    random_state=42,
    n_jobs=-1,
    eval_metric='mlogloss'
)
xgb_model.fit(X_train, y_train)
y_pred_xgb = xgb_model.predict(X_test)

xgb_accuracy = accuracy_score(y_test, y_pred_xgb)
xgb_precision = precision_score(y_test, y_pred_xgb, average='weighted')
xgb_recall = recall_score(y_test, y_pred_xgb, average='weighted')
xgb_f1 = f1_score(y_test, y_pred_xgb, average='weighted')

results['XGBoost'] = {
    'accuracy': xgb_accuracy,
    'precision': xgb_precision,
    'recall': xgb_recall,
    'f1_score': xgb_f1,
    'predictions': y_pred_xgb.tolist(),
    'confusion_matrix': confusion_matrix(y_test, y_pred_xgb).tolist()
}

print(f"Accuracy: {xgb_accuracy:.4f}")
print(f"Precision: {xgb_precision:.4f}")
print(f"Recall: {xgb_recall:.4f}")
print(f"F1-Score: {xgb_f1:.4f}")
print("\nClassification Report:\n", classification_report(y_test, y_pred_xgb))
print("\nConfusion Matrix:\n", confusion_matrix(y_test, y_pred_xgb))

# Save XGBoost model
xgb_model_filename = 'random_forest/xgboost_burnout_model.pkl'
joblib.dump(xgb_model, xgb_model_filename)
print(f"\nXGBoost model saved as: {xgb_model_filename}")

# ============================================================================
# 3. SUPPORT VECTOR MACHINE (Margin-based Method)
# ============================================================================
print("\n" + "="*70)
print("3. TRAINING SUPPORT VECTOR MACHINE (Margin-based Method)")
print("="*70)

# Scale features for SVM (SVM is sensitive to feature scaling)
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)

svm_model = SVC(
    kernel='rbf',
    C=1.0,
    gamma='scale',
    random_state=42,
    probability=True
)
svm_model.fit(X_train_scaled, y_train)
y_pred_svm = svm_model.predict(X_test_scaled)

svm_accuracy = accuracy_score(y_test, y_pred_svm)
svm_precision = precision_score(y_test, y_pred_svm, average='weighted')
svm_recall = recall_score(y_test, y_pred_svm, average='weighted')
svm_f1 = f1_score(y_test, y_pred_svm, average='weighted')

results['SVM'] = {
    'accuracy': svm_accuracy,
    'precision': svm_precision,
    'recall': svm_recall,
    'f1_score': svm_f1,
    'predictions': y_pred_svm.tolist(),
    'confusion_matrix': confusion_matrix(y_test, y_pred_svm).tolist()
}

print(f"Accuracy: {svm_accuracy:.4f}")
print(f"Precision: {svm_precision:.4f}")
print(f"Recall: {svm_recall:.4f}")
print(f"F1-Score: {svm_f1:.4f}")
print("\nClassification Report:\n", classification_report(y_test, y_pred_svm))
print("\nConfusion Matrix:\n", confusion_matrix(y_test, y_pred_svm))

# Save SVM model and scaler
svm_model_filename = 'random_forest/svm_burnout_model.pkl'
svm_scaler_filename = 'random_forest/svm_scaler.pkl'
joblib.dump(svm_model, svm_model_filename)
joblib.dump(scaler, svm_scaler_filename)
print(f"\nSVM model saved as: {svm_model_filename}")
print(f"SVM scaler saved as: {svm_scaler_filename}")

# Save results to JSON for discussion
results_filename = 'random_forest/model_comparison_results.json'
with open(results_filename, 'w') as f:
    json.dump(results, f, indent=4)
print(f"\nComparison results saved as: {results_filename}")

# ============================================================================
# VISUALIZATIONS
# ============================================================================

# Create confusion matrices for all models
models_data = [
    ('Random Forest', y_pred_rf, 'Blues'),
    ('XGBoost', y_pred_xgb, 'Greens'),
    ('SVM', y_pred_svm, 'Oranges')
]

for model_name, y_pred, cmap_color in models_data:
    cm = confusion_matrix(y_test, y_pred)
    plt.figure(figsize=(10, 8))
    sns.heatmap(cm, annot=True, fmt='d', cmap=cmap_color, 
                xticklabels=[category_labels[i] for i in sorted(category_labels.keys())],
                yticklabels=[category_labels[i] for i in sorted(category_labels.keys())],
                cbar_kws={'label': 'Count'})
    plt.title(f'Confusion Matrix - {model_name}', fontsize=14, fontweight='bold', pad=20)
    plt.ylabel('Actual Values', fontsize=12, fontweight='bold')
    plt.xlabel('Predicted Values', fontsize=12, fontweight='bold')
    plt.tight_layout()
    confusion_matrix_path = f'random_forest/confusion_matrix_{model_name.lower().replace(" ", "_")}.png'
    plt.savefig(confusion_matrix_path, dpi=300, bbox_inches='tight')
    print(f"\n{model_name} confusion matrix saved as: {confusion_matrix_path}")
    plt.close()

# Feature importances - Top 10 only (Random Forest)
importances = pd.Series(rf.feature_importances_, index=features).sort_values(ascending=False)
top_10_features = importances.head(10)

print("\nTop 10 important features (Random Forest):")
print(top_10_features)

# Save all feature importances as JSON for dashboard
all_importances = {feature: float(importance) for feature, importance in zip(features, rf.feature_importances_)}
sorted_importances = dict(sorted(all_importances.items(), key=lambda x: x[1], reverse=True))
feature_importance_json_path = 'random_forest/feature_importance.json'
with open(feature_importance_json_path, 'w') as f:
    json.dump(sorted_importances, f, indent=4)
print(f"\nAll feature importances saved as: {feature_importance_json_path}")

# Create feature importance visualization
plt.figure(figsize=(10, 6))
colors = plt.cm.viridis(np.linspace(0, 1, len(top_10_features)))
bars = plt.barh(range(len(top_10_features)), top_10_features.values, color=colors)
plt.yticks(range(len(top_10_features)), top_10_features.index)
plt.xlabel('Feature Importance', fontsize=12, fontweight='bold')
plt.ylabel('Features (Questions)', fontsize=12, fontweight='bold')
plt.title('Top 10 Most Important Features for Burnout Prediction (Random Forest)', fontsize=14, fontweight='bold', pad=20)
plt.gca().invert_yaxis()

# Add value labels on bars
for i, (idx, val) in enumerate(top_10_features.items()):
    plt.text(val + 0.001, i, f'{val:.4f}', va='center', fontsize=9)

plt.grid(axis='x', alpha=0.3, linestyle='--')
plt.tight_layout()
feature_importance_path = 'random_forest/feature_importance_top10.png'
plt.savefig(feature_importance_path, dpi=300, bbox_inches='tight')
print(f"Feature importance plot saved as: {feature_importance_path}")
plt.close()

# ============================================================================
# MODEL COMPARISON VISUALIZATIONS
# ============================================================================

# Comparison bar chart for metrics
metrics = ['Accuracy', 'Precision', 'Recall', 'F1-Score']
rf_metrics = [rf_accuracy, rf_precision, rf_recall, rf_f1]
xgb_metrics = [xgb_accuracy, xgb_precision, xgb_recall, xgb_f1]
svm_metrics = [svm_accuracy, svm_precision, svm_recall, svm_f1]

x = np.arange(len(metrics))
width = 0.25

fig, ax = plt.subplots(figsize=(12, 6))
bars1 = ax.bar(x - width, rf_metrics, width, label='Random Forest', color='#3498db')
bars2 = ax.bar(x, xgb_metrics, width, label='XGBoost', color='#2ecc71')
bars3 = ax.bar(x + width, svm_metrics, width, label='SVM', color='#e67e22')

ax.set_xlabel('Metrics', fontsize=12, fontweight='bold')
ax.set_ylabel('Score', fontsize=12, fontweight='bold')
ax.set_title('Model Comparison: Performance Metrics', fontsize=14, fontweight='bold', pad=20)
ax.set_xticks(x)
ax.set_xticklabels(metrics)
ax.legend()
ax.set_ylim([0, 1.05])
ax.grid(axis='y', alpha=0.3, linestyle='--')

# Add value labels on bars
for bars in [bars1, bars2, bars3]:
    for bar in bars:
        height = bar.get_height()
        ax.text(bar.get_x() + bar.get_width()/2., height + 0.01,
                f'{height:.3f}', ha='center', va='bottom', fontsize=9)

plt.tight_layout()
comparison_path = 'random_forest/model_comparison_metrics.png'
plt.savefig(comparison_path, dpi=300, bbox_inches='tight')
print(f"\nModel comparison chart saved as: {comparison_path}")
plt.close()

# Summary comparison table
print("\n" + "="*70)
print("MODEL COMPARISON SUMMARY")
print("="*70)
comparison_df = pd.DataFrame({
    'Model': ['Random Forest', 'XGBoost', 'SVM'],
    'Accuracy': [rf_accuracy, xgb_accuracy, svm_accuracy],
    'Precision': [rf_precision, xgb_precision, svm_precision],
    'Recall': [rf_recall, xgb_recall, svm_recall],
    'F1-Score': [rf_f1, xgb_f1, svm_f1]
})
print(comparison_df.to_string(index=False))
print("="*70)
