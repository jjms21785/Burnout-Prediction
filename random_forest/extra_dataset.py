import pandas as pd
import numpy as np
import random

# -------------------------
# Reference responses
# -------------------------
category_0_refs = [
    [3,3,2,0,1,2,4,4,4,4,3,3,3,4,2,1,2,2,1,3,1,2,2,2,2,1,2,3,2,2]
]

category_1_refs = [
    [3,3,2,2,1,4,0,2,2,2,2,3,3,0,2,3,1,2,2,2,1,4,1,1,3,2,2,3,3,4],
    [3,3,2,2,1,4,0,2,2,2,2,3,3,0,2,3,1,2,2,2,1,4,1,1,3,2,2,3,3,4],
    [3,2,1,1,1,1,2,1,3,2,3,3,1,4,2,3,1,3,2,3,1,3,2,2,2,2,4,2,3,2],
    [2,3,2,2,2,2,4,4,4,3,2,2,1,4,2,2,2,3,2,3,2,2,2,2,2,3,2,2,2,2]
]

category_2_refs = [
    [3,3,4,0,0,4,3,3,4,3,4,4,4,4,1,4,1,4,1,4,2,3,2,1,2,3,1,3,1,2],
    [3,3,1,2,2,2,4,4,4,3,4,4,4,4,2,3,2,2,2,4,2,2,2,2,2,1,2,1,2,1],
    [3,4,2,1,1,2,4,4,3,2,3,1,2,4,2,4,2,2,1,3,2,3,3,2,3,2,2,3,2,2],
    [3,4,2,2,0,2,3,4,4,3,3,3,3,3,1,1,1,3,1,4,3,4,3,1,2,2,1,3,1,1],
    [3,3,2,1,1,2,3,1,2,2,2,2,2,3,2,3,2,2,2,3,2,3,1,1,3,1,1,3,2,2],
    [3,3,2,1,1,3,3,3,2,2,3,3,2,4,1,4,3,3,1,3,3,3,2,1,3,3,1,3,2,3],
    [3,3,2,0,2,2,4,4,4,3,3,3,2,4,1,3,1,2,1,2,2,4,2,2,3,2,2,3,2,1]
]

# -------------------------
# Parameters
# -------------------------
target_counts = {0:60, 1:86, 2:103, 3:0}  # Category 3 = 0
questions = [f'Q{i}' for i in range(1,31)]

# Demographics options
sex_options = ['Male','Female']
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
age_range = (18,24)

# -------------------------
# Helper functions
# -------------------------
def perturb(values, min_val=0, max_val=4):
    """Slightly perturb reference values while staying within bounds"""
    return [min(max(val + random.choice([-1,0,1]), min_val), max_val) for val in values]

def compute_exhaustion(row):
    return np.mean([row[f'Q{i}'] for i in [16,17,20,21,23,25,28,29]])

def compute_disengagement(row):
    return np.mean([row[f'Q{i}'] for i in [15,18,19,22,24,26,27,30]])

def assign_category(exhaustion, disengagement):
    if exhaustion < 2.25 and disengagement < 2.1:
        return 0
    elif exhaustion < 2.25 and disengagement >= 2.1:
        return 1
    elif exhaustion >= 2.25 and disengagement < 2.1:
        return 2
    else:
        return 3

# -------------------------
# Data generation
# -------------------------
all_rows = []
for cat, count in target_counts.items():
    if count == 0:
        continue
    refs = {0:category_0_refs, 1:category_1_refs, 2:category_2_refs}[cat]
    for _ in range(count):
        template = random.choice(refs)
        q_vals = perturb(template)
        row = {f'Q{i+1}': q_vals[i] for i in range(30)}
        row['sex'] = random.choice(sex_options)
        row['age'] = random.randint(*age_range)
        row['year'] = random.choice(year_options)
        row['college'] = random.choice(college_options)
        # Derived columns
        row['Exhaustion'] = compute_exhaustion(row)
        row['Disengagement'] = compute_disengagement(row)
        row['Burnout_Category'] = assign_category(row['Exhaustion'], row['Disengagement'])
        all_rows.append(row)

# Shuffle all rows
random.shuffle(all_rows)

# Convert to DataFrame
df = pd.DataFrame(all_rows)

# Reorder columns as requested
column_order = ['sex','age','year','college'] + questions + ['Exhaustion','Disengagement','Burnout_Category']
df = df[column_order]

# -------------------------
# Output
# -------------------------
print("Category distribution after generation:")
print(df['Burnout_Category'].value_counts())
df.to_csv('random_forest/dataset/training_data(2).csv', index=False)
print("CSV saved as 'random_forest/dataset/training_data(2).csv'")
