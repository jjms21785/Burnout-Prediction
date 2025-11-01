import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
from sklearn.preprocessing import LabelEncoder
import matplotlib.pyplot as plt
import seaborn as sns

# Read the training data
df = pd.read_csv('random_forest/dataset/training_data.csv')

print("Dataset Shape:", df.shape)
print("\nFirst few rows:")
print(df.head())
print("\nBurnout Category Distribution:")
print(df['Category'].value_counts().sort_index())

# Encode categorical variables (sex and college)
le_sex = LabelEncoder()
le_college = LabelEncoder()

df['sex_encoded'] = le_sex.fit_transform(df['sex'])
df['college_encoded'] = le_college.fit_transform(df['college'])

# Select features for training
# Using all questions (Q1-Q30), age, year, sex, and college
feature_columns = ['sex_encoded', 'age', 'year', 'college_encoded'] + [f'Q{i}' for i in range(1, 31)]

X = df[feature_columns]
y = df['Category']

# Split data into training and testing sets (80-20 split)
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

print(f"\nTraining set size: {len(X_train)}")
print(f"Testing set size: {len(X_test)}")

# Train Random Forest Classifier
rf_model = RandomForestClassifier(
    n_estimators=100,      # Number of trees
    max_depth=10,          # Maximum depth of trees
    min_samples_split=5,   # Minimum samples to split a node
    min_samples_leaf=2,    # Minimum samples at leaf node
    random_state=42,
    n_jobs=-1              # Use all CPU cores
)

print("\nTraining Random Forest model...")
rf_model.fit(X_train, y_train)

# Make predictions
y_pred = rf_model.predict(X_test)

# Evaluate the model
accuracy = accuracy_score(y_test, y_pred)
print(f"\n{'='*50}")
print(f"Model Accuracy: {accuracy:.4f} ({accuracy*100:.2f}%)")
print(f"{'='*50}")

# Classification Report
print("\nClassification Report:")
print(classification_report(y_test, y_pred, 
                          target_names=['Non-Burnout', 'Disengaged', 'Exhausted', 'BURNOUT']))

# Confusion Matrix
cm = confusion_matrix(y_test, y_pred)
print("\nConfusion Matrix:")
print(cm)

# Plot Confusion Matrix
plt.figure(figsize=(8, 6))
sns.heatmap(cm, annot=True, fmt='d', cmap='Blues', 
            xticklabels=['Non-Burnout', 'Disengaged', 'Exhausted', 'BURNOUT'],
            yticklabels=['Non-Burnout', 'Disengaged', 'Exhausted', 'BURNOUT'])
plt.title('Confusion Matrix')
plt.ylabel('Actual')
plt.xlabel('Predicted')
plt.tight_layout()
plt.savefig('random_forest/confusion_matrix.png', dpi=300, bbox_inches='tight')
print("\nConfusion matrix saved as 'random_forest/confusion_matrix.png'")

# Feature Importance
feature_importance = pd.DataFrame({
    'feature': feature_columns,
    'importance': rf_model.feature_importances_
}).sort_values('importance', ascending=False)

print("\nTop 15 Most Important Features:")
print(feature_importance.head(15))

# Plot Feature Importance
plt.figure(figsize=(10, 8))
top_features = feature_importance.head(20)
plt.barh(range(len(top_features)), top_features['importance'])
plt.yticks(range(len(top_features)), top_features['feature'])
plt.xlabel('Importance')
plt.title('Top 20 Feature Importances')
plt.gca().invert_yaxis()
plt.tight_layout()
plt.savefig('random_forest/feature_importance.png', dpi=300, bbox_inches='tight')
print("\nFeature importance plot saved as 'random_forest/feature_importance.png'")

# Save the model
import joblib
joblib.dump(rf_model, 'random_forest/rf_model.pkl')
joblib.dump(le_sex, 'random_forest/le_sex.pkl')
joblib.dump(le_college, 'random_forest/le_college.pkl')
print("\nModel saved as 'random_forest/rf_model.pkl'")
print("Label encoders saved for future predictions")

# Example prediction on new data
print("\n" + "="*50)
print("Example: Making a prediction on the first test sample")
print("="*50)
sample = X_test.iloc[0:1]
prediction = rf_model.predict(sample)
probability = rf_model.predict_proba(sample)

burnout_labels = ['Non-Burnout', 'Disengaged', 'Exhausted', 'BURNOUT']
print(f"Predicted Category: {burnout_labels[prediction[0]]} ({prediction[0]})")
print("\nPrediction Probabilities:")
for i, label in enumerate(burnout_labels):
    print(f"  {label}: {probability[0][i]:.4f} ({probability[0][i]*100:.2f}%)")

print("\nTraining complete!")