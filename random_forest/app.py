from flask import Flask, render_template, request, jsonify
import joblib
import numpy as np

app = Flask(__name__)

try:
    model = joblib.load("random_forest_burnout_model.pkl")
except FileNotFoundError:
    raise FileNotFoundError("Model file not found. Please ensure random_forest_burnout_model.pkl exists.")

# ✅ FIX 1: Correct category labels matching your training
category_labels = {
    0: "Non-Burnout",
    1: "Exhausted",      # ✅ FIXED ORDER
    2: "Disengaged",     # ✅ FIXED ORDER
    3: "BURNOUT"
}

# ✅ FIX 2: Correct OLBI reverse-scored items (standard OLBI-S pattern)
# These are the POSITIVELY worded items in OLBI-S that need reversing
# Assuming standard OLBI-S: items 1, 3, 6, 7, 9, 11, 14, 15 (in 1-16 numbering)
# In 0-indexed: 0, 2, 5, 6, 8, 10, 13, 14
olbi_reverse_indices = [0, 2, 5, 6, 8, 10, 13, 14]

def reverse_score(val):
    """Reverse score: 1→4, 2→3, 3→2, 4→1"""
    return 5 - val  # ✅ FIX: Correct formula for 1-4 scale

@app.route('/')
def home():
    return render_template('index.html')

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json() or {}
        
        if 'all_answers' not in data:
            return jsonify({'error': 'Missing all_answers field'}), 400
        
        all_responses = data['all_answers']
        if len(all_responses) != 30:
            return jsonify({'error': f'Expected 30 answers, got {len(all_responses)}'}), 400
        
        # ✅ Create responses dict (Q1-Q30)
        responses_dict = {f'Q{i+1}': all_responses[i] for i in range(30)}
        
        # ✅ FIX 3: ML Prediction uses ALL 30 raw responses
        features_array = np.array([all_responses])
        predicted_category = int(model.predict(features_array)[0])
        predicted_label = category_labels.get(predicted_category, "Unknown")
        
        # ========================================
        # OLBI-S SCORING (Q15-Q30 = indices 14-29)
        # ========================================
        
        # ✅ FIX 4: Extract OLBI responses correctly
        olbi_responses = all_responses[14:30]  # Q15-Q30 (16 items)
        
        # ✅ FIX 5: Apply reverse scoring to correct items
        scored_olbi = []
        for i, val in enumerate(olbi_responses):
            if i in olbi_reverse_indices:
                scored_olbi.append(reverse_score(val))
            else:
                scored_olbi.append(val)
        
        # ✅ FIX 6: Split into Exhaustion and Disengagement
        # OLBI-S structure: alternating pattern or specific subscales
        # Standard OLBI-S: odd items = Exhaustion, even items = Disengagement
        # OR use your specific mapping from thesis
        
        # Assuming standard pattern (adjust if your thesis specifies different):
        exhaustion_indices = [0, 2, 4, 6, 8, 10, 12, 14]  # Items 1, 3, 5, 7, 9, 11, 13, 15
        disengagement_indices = [1, 3, 5, 7, 9, 11, 13, 15]  # Items 2, 4, 6, 8, 10, 12, 14, 16
        
        exhaustion_items_scores = [scored_olbi[i] for i in exhaustion_indices]
        disengagement_items_scores = [scored_olbi[i] for i in disengagement_indices]
        
        exhaustion_sum = sum(exhaustion_items_scores)
        disengagement_sum = sum(disengagement_items_scores)
        
        exhaustion_mean = exhaustion_sum / 8
        disengagement_mean = disengagement_sum / 8
        
        # ✅ Apply thresholds (from your thesis)
        ex_high = exhaustion_mean >= 2.25
        dis_high = disengagement_mean >= 2.1
        
        exhaustion_code = "A2" if ex_high else "A1"
        exhaustion_label = f"{exhaustion_code}: {'High' if ex_high else 'Low'} Exhaustion"
        
        disengagement_code = "B2" if dis_high else "B1"
        disengagement_label = f"{disengagement_code}: {'High' if dis_high else 'Low'} Disengagement"
        
        # Combined burnout state interpretation
        if ex_high and dis_high:
            combined_code = "C4"
            combined_label = "C4: High Burnout (High Exhaustion + High Disengagement)"
        elif ex_high and not dis_high:
            combined_code = "C2"
            combined_label = "C2: Exhausted (High Exhaustion + Low Disengagement)"
        elif not ex_high and dis_high:
            combined_code = "C3"
            combined_label = "C3: Disengaged (Low Exhaustion + High Disengagement)"
        else:
            combined_code = "C1"
            combined_label = "C1: Low Burnout (Low Exhaustion + Low Disengagement)"
        
        # ========================================
        # ACADEMIC PERFORMANCE (Q1-Q2)
        # ========================================
        academic_sum = responses_dict['Q1'] + responses_dict['Q2']
        academic_code = "D1" if academic_sum >= 5 else "D2"
        academic_label = f"{academic_code}: Academic Performance - {'Good/High' if academic_code == 'D1' else 'Struggling/Low'}"
        
        # ========================================
        # STRESS LEVEL (Q3-Q6) - PSS-4
        # ========================================
        # Q3, Q6 = negative items (higher = more stress)
        # Q4, Q5 = positive items (need reversing)
        stress_score = (
            responses_dict['Q3'] +
            (5 - responses_dict['Q4']) +  # Reverse
            (5 - responses_dict['Q5']) +  # Reverse
            responses_dict['Q6']
        )
        
        if stress_score <= 4:
            stress_code = "D3"
            stress_label = "D3: Stress Level - Low"
        elif stress_score <= 8:
            stress_code = "D4"
            stress_label = "D4: Stress Level - Moderate"
        else:
            stress_code = "D5"
            stress_label = "D5: Stress Level - High"
        
        # ========================================
        # SLEEP QUALITY (Q7-Q14) - SCI-8
        # ========================================
        sleep_sum = sum(responses_dict[f'Q{i}'] for i in range(7, 15))
        
        if sleep_sum >= 24:
            sleep_code = "D6"
            sleep_label = "D6: Sleep Quality - Good"
        elif sleep_sum >= 16:
            sleep_code = "D7"
            sleep_label = "D7: Sleep Quality - Fair/Moderate"
        else:
            sleep_code = "D8"
            sleep_label = "D8: Sleep Quality - Poor"
        
        # ========================================
        # BAR GRAPH DATA (Percentages)
        # ========================================
        bar_data = {
            "Academic Performance": round((academic_sum / 8) * 100, 2),
            "Stress": round((stress_score / 16) * 100, 2),
            "Sleep": round((sleep_sum / 32) * 100, 2),
            "Exhaustion": round((exhaustion_sum / 32) * 100, 2),  # ✅ FIX: out of 32
            "Disengagement": round((disengagement_sum / 32) * 100, 2),  # ✅ FIX: out of 32
        }
        
        # ========================================
        # FINAL RESPONSE PAYLOAD
        # ========================================
        result_payload = {
            "PredictedResult": {
                "predicted_category": predicted_category,
                "label": predicted_label,
                "interpretation": f"Category {predicted_category}: {predicted_label}"
            },
            "Scores": {
                "exhaustion_score": round(exhaustion_mean, 2),
                "disengagement_score": round(disengagement_mean, 2),
                "academic_score": academic_sum,
                "stress_score": stress_score,
                "sleep_score": sleep_sum
            },
            "ResponseResult": {
                "Interpretations": {
                    "Combined": combined_label,
                    "Academic": academic_label,
                    "Stress": stress_label,
                    "Sleep": sleep_label,
                    "Exhaustion": exhaustion_label,
                    "Disengagement": disengagement_label
                },
                "Codes": {
                    "Combined": combined_code,
                    "Academic": academic_code,
                    "Stress": stress_code,
                    "Sleep": sleep_code,
                    "Exhaustion": exhaustion_code,
                    "Disengagement": disengagement_code
                }
            },
            "BarGraph": bar_data
        }
        
        return jsonify(result_payload)
        
    except Exception as e:
        import traceback
        return jsonify({
            'error': str(e),
            'traceback': traceback.format_exc()
        }), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)