import pandas as pd
import numpy as np
import random
import os

# Define OLBI-S question columns
items = [f"Q{i+1}" for i in range(16)]
reverse_scored_items = ['Q1', 'Q4', 'Q7', 'Q8', 'Q11', 'Q13', 'Q15', 'Q16']

# Labels and mappings
labels = ['Low', 'Moderate', 'High']
label_map = {'Low': 0, 'Moderate': 1, 'High': 2}
gender_map = {"Female": 0, "Male": 1}
program_map = {
    "BSA": 0, "BSBA": 1, "BSENT": 2, "BSHM": 3, "BEED": 4,
    "BSED-ENG": 5, "BSED-FIL": 6, "BSED-MATH": 7, "BA-PSYCH": 8,
    "BSCS": 9, "BSIT": 10, "BSECE": 11, "BSN": 12
}
programs = list(program_map.keys())
genders = list(gender_map.keys())

# Number of records per class
samples_per_class = 800 // len(labels)

# Function to reverse score (on 1–4 scale)
def reverse_score(val):
    return 5 - val

# Generate synthetic response
def generate_response(label):
    response = {}

    # OLBI responses
    for q in items:
        if label == 'Low':
            score = np.random.randint(3, 5)
        elif label == 'Moderate':
            score = np.random.randint(2, 4)
        else:  # High
            score = np.random.randint(1, 3)

        if q in reverse_scored_items:
            score = reverse_score(score)

        response[q] = score

    # Demographics
    gender = random.choice(genders)
    program = random.choice(programs)
    age = np.random.randint(17, 25)

    response['Age'] = age
    response['Gender'] = gender_map[gender]
    response['Program'] = program_map[program]

    # Score & label
    response['Total_Score'] = sum(response[q] for q in items)
    response['Burnout_Risk'] = label_map[label]

    return response

# Generate data
data = []
for label in labels:
    for _ in range(samples_per_class):
        data.append(generate_response(label))

# Create DataFrame and shuffle
df = pd.DataFrame(data)
df = df.sample(frac=1, random_state=42).reset_index(drop=True)

# Save to CSV
df.to_csv("olbi_dataset.csv", index=False)
print("Dataset saved to:", os.path.abspath("olbi_dataset.csv"))
