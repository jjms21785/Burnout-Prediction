import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, accuracy_score
import joblib

# Load your cleaned dataset
df = pd.read_csv(r'C:\Users\juice wah\Desktop\thesis\thesis\random_forest\cleaned_data.csv')

# Define features and target
feature_cols = ['D1', 'D2', 'D3', 'D4', 'D5', 'D6', 'D7', 'D8',
                'E1', 'E2', 'E3', 'E4', 'E5', 'E6', 'E7', 'E8']
X = df[feature_cols]
y = df['burnout_cat']

# Split dataset
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Train model
clf = RandomForestClassifier(random_state=42)
clf.fit(X_train, y_train)

# Predict
y_pred = clf.predict(X_test)

# Evaluate model
print("Accuracy:", accuracy_score(y_test, y_pred))
print("\nClassification Report:\n", classification_report(y_test, y_pred, digits=4))

# # Save model
# joblib.dump(clf, r'C:\Users\juice wah\Desktop\thesis\thesis\random_forest\olbi_model.joblib')
