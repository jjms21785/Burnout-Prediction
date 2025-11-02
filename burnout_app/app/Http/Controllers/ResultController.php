<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * Interpretations from details.txt
     */
    private static $interpretations = [
        'A1' => [
            'title' => 'Exhaustion - Low', // Exhaustion
            'text' => "Responses show good energy levels for school work. There's enough energy to handle study demands without feeling constantly tired or drained. This means getting enough rest to handle daily tasks, recovering well after studying, and keeping up physical and mental energy throughout the day."
        ],
        'A2' => [
            'title' => 'Exhaustion - High', // Exhaustion
            'text' => "Responses show major physical and emotional tiredness related to studies. This might feel like being constantly drained, needing more recovery time than before, having trouble focusing, or having physical problems like headaches or sleep issues. This suggests operating without enough rest or that demands are too much right now."
        ],
        'B1' => [
            'title' => 'Disengagement - Low', // Disengagement
            'text' => "Responses show positive interest and connection to school work. There's still meaning in what's being studied, motivation to do coursework, and a sense of purpose in the academic path. The emotional and mental investment in studies is still strong."
        ],
        'B2' => [
            'title' => 'Disengagement - High',
            'text' => "Responses show reduced connection to and interest in school work. This might feel like being detached or not caring about studies, questioning if what's being learned matters, or just going through the motions without real interest. This suggests concerns about meaning, fit, or whether the academic path matches with interests or goals."
        ],
        'C1' => [
            'title' => 'Low Exhaustion + Low Disengagement = Low Burnout',
            'text' => "Things are functioning in a healthy range in both areas. There's enough energy to study AND interest in doing it. This balanced state means current demands are manageable and sustainable. This is experiencing the normal ups and downs of school life without crossing into burnout."
        ],
        'C2' => [
            'title' => 'High Exhaustion + Low Disengagement = Exhausted',
            'text' => 'This is a "still care but too tired" state. Despite feeling physically and emotionally drained, interest in studies is still there - there hasn\'t been a mental check out. This pattern means being overextended without enough recovery, but the fact that there\'s still care is actually protective. With proper rest and workload adjustment, recovery is possible without losing motivation.'
        ],
        'C3' => [
            'title' => 'Low Exhaustion + High Disengagement = Disengaged',
            'text' => "There's energy and capacity for school work, but not the interest or motivation to really engage with it. This \"have energy but no connection\" pattern means the issue isn't about needing rest, it's about questioning meaning, purpose, or fit. This often signals need to explore what's creating the disconnect: Is it the field of study? Teaching methods? Career uncertainty? Outside stress?"
        ],
        'C4' => [
            'title' => 'High Exhaustion + High Disengagement = High Burnout',
            'text' => 'Base on the result, both physically exhausted AND emotionally disconnected. The burnout has gone beyond early stages. Running on empty without motivation to refuel. This affects not just school but likely relationships, health, and overall quality of life. This needs immediate professional support.'
        ],
        'D1' => [
            'title' => 'Academic Performance - Good',
            'text' => "Responses suggest maintaining okay academic performance despite other challenges. GPA is stable, attending classes regularly, submitting assignments on time, and meeting school expectations. This means managing to fulfill requirements even if it's taking a lot of effort.\nGood grades alongside high exhaustion or disengagement often means \"pushing through\" at a big personal cost. Success doesn't mean there's no struggle - it may mean working much harder than is sustainable to keep it up."
        ],
        'D2' => [
            'title' => 'Academic Performance - Struggling',
            'text' => "Responses show challenges with school performance. This might mean missing classes, turning in assignments late, getting lower grades, or feeling unable to meet course requirements. This suggests that burnout symptoms are starting to impact the ability to function academically.\nFalling grades are often a late sign - by the time grades drop, burnout has usually been building for weeks or months. This signals urgent need for help and support."
        ],
        'D3' => [
            'title' => 'Stress Level - Low',
            'text' => "Responses show manageable stress levels. Handling school and personal demands without feeling overwhelmed. Coping strategies seem to be working, and there's no constant worry or pressure getting in the way of daily life.\nLow stress alongside high exhaustion might mean the body is worn out even without current outside pressure - suggesting built-up tiredness that needs recovery time."
        ],
        'D4' => [
            'title' => 'Stress Level - Moderate',
            'text' => "Responses show noticeable but not extreme stress levels. Experiencing some pressure from school or personal demands, and may feel stressed sometimes, but it's not constant or unmanageable. This is a zone where stress is there but hasn't become overwhelming.\nModerate stress is common in school. The question is whether there are good coping strategies and enough recovery time to manage it long-term."
        ],
        'D5' => [
            'title' => 'Stress Level - High',
            'text' => "Responses show major stress levels. This might feel like being constantly under pressure, worrying a lot about school demands, feeling like problems are piling up, or struggling to feel in control. High stress affects the ability to relax, focus, and keep emotions balanced.\nHigh stress combined with high exhaustion creates a dangerous cycle where stress prevents recovery and lack of recovery increases stress. This combination needs immediate attention to break the pattern."
        ],
        'D6' => [
            'title' => 'Sleep Quality - Good',
            'text' => "Responses show good sleep quality. Generally getting enough rest, falling asleep without major problems, and waking up feeling reasonably refreshed. Sleep is doing its job of restoring the body and mind.\nGood sleep alongside high exhaustion suggests the tiredness isn't mainly about sleep - it may be emotional or mental exhaustion that rest alone doesn't fully fix."
        ],
        'D7' => [
            'title' => 'Sleep Quality - Moderate',
            'text' => "Responses show some sleep problems. This might mean sometimes having trouble falling asleep, waking up during the night, or not feeling fully rested in the morning. Sleep is somewhat affected but not severely disrupted.\nFair sleep quality often both comes from and adds to school stress. Improving sleep habits can have positive effects on energy and stress management."
        ],
        'D8' => [
            'title' => 'Sleep Quality - Poor',
            'text' => "Responses show major sleep problems. This might mean struggling to fall asleep, waking up a lot, having insomnia, or always feeling unrested despite time in bed. Poor sleep is likely affecting daytime functioning, mood, and ability to cope with stress.\nPoor sleep is both a symptom and cause of burnout. It's almost impossible to recover from exhaustion without fixing sleep quality. This should be a main focus for improvement."
        ],
    ];

    /**
     * Recommendations from details.txt
     */
    private static $recommendations = [
        'A1' => "Keep doing what's working to maintain energy levels. Continue getting enough sleep, taking regular breaks, and balancing study time with rest. Stay active with simple exercise like walking, and set realistic goals to avoid burning out later. Keep boundaries between study and personal time clear, and don't skip meals or sleep to finish work.",
        'A2' => "Rest needs to be the top priority right now. Get more sleep, take breaks during study sessions, and talk to instructors about possible extensions or reduced workload. Say no to extra commitments and focus only on essential tasks. Try gentle activities like walking or stretching to release stress. If feeling exhausted doesn't improve with rest, consider seeing a doctor to check if something else is affecting energy levels.",
        'B1' => "Stay connected to what makes studying meaningful. Keep linking coursework to personal interests and future goals. Try different study methods to keep things interesting, and connect with classmates or study groups to make learning more enjoyable. Celebrate small wins along the way, and remember why the academic path matters beyond just getting good grades.",
        'B2' => "Take time to figure out why there's a disconnect with studies. Think about whether the current field really fits with interests and goals, or if outside pressure is driving the academic choices. Talk to advisors or counselors about options and different paths. Try connecting assignments to personal interests to see if engagement improves. If the disconnection doesn't improve, consider that changing direction or taking time off might be the right choice.",
        'C1' => "Keep up current habits that support both energy and motivation. Stay aware of any changes in how the body or mind feels, and make small adjustments when needed. Use this balanced time to build healthy routines and connect with support resources on campus. Remember that some ups and downs are normal, but watch for patterns that last more than a few days.",
        'C2' => "Focus on rest without feeling guilty about it. The fact that interest in studies is still there means recovery is possible with proper rest. Talk to instructors about the situation - they often have more flexibility than expected. Cut back on non-essential activities and accept help from friends or tutors. Set firm boundaries for rest time and don't let guilt about studying take over. If exhaustion continues despite resting, see a healthcare provider to rule out medical issues.",
        'C3' => "The issue isn't about needing rest - it's about finding meaning and connection. Think honestly about what originally sparked interest in this field and whether it still fits. Consider if boring classes or outside stress are causing the disconnect, or if it's something deeper about the academic path. Talk to people working in related careers to get a realistic picture. If after exploring these questions the disconnection remains, know that changing direction shows self-awareness, not failure.",
        'C4' => "Seek professional help immediately. This level of burnout is serious and typically needs more than self-help. Contact campus counseling or a mental health professional right away. Consider options like reducing course load or taking a leave of absence to create real space for recovery. Tell academic advisors or student services about the situation to learn about available accommodations. Focus on basic needs like sleep and eating, even when motivation is low. Reach out to friends or family for support. Understand that recovery takes time - weeks or months, not days - and that's normal.",
        'D1' => "Check whether good grades are coming from healthy habits or from sacrificing sleep, health, or personal time. If pushing too hard to maintain performance, consider whether slightly lower grades might be worth protecting overall wellbeing. Success in classes doesn't mean struggles with stress or exhaustion aren't real or important. Make sure to acknowledge accomplishments instead of immediately moving to the next challenge without pause.",
        'D2' => "Struggling grades are often a sign that something else needs attention first - exhaustion, stress, or disconnection. Get academic support through tutoring, study groups, or office hours. Talk to instructors about difficulties - they can often offer extensions or point to helpful resources. Focus on completing work rather than making it perfect. Consider whether the current course load is realistic, and whether dropping a class or taking time off might help long-term success. Remember that current struggles don't define future potential.",
        'D3' => "Keep up whatever is helping manage stress well. Use this calmer time to practice stress-management techniques like exercise or relaxation so they become habits. Build and maintain supportive friendships. Pay attention to early signs if stress starts increasing, so adjustments can be made before feeling overwhelmed. Take advantage of lower stress periods to get ahead on upcoming work when possible.",
        'D4' => "Some stress is normal in school, but make sure it's not slowly building up. Check that coping strategies are working and adjust if needed. Identify what's most important and what can wait or be skipped. Keep regular stress-release activities in the weekly routine - don't let them slide. Watch whether stress is staying the same, getting better, or getting worse over time. Consider stress-management resources or workshops to build better coping skills.",
        'D5' => "Take action now to reduce stress before it gets worse. Reach out to counseling services for both practical techniques and emotional support. Look at what's causing stress and see what can be changed or reduced, even temporarily. Cut back on commitments wherever possible. Practice daily stress-release like deep breathing or short walks. Make sure to get enough sleep and eat regularly - skipping these makes stress worse. Talk to friends or family for help and perspective.",
        'D6' => "Protect good sleep by keeping consistent sleep schedules and bedtime routines. Don't sacrifice sleep when workload increases - it makes everything harder. If feeling exhausted despite good sleep, the tiredness might be emotional or mental rather than physical, which means other areas need attention beyond just rest.",
        'D7' => "Small improvements to sleep can make a big difference in energy and stress. Try going to bed and waking up at the same time every day. Avoid screens, caffeine, and intense studying in the hour before bed. Create a calming bedtime routine. Check that the sleep space is dark, quiet, and comfortable. If thoughts keep racing at night, try writing them down earlier in the day. If sleep doesn't improve with these changes, talk to a healthcare provider.",
        'D8' => "Fixing sleep needs to be a top priority because everything else is harder without good rest. See a doctor or sleep specialist to check for sleep disorders that might need treatment. Set consistent sleep and wake times, make the bedroom dark and cool, and remove screens from the bedtime routine. Cut back on caffeine and check if medications might be affecting sleep. If anxiety keeps the mind awake, consider counseling or therapy. Understand that sleep improvement takes time - usually several weeks - but it's worth the effort because poor sleep affects mood, focus, stress, and health.",
    ];

    /**
     * Validate if Python response has all required data
     * 
     * @param array $processedData Processed data from processPythonResponse
     * @return bool True if data is available and valid
     */
    public static function validateData($processedData)
    {
        if (!is_array($processedData)) {
            return false;
        }
        
        // Check required fields
        $hasCategories = isset($processedData['exhaustion_category']) && isset($processedData['disengagement_category']);
        $hasInterpretations = isset($processedData['interpretations']) && is_array($processedData['interpretations']);
        $hasRecommendations = isset($processedData['recommendations']) && is_array($processedData['recommendations']);
        $hasBarGraph = isset($processedData['bar_graph']) && is_array($processedData['bar_graph']);
        
        return $hasCategories && $hasInterpretations && $hasRecommendations && $hasBarGraph;
    }

    /**
     * Process Python API response and convert to format for view
     * 
     * @param array $pythonResponse The response from Python Flask API
     * @return array Formatted data ready for the view, includes 'data_available' flag
     */
    public static function processPythonResponse($pythonResponse)
    {
        $predictedResult = $pythonResponse['PredictedResult'] ?? null;
        $responseResult = $pythonResponse['ResponseResult'] ?? null;
        $barGraph = $pythonResponse['BarGraph'] ?? null;
        
        // Extract codes from ResponseResult
        $codes = $responseResult['Codes'] ?? [];
        $exhaustionCode = $codes['Exhaustion'] ?? 'A1';
        $disengagementCode = $codes['Disengagement'] ?? 'B1';
        $academicCode = $codes['Academic'] ?? null;
        $stressCode = $codes['Stress'] ?? null;
        $sleepCode = $codes['Sleep'] ?? null;
        
        // Determine combined code (C1-C4)
        $isHighExhaustion = ($exhaustionCode === 'A2');
        $isHighDisengagement = ($disengagementCode === 'B2');
        
        if (!$isHighExhaustion && !$isHighDisengagement) {
            $combinedCode = 'C1';
        } elseif ($isHighExhaustion && !$isHighDisengagement) {
            $combinedCode = 'C2';
        } elseif (!$isHighExhaustion && $isHighDisengagement) {
            $combinedCode = 'C3';
        } else {
            $combinedCode = 'C4';
        }
        
        // Build interpretations structure
        $interpretations = [
            'top_card' => [
                'exhaustion' => [
                    'code' => $exhaustionCode,
                    'title' => self::$interpretations[$exhaustionCode]['title'] ?? '',
                    'text' => self::$interpretations[$exhaustionCode]['text'] ?? ''
                ],
                'disengagement' => [
                    'code' => $disengagementCode,
                    'title' => self::$interpretations[$disengagementCode]['title'] ?? '',
                    'text' => self::$interpretations[$disengagementCode]['text'] ?? ''
                ]
            ],
            'combined_result' => [
                'code' => $combinedCode,
                'title' => self::$interpretations[$combinedCode]['title'] ?? '',
                'text' => self::$interpretations[$combinedCode]['text'] ?? ''
            ],
            'breakdown' => []
        ];
        
        // Add breakdown interpretations if codes are available
        if ($academicCode && isset(self::$interpretations[$academicCode])) {
            $interpretations['breakdown']['academic'] = [
                'code' => $academicCode,
                'title' => self::$interpretations[$academicCode]['title'],
                'text' => self::$interpretations[$academicCode]['text'],
                'recommendation' => self::$recommendations[$academicCode] ?? ''
            ];
        }
        
        if ($stressCode && isset(self::$interpretations[$stressCode])) {
            $interpretations['breakdown']['stress'] = [
                'code' => $stressCode,
                'title' => self::$interpretations[$stressCode]['title'],
                'text' => self::$interpretations[$stressCode]['text'],
                'recommendation' => self::$recommendations[$stressCode] ?? ''
            ];
        }
        
        if ($sleepCode && isset(self::$interpretations[$sleepCode])) {
            $interpretations['breakdown']['sleep'] = [
                'code' => $sleepCode,
                'title' => self::$interpretations[$sleepCode]['title'],
                'text' => self::$interpretations[$sleepCode]['text'],
                'recommendation' => self::$recommendations[$sleepCode] ?? ''
            ];
        }
        
        // Build recommendations structure
        $recommendations = [
            'exhaustion' => self::$recommendations[$exhaustionCode] ?? '',
            'disengagement' => self::$recommendations[$disengagementCode] ?? '',
            'combined' => self::$recommendations[$combinedCode] ?? ''
        ];
        
        // Extract categories for exhaustion and disengagement
        $exhaustionCategory = ($exhaustionCode === 'A2') ? 'High' : 'Low';
        $disengagementCategory = ($disengagementCode === 'B2') ? 'High' : 'Low';
        
        // Get predicted label and normalize it
        $predictedLabel = $predictedResult['label'] ?? 'Unknown';
        
        // Map Python labels to view-friendly labels
        $labelMap = [
            'Non-Burnout' => 'Low',
            'Disengaged' => 'Disengaged',
            'Exhausted' => 'Exhausted',
            'BURNOUT' => 'High'
        ];
        if (isset($labelMap[$predictedLabel])) {
            $predictedLabel = $labelMap[$predictedLabel];
        }
        
        // Validate data availability
        $result = [
            'predicted_label' => $predictedLabel,
            'predicted_category' => $predictedResult['predicted_category'] ?? 0,
            'exhaustion_category' => $exhaustionCategory,
            'disengagement_category' => $disengagementCategory,
            'interpretations' => $interpretations,
            'recommendations' => $recommendations,
            'bar_graph' => $barGraph,
            'codes' => [
                'exhaustion' => $exhaustionCode,
                'disengagement' => $disengagementCode,
                'combined' => $combinedCode,
                'academic' => $academicCode,
                'stress' => $stressCode,
                'sleep' => $sleepCode,
            ]
        ];
        
        // Add data availability flag
        $result['data_available'] = self::validateData($result);
        
        return $result;
    }

    /**
     * Process result data for display in the view
     * Handles category determination, bar graph percentages, and error messages
     * 
     * @param bool $dataAvailable Whether data is available
     * @param string|null $exhaustionCategory 'High' or 'Low'
     * @param string|null $disengagementCategory 'High' or 'Low'
     * @param array|null $barGraph Bar graph data with percentages
     * @param string|null $errorMsg Error message if available
     * @return array Processed result data for the view
     */
    public static function processResultForView($dataAvailable, $exhaustionCategory = null, $disengagementCategory = null, $barGraph = null, $errorMsg = null)
    {
        if (!$dataAvailable) {
            return [
                'categoryName' => 'Results Unavailable',
                'categoryCode' => $errorMsg ?? 'Assessment data not available. Please ensure the Flask API is running.',
                'category' => null,
                'exhaustionPercent' => 0,
                'disengagementPercent' => 0,
                'academicPercent' => 0,
                'stressPercent' => 0,
                'sleepPercent' => 0,
            ];
        }

        // Determine burnout category from actual data
        $category = null;
        $categoryName = '';
        $categoryCode = '';

        if ($exhaustionCategory == 'Low' && $disengagementCategory == 'Low') {
            $category = 'low';
            $categoryName = 'Low Burnout';
            $categoryCode = 'Low Exhaustion + Low Disengagement';
        } elseif ($exhaustionCategory == 'High' && $disengagementCategory == 'Low') {
            $category = 'exhausted';
            $categoryName = 'Exhausted';
            $categoryCode = 'High Exhaustion + Low Disengagement';
        } elseif ($exhaustionCategory == 'Low' && $disengagementCategory == 'High') {
            $category = 'disengaged';
            $categoryName = 'Disengaged';
            $categoryCode = 'Low Exhaustion + High Disengagement';
        } else {
            $category = 'high';
            $categoryName = 'High Burnout';
            $categoryCode = 'High Exhaustion + High Disengagement';
        }

        // Use bar graph percentages from Python (already calculated in app.py)
        $exhaustionPercent = isset($barGraph['Exhaustion']) ? round($barGraph['Exhaustion']) : 0;
        $disengagementPercent = isset($barGraph['Disengagement']) ? round($barGraph['Disengagement']) : 0;
        $academicPercent = isset($barGraph['Academic Performance']) ? round($barGraph['Academic Performance']) : 0;
        $stressPercent = isset($barGraph['Stress']) ? round($barGraph['Stress']) : 0;
        $sleepPercent = isset($barGraph['Sleep']) ? round($barGraph['Sleep']) : 0;

        // Ensure percentages are within 0-100 range (safety check)
        $exhaustionPercent = max(0, min(100, $exhaustionPercent));
        $disengagementPercent = max(0, min(100, $disengagementPercent));
        $academicPercent = max(0, min(100, $academicPercent));
        $stressPercent = max(0, min(100, $stressPercent));
        $sleepPercent = max(0, min(100, $sleepPercent));

        return [
            'categoryName' => $categoryName,
            'categoryCode' => $categoryCode,
            'category' => $category,
            'exhaustionPercent' => $exhaustionPercent,
            'disengagementPercent' => $disengagementPercent,
            'academicPercent' => $academicPercent,
            'stressPercent' => $stressPercent,
            'sleepPercent' => $sleepPercent,
        ];
    }
}
