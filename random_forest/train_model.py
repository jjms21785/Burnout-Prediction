import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import joblib

# Load dataset
df = pd.read_csv(r"C:\Users\juice wah\Desktop\thesis\thesis\random_forest\olbi_dataset.csv")  

X = df.drop(["Burnout_Risk", "Age", "Total_Score", "Gender", "Program"], axis=1)

# Target label (numeric: 0=Low, 1=Moderate, 2=High)
y = df["Burnout_Risk"]

# Train-test split
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42, stratify=y
)

# Initialize Random Forest
model = RandomForestClassifier(
    n_estimators=100,
    max_depth=None,
    random_state=42,
    class_weight="balanced"
)

# Train the model
model.fit(X_train, y_train)

# Predict on test set
y_pred = model.predict(X_test)

# Evaluate performance
print("Model Accuracy:", accuracy_score(y_test, y_pred))
print("\nClassification Report:\n", classification_report(y_test, y_pred, target_names=["Low", "Moderate", "High"]))
print("\nConfusion Matrix:\n", confusion_matrix(y_test, y_pred))

# # Save trained model
# joblib.dump(model, "olbi_model.pkl")
# print("\nModel saved successfully as")
