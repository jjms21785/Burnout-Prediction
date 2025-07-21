import pandas as pd
import numpy as np
import random

# Define item names (OLBI-S: 16 items)
items = [f"Q{i+1}" for i in range(16)]
labels = ['Low', 'Moderate', 'High']

# Number of samples per class
samples_per_class = 800 // len(labels)

def generate_response(label):
    response = []
    for i in range(16):
        if label == 'Low':
            # Higher engagement, lower exhaustion
            score = np.random.randint(3, 5)
        elif label == 'Moderate':
            # Mixed values
            score = np.random.randint(2, 4)
        else:  # High burnout
            # Lower engagement, higher exhaustion
            score = np.random.randint(1, 3)
        response.append(score)
    return response

# Generate dataset
data = []
for label in labels:
    for _ in range(samples_per_class):
        row = generate_response(label)
        row.append(label)
        data.append(row)

# Create DataFrame
columns = items + ['Burnout_Risk']
df = pd.DataFrame(data, columns=columns)

# Shuffle dataset
df = df.sample(frac=1).reset_index(drop=True)

# Save to CSV
df.to_csv("olbi_burnout_dataset_800.csv", index=False)

# Output path
import os
print("Dataset saved to:", os.path.abspath("olbi_burnout_dataset_800.csv"))
