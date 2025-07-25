import pandas as pd

# Load dataset
df = pd.read_csv(r'C:\Users\juice wah\Desktop\thesis\thesis\random_forest\journal_pone_0253808_s003.csv')

# Define column groups
disengagement_cols = ['D1P', 'D2N', 'D3P', 'D4N', 'D5P', 'D6N', 'D7P', 'D8N']
exhaustion_cols = ['E1N', 'E2P', 'E3N', 'E4P', 'E5N', 'E6P', 'E7N', 'E8P']

# Calculate totals and averages
df['d_total'] = df[disengagement_cols].sum(axis=1)
df['e_total'] = df[exhaustion_cols].sum(axis=1)
df['d_avg'] = df['d_total'] / len(disengagement_cols)
df['e_avg'] = df['e_total'] / len(exhaustion_cols)

# Classification thresholds
DISENGAGEMENT_THRESHOLD = 2.10
EXHAUSTION_THRESHOLD = 2.25

# Burnout classification logic
def classify_burnout_numeric(row):
    d = row['d_avg'] >= DISENGAGEMENT_THRESHOLD
    e = row['e_avg'] >= EXHAUSTION_THRESHOLD

    # 0 = Low 
    # 1 = Disengaged
    # 2 = Exhausted
    # 3 = High 

    if not e and not d:
        return 0
    elif not e and d:
        return 1
    elif e and not d:
        return 2
    else:
        return 3

# Apply classification
df['burnout_cat'] = df.apply(classify_burnout_numeric, axis=1)

# Rename columns to remove N/P
rename_map = {col: col[:2] + col[2:] for col in disengagement_cols + exhaustion_cols}
rename_map.update({old: new.replace('N', '').replace('P', '') for old, new in rename_map.items()})
df.rename(columns=rename_map, inplace=True)

# Prepare output
output_cols = [rename_map[col] for col in disengagement_cols + exhaustion_cols] + ['burnout_cat']
result = df[output_cols]

# Save to CSV
result.to_csv(r'C:\Users\juice wah\Desktop\thesis\thesis\random_forest\cleaned_data.csv', index=False)
