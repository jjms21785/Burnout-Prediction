import pandas as pd
import matplotlib.pyplot as plt

# Read the CSV file
df = pd.read_csv('random_forest/dataset/survey_cleaned.csv')

# Define the mapping dictionaries for each question type
mappings = {
    'Q1': {'Excellent': 4, 'Very Good': 3, 'Good': 2, 'Fair': 1, 'Poor': 0},
    'Q2': {'Much better': 4, 'Somewhat better': 3, 'About the same': 2, 'Somewhat worse': 1, 'Much worse': 0},
    'Q3': {'Very Often': 4, 'Fairly Often': 3, 'Sometimes': 2, 'Almost Never': 1, 'Never': 0},
    'Q4': {'Very Often': 0, 'Fairly Often': 1, 'Sometimes': 2, 'Almost Never': 3, 'Never': 4},
    'Q5': {'Very Often': 0, 'Fairly Often': 1, 'Sometimes': 2, 'Almost Never': 3, 'Never': 4},
    'Q6': {'Very Often': 4, 'Fairly Often': 3, 'Sometimes': 2, 'Almost Never': 1, 'Never': 0},
    'Q7': {'0 to15 mins': 4, '16 to 30 mins': 3, '31 to 45 mins': 2, '46 to 60 mins': 1, 'Greater than 60 mins': 0},
    'Q8': {'0 to15 mins': 4, '16 to 30 mins': 3, '31 to 45 mins': 2, '46 to 60 mins': 1, 'Greater than 60 mins': 0},
    'Q9': {'0 to 1': 4, '2': 3, '3': 2, '4': 1, '5 to 7': 0},
    'Q10': {'Very good': 4, 'Good': 3, 'Average': 2, 'Poor': 1, 'Very Poor': 0},
    'Q11': {'Not at all': 4, 'A little': 3, 'Somewhat': 2, 'Much': 1, 'Very much': 0},
    'Q12': {'Not at all': 4, 'A little': 3, 'Somewhat': 2, 'Much': 1, 'Very much': 0},
    'Q13': {'Not at all': 4, 'A little': 3, 'Somewhat': 2, 'Much': 1, 'Very much': 0},
    'Q14': {"I don’t have a problem / Less than 1 month": 4, '1 - 2 months': 3, '3 - 6 months': 2, '7 - 12 months': 1, 'More than 1 year': 0},
    'Q15': {'Strongly Agree': 1, 'Agree': 2, 'Disagree': 3, 'Strongly Disagree': 4},
    'Q16': {'Strongly Agree': 4, 'Agree': 3, 'Disagree': 2, 'Strongly Disagree': 1},
    'Q17': {'Strongly Agree': 1, 'Agree': 2, 'Disagree': 3, 'Strongly Disagree': 4},
    'Q18': {'Strongly Agree': 4, 'Agree': 3, 'Disagree': 2, 'Strongly Disagree': 1},
    'Q19': {'Strongly Agree': 1, 'Agree': 2, 'Disagree': 3, 'Strongly Disagree': 4},
    'Q20': {'Strongly Agree': 4, 'Agree': 3, 'Disagree': 2, 'Strongly Disagree': 1},
    'Q21': {'Strongly Agree': 1, 'Agree': 2, 'Disagree': 3, 'Strongly Disagree': 4},
    'Q22': {'Strongly Agree': 4, 'Agree': 3, 'Disagree': 2, 'Strongly Disagree': 1},
    'Q23': {'Strongly Agree': 1, 'Agree': 2, 'Disagree': 3, 'Strongly Disagree': 4},
    'Q24': {'Strongly Agree': 1, 'Agree': 2, 'Disagree': 3, 'Strongly Disagree': 4},
    'Q25': {'Strongly Agree': 4, 'Agree': 3, 'Disagree': 2, 'Strongly Disagree': 1},
    'Q26': {'Strongly Agree': 4, 'Agree': 3, 'Disagree': 2, 'Strongly Disagree': 1},
    'Q27': {'Strongly Agree': 1, 'Agree': 2, 'Disagree': 3, 'Strongly Disagree': 4},
    'Q28': {'Strongly Agree': 4, 'Agree': 3, 'Disagree': 2, 'Strongly Disagree': 1},
    'Q29': {'Strongly Agree': 1, 'Agree': 2, 'Disagree': 3, 'Strongly Disagree': 4},
    'Q30': {'Strongly Agree': 4, 'Agree': 3, 'Disagree': 2, 'Strongly Disagree': 1}
}

# Apply mappings to convert text responses to numerical values
for col, mapping in mappings.items():
    if col in df.columns:
        df[col] = df[col].apply(
            lambda x: mapping[x] if isinstance(x, str) and x in mapping else x
        )

# --- Calculate Burnout Scores ---

# Exhaustion score (E1–E8)
exhaustion_cols = ['Q16', 'Q17', 'Q20', 'Q21', 'Q23', 'Q25', 'Q28', 'Q29']
df['Exhaustion'] = df[exhaustion_cols].sum(axis=1) / 8

# Disengagement score (D1–D8)
disengagement_cols = ['Q15', 'Q18', 'Q19', 'Q22', 'Q24', 'Q26', 'Q27', 'Q30']
df['Disengagement'] = df[disengagement_cols].sum(axis=1) / 8

# --- Categorize Burnout ---
def categorize_burnout(row):
    exhaustion = row['Exhaustion']
    disengagement = row['Disengagement']
    
    high_exhaustion = exhaustion >= 2.25
    high_disengagement = disengagement >= 2.1
    
    if not high_exhaustion and not high_disengagement:
        return 0  # Non-Burnout
    elif not high_exhaustion and high_disengagement:
        return 1  # Disengaged
    elif high_exhaustion and not high_disengagement:
        return 2  # Exhausted
    else:
        return 3  # BURNOUT

df['Burnout_Category'] = df.apply(categorize_burnout, axis=1)

df.to_csv('random_forest/dataset/training_data.csv', index=False)

print(f"Total rows processed: {len(df)}\n")

category_counts = df['Burnout_Category'].value_counts().sort_index()

print("Burnout Category Distribution:")
print(f"0 (Non-Burnout):  {category_counts.get(0, 0)}")
print(f"1 (Disengaged):   {category_counts.get(1, 0)}")
print(f"2 (Exhausted):    {category_counts.get(2, 0)}")
print(f"3 (BURNOUT):      {category_counts.get(3, 0)}")

# Percentages
print("\n Percentage Distribution:")
percentages = (category_counts / len(df) * 100).round(2)
for cat, pct in percentages.items():
    print(f"Category {cat}: {pct}%")

# --- Optional Visualization ---
plt.figure(figsize=(6,4))
category_counts.plot(kind='bar', color=['#4CAF50','#FFC107','#FF5722','#F44336'])
plt.title('Burnout Category Distribution')
plt.xlabel('Category')
plt.ylabel('Number of Respondents')
plt.xticks(ticks=[0,1,2,3], labels=['Non-Burnout','Disengaged','Exhausted','BURNOUT'], rotation=30)
plt.tight_layout()
plt.show()
