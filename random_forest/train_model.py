import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import joblib

# Load dataset
df = pd.read_csv("olbi_burnout_dataset.csv")

# Split into features and label
X = df.drop("Burnout_Risk", axis=1)
y = df["Burnout_Risk"]

# Encode labels (Low=0, Moderate=1, High=2)
le = LabelEncoder()
y_encoded = le.fit_transform(y)

# Train-test split
X_train, X_test, y_train, y_test = train_test_split(
    X, y_encoded, test_size=0.2, random_state=42, stratify=y_encoded
)

# Initialize Random Forest
model = RandomForestClassifier(
    n_estimators=100,        # Number of trees
    max_depth=None,          # Let trees grow fully
    random_state=42,
    class_weight="balanced"  # In case of imbalance
)

# Train the model
model.fit(X_train, y_train)

# Predict
y_pred = model.predict(X_test)

# Evaluate
print("Model Accuracy:", accuracy_score(y_test, y_pred))
print("\n Classification Report:\n", classification_report(y_test, y_pred, target_names=le.classes_))
print("\n Confusion Matrix:\n", confusion_matrix(y_test, y_pred))

# Save the model and label encoder
joblib.dump(model, "random_forest_olbi_model.pkl")
joblib.dump(le, "label_encoder.pkl")

print("\n Model and encoder saved successfully.")
