from flask import Flask, render_template, request, jsonify
import joblib
import numpy as np

app = Flask(__name__)

try:
    model = joblib.load("random_forest_burnout_model.pkl")
except FileNotFoundError:
    raise FileNotFoundError("Model file not found. Please ensure model exists.")

category_labels = {
    0: "Non-Burnout",
    1: "Exhausted",     
    2: "Disengaged",    
    3: "BURNOUT"
}

# positively-worded OLBI items 
# Q15, Q17, Q19, Q21, Q23, Q24, Q27, Q29
# In the OLBI array order: [0, 9, 2, 11, 12, 4, 6, 15]
reverse_scored_indices = [0, 2, 4, 6, 9, 11, 12, 15]

def reverse_score(val):
    return 5 - val

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
        
        # Create responses dict
        responses_dict = {f'Q{i+1}': all_responses[i] for i in range(30)}
        
        # ═══════════════════════════════════════════════════════════
        # MACHINE LEARNING PREDICTION (MAIN OUTPUT)
        # ═══════════════════════════════════════════════════════════
        features_array = np.array([all_responses])
        predicted_category = int(model.predict(features_array)[0])
        predicted_label = category_labels.get(predicted_category, "Unknown")
        
        # ═══════════════════════════════════════════════════════════
        # ACADEMIC PERFORMANCE (Q1-Q2)
        # ═══════════════════════════════════════════════════════════
        academic_score = responses_dict['Q1'] + responses_dict['Q2']
       
        academic_code = "D1" if academic_score >= 5 else "D2"
        academic_label = f"{academic_code}: Academic Performance - {'Good/High' if academic_code == 'D1' else 'Struggling/Low'}"
        
        # ═══════════════════════════════════════════════════════════
        # STRESS LEVEL (Q3-Q6 / PSS-4)
        # ═══════════════════════════════════════════════════════════  
        stress_score = (
            responses_dict['Q3'] +
            (4 - responses_dict['Q4']) +  # Reverse
            (4 - responses_dict['Q5']) +  # Reverse
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
        
        # ═══════════════════════════════════════════════════════════
        # SLEEP QUALITY (Q7-Q14 / SCI-8)
        # ═══════════════════════════════════════════════════════════
        sleep_score = sum(responses_dict[f'Q{i}'] for i in range(7, 15))
           
        if sleep_score >= 24:
            sleep_code = "D6"
            sleep_label = "D6: Sleep Quality - Good"
        elif sleep_score >= 16:
            sleep_code = "D7"
            sleep_label = "D7: Sleep Quality - Fair/Moderate"
        else:
            sleep_code = "D8"
            sleep_label = "D8: Sleep Quality - Poor"
        
        # ═══════════════════════════════════════════════════════════
        # OLBI-S BURNOUT ASSESSMENT (Q15-Q30)
        # ═══════════════════════════════════════════════════════════
        
        olbi_mapping = {
            'disengagement': [14, 17, 18, 21, 23, 25, 26, 29],  # Q15,Q18,Q19,Q22,Q24,Q26,Q27,Q30
            'exhaustion': [15, 16, 19, 20, 22, 24, 27, 28]      # Q16,Q17,Q20,Q21,Q23,Q25,Q28,Q29
        }
        
        olbi_responses = []
        for idx in olbi_mapping['disengagement']:
            olbi_responses.append(all_responses[idx])
        for idx in olbi_mapping['exhaustion']:
            olbi_responses.append(all_responses[idx])
        
        # Apply reverse scoring to positively-worded items
        scored_responses = []
        for i in range(16):
            val = olbi_responses[i]
            if i in reverse_scored_indices:
                val = reverse_score(val)
            scored_responses.append(val)
        
        # Calculate subscale scores
        disengagement_sum = sum(scored_responses[0:8])   # First 8 items
        exhaustion_sum = sum(scored_responses[8:16])     # Last 8 items
        
        disengagement_mean = disengagement_sum / 8
        exhaustion_mean = exhaustion_sum / 8
        
        # Apply thresholds
        ex_high = exhaustion_mean >= 2.25
        dis_high = disengagement_mean >= 2.1
        
        # Generate interpretation codes
        exhaustion_code = "A2" if ex_high else "A1"
        exhaustion_label = f"{exhaustion_code}: {'High' if ex_high else 'Low'} Exhaustion"
        
        disengagement_code = "B2" if dis_high else "B1"
        disengagement_label = f"{disengagement_code}: {'High' if dis_high else 'Low'} Disengagement"
        
        # Combined burnout state
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
        
        # ═══════════════════════════════════════════════════════════
        # BAR GRAPH DATA
        # ═══════════════════════════════════════════════════════════
        bar_data = {
            "Academic Performance": round((academic_score / 8) * 100, 2),
            "Stress": round((stress_score / 16) * 100, 2),
            "Sleep": round((sleep_score / 32) * 100, 2),
            "Exhaustion": round(((exhaustion_mean - 1) / 3) * 100, 2),
            "Disengagement": round(((disengagement_mean - 1) / 3) * 100, 2),
        }
        
        # ═══════════════════════════════════════════════════════════
        # FINAL RESPONSE PAYLOAD
        # ═══════════════════════════════════════════════════════════
        result_payload = {
            "PredictedResult": {
                "predicted_category": predicted_category,
                "label": predicted_label,  # ⭐ THIS IS THE MAIN OUTPUT
                "interpretation": f"Category {predicted_category}: {predicted_label}"
            },
            "Scores": {
                "exhaustion_score": round(exhaustion_mean, 2),
                "disengagement_score": round(disengagement_mean, 2),
                "academic_score": academic_score,
                "stress_score": stress_score,
                "sleep_score": sleep_score
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
    import os
    port = int(os.environ.get("PORT", 5000))
    app.run(debug=False, host='0.0.0.0', port=port)