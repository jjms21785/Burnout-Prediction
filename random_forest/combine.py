import pandas as pd

# -----------------------------
# Load the two datasets
# -----------------------------
file1 = 'random_forest/dataset/dataset.csv'
file2 = 'random_forest/dataset/dataset2.csv'

print("📂 Loading datasets...")
df1 = pd.read_csv(file1)
df2 = pd.read_csv(file2)

print(f"✅ {file1} loaded with {len(df1)} rows")
print(f"✅ {file2} loaded with {len(df2)} rows")

# -----------------------------
# Combine datasets
# -----------------------------
combined_df = pd.concat([df1, df2], ignore_index=True)

# Shuffle the combined data
combined_df = combined_df.sample(frac=1, random_state=42).reset_index(drop=True)

# -----------------------------
# Save merged dataset
# -----------------------------
output_file = 'random_forest/dataset/training_data.csv'
combined_df.to_csv(output_file, index=False)

# -----------------------------
# Summary
# -----------------------------
print("\n✅ Combined dataset saved as:", output_file)
print(f"Total rows: {len(combined_df)}")

if 'Category' in combined_df.columns:
    print("\n📊 Category distribution:")
    print(combined_df['Category'].value_counts().sort_index())
elif 'Burnout_Category' in combined_df.columns:
    print("\n📊 Burnout category distribution:")
    print(combined_df['Burnout_Category'].value_counts().sort_index())
