import pandas as pd
import numpy as np
import random

# -----------------------------
# Configuration
# -----------------------------

# Target category counts
target_counts = {0: 60, 1: 86, 2: 103, 3: 0}

# Reference responses for each category
ref_responses = {
    0: [3,3,2,0,1,2,4,4,4,4,3,3,3,4,2,1,2,2,1,3,1,2,2,2,2,1,2,3,2,2],
    1: [
        [3,3,2,2,1,4,0,2,2,2,2,3,3,0,2,3,1,2,2,2,1,4,1,1,3,2,2,3,3,4],
        [3,2,1,1,1,1,2,1,3,2,3,3,1,4,2,3,1,3,2,3,1,3,2,2,2,2,4,2,3,2],
        [2,3,2,2,2,2,4,4,4,3,2,2,1,4,2,2,2,3,2,3,2,2,2,2,2,3,2,2,2,2]
    ],
    2: [
        [3,3,4,0,0,4,3,3,4,3,4,4,4,4,1,4,1,4,1,4,2,3,2,1,2,3,1,3,1,2],
        [3,3,1,2,2,2,4,4,4,3,4,4,4,4,2,3,2,2,2,4,2,2,2,2,2,1,2,1,2,1],
        [3,4,2,1,1,2,4,4,3,2,3,1,2,4,2,4,2,2,1,3,2,3,3,2,3,2,2,3,2,2],
        [3,3,2,1,1,2,3,1,2,2,2,2,2,3,2,3,2,2,2,3,2,3,1,1,3,1,1,3,2,2],
        [3,3,2,1,1,3,3,3,2,2,3,3,2,4,1,4,3,3,1,3,3,3,2,1,3,3,1,3,2,3]
    ]
}

# Demographics options
sex_options = ['Male', 'Female']
year_options = ['First','Second','Third','Fourth']
college_options = [
    'College of Business and Accountancy',
    'College of Computer Studies',
    'College of Education',
    'College of Engineering',
    'College of Hospitality Management',
    'College of Nursing',
    'College of Art and Science'
]

questions = [f'Q{i}' for i in range(1,31)]

# -----------------------------
# Helper Functions
# -----------------------------

def perturb_response(response, max_val=4, min_val=0):
    """Randomly perturb a reference response slightly while staying in bounds"""
    return [min(max(r + random.choice([-1,0,1]), min_val), max_val) for r in response]

def compute_exhaustion(q):
    """Compute Exhaustion score"""
    return np.mean([q[i-1] for i in [16,17,20,21,23,25,28,29]])

def compute_disengagement(q):
    """Compute Disengagement score"""
    return np.mean([q[i-1] for i in [15,18,19,22,24,26,27,30]])

def adjust_to_category(q, target_cat):
    """Adjust Exhaustion/Disengagement items to match target category if needed"""
    ex_cut, de_cut = 2.25, 2.1
    exhausted = compute_exhaustion(q)
    disengaged = compute_disengagement(q)

    # Get index lists (0-based)
    ex_items = [15,16,19,20,22,24,27,28]
    de_items = [14,17,18,21,23,25,26,29]

    def decrease(indices):
        for i in indices:
            if q[i] > 0:
                q[i] -= 1

    def increase(indices):
        for i in indices:
            if q[i] < 4:
                q[i] += 1

    if target_cat == 0:  # Low Exhaustion, Low Disengagement
        if exhausted >= ex_cut: decrease(ex_items)
        if disengaged >= de_cut: decrease(de_items)
    elif target_cat == 1:  # Low Exhaustion, High Disengagement
        if exhausted >= ex_cut: decrease(ex_items)
        if disengaged < de_cut: increase(de_items)
    elif target_cat == 2:  # High Exhaustion, Low Disengagement
        if exhausted < ex_cut: increase(ex_items)
        if disengaged >= de_cut: decrease(de_items)
    elif target_cat == 3:  # High Exhaustion, High Disengagement
        if exhausted < ex_cut: increase(ex_items)
        if disengaged < de_cut: increase(de_items)

    return q

# -----------------------------
# Generate Data
# -----------------------------
data_rows = []

for cat, n_samples in target_counts.items():
    for _ in range(n_samples):
        # Pick a base reference response
        ref = random.choice(ref_responses[cat]) if isinstance(ref_responses[cat], list) and isinstance(ref_responses[cat][0], list) else ref_responses[cat]
        q_vals = perturb_response(ref)

        # Adjust to keep within intended burnout category
        q_vals = adjust_to_category(q_vals, cat)

        # Compute metrics
        exhaustion = compute_exhaustion(q_vals)
        disengagement = compute_disengagement(q_vals)

        # Random demographics
        sex = random.choice(sex_options)
        age = random.randint(18,25)
        year = random.choice(year_options)
        college = random.choice(college_options)

        # Append full row
        row = [sex, age, year, college] + q_vals + [exhaustion, disengagement, cat]
        data_rows.append(row)

# Convert to DataFrame
column_order = ['sex','age','year','college'] + questions + ['Exhaustion','Disengagement','Burnout_Category']
df = pd.DataFrame(data_rows, columns=column_order)

# Reorder columns as requested
df = df[column_order]
df = df.sample(frac=1, random_state=42).reset_index(drop=True)

# Save CSV
df.to_csv('random_forest/dataset/training_data(3).csv', index=False)

# Summary
print("✅ Generated dataset saved as 'random_forest/dataset/training_data(3).csv'")
print(df['Burnout_Category'].value_counts().sort_index())
