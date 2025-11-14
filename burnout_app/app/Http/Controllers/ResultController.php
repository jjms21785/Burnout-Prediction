<?php

namespace App\Http\Controllers;

class ResultController extends Controller
{
    // Interpretations
    private static $interpretations = [
        'A1' => [
            'title' => 'Exhaustion - Low', 
            'text' => "Responses show good energy levels for school work. There's enough energy to handle study demands without feeling constantly tired or drained. This means getting enough rest to handle daily tasks, recovering well after studying, and keeping up physical and mental energy throughout the day."
        ],
        'A2' => [
            'title' => 'Exhaustion - High', 
            'text' => "Responses show major physical and emotional tiredness related to studies. This might feel like being constantly drained, needing more recovery time than before, having trouble focusing, or having physical problems like headaches or sleep issues. This suggests operating without enough rest or that demands are too much right now."
        ],
        'B1' => [
            'title' => 'Disengagement - Low', 
            'text' => "Responses show positive interest and connection to school work. There's still meaning in what's being studied, motivation to do coursework, and a sense of purpose in the academic path. The emotional and mental investment in studies is still strong."
        ],
        'B2' => [
            'title' => 'Disengagement - High',
            'text' => "Responses show reduced connection to and interest in school work. This might feel like being detached or not caring about studies, questioning if what's being learned matters, or just going through the motions without real interest. This suggests concerns about meaning, fit, or whether the academic path matches with interests or goals."
        ],
        'C1' => [
            'title' => 'Low Exhaustion & Low Disengagement = Low Burnout',
            'text' => "Things are functioning in a healthy range in both areas. There's enough energy to study and interest in doing it. This balanced state means current demands are manageable and sustainable. This is experiencing the normal ups and downs of school life without crossing into burnout."
        ],
        'C2' => [
            'title' => 'High Exhaustion & Low Disengagement = Exhausted',
            'text' => 'This is a "still care but too tired" state. Despite feeling physically and emotionally drained, interest in studies is still there, there hasn\'t been a mental check out. This pattern means being overextended without enough recovery, but the fact that there\'s still care is actually protective. With proper rest and workload adjustment, recovery is possible without losing motivation.'
        ],
        'C3' => [
            'title' => 'Low Exhaustion & High Disengagement = Disengaged',
            'text' => "There's energy and capacity for school work, but not the interest or motivation to really engage with it. This \"have energy but no connection\" pattern means the issue isn't about needing rest, it's about questioning meaning, purpose, or fit. This often signals need to explore what's creating the disconnect: Is it the field of study? Teaching methods? Career uncertainty? Outside stress?"
        ],
        'C4' => [
            'title' => 'High Exhaustion & High Disengagement = High Burnout',
            'text' => 'Base on the result, both physically exhausted and emotionally disconnected. The burnout has gone beyond early stages. Running on empty without motivation to refuel. This affects not just school but likely relationships, health, and overall quality of life. This needs immediate rest and support.'
        ],
        'D1' => [
            'title' => 'Academic Performance - Good',
            'text' => "Responses suggest maintaining okay academic performance despite other challenges. GPA is stable and meeting school expectations. This means managing to fulfill requirements even if it's taking a lot of effort.\nGood grades alongside high exhaustion or disengagement often means \"pushing through\" at a big personal cost. Success doesn't mean there's no struggle, it may mean working much harder than is sustainable to keep it up."
        ],
        'D2' => [
            'title' => 'Academic Performance - Struggling',
            'text' => "Responses show challenges with school performance. This might mean missing classes, turning in assignments late, getting lower grades, or feeling unable to meet course requirements. This suggests that burnout symptoms are starting to impact the ability to function academically.\nFalling grades are often a late sign, by the time grades drop, burnout has usually been building for weeks or months. This signals urgent need for help and support."
        ],
        'D3' => [
            'title' => 'Stress Level - Low',
            'text' => "Responses show manageable stress levels. Handling school and personal demands without feeling overwhelmed. Coping strategies seem to be working, and there's no constant worry or pressure getting in the way of daily life.\nLow stress alongside high exhaustion might mean the body is worn out even without current outside pressure, suggesting built-up tiredness that needs recovery time."
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
            'text' => "Responses show good sleep quality. Generally getting enough rest, falling asleep without major problems, and waking up feeling reasonably refreshed. Sleep is doing its job of restoring the body and mind.\nGood sleep alongside high exhaustion suggests the tiredness isn't mainly about sleep, it may be emotional or mental exhaustion that rest alone doesn't fully fix."
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

    // Recommendations
    private static $recommendations = [
        'A1' => "Keep doing what's working to maintain energy levels. Continue getting enough sleep, taking regular breaks, and balancing study time with rest. Stay active with simple exercise like walking, and set realistic goals to avoid burning out later.",
        'A2' => "Prioritize rest. Sleep more, take study breaks, reduce workload, refuse extra commitments, do gentle activities, and don't skip meals or sleep to finish work.",
        'B1' => "Stay connected to what makes studying meaningful. Try different study methods to keep things interesting. Appreciate small things, and remember why the academic path matters beyond just getting good grades.",
        'B2' => "Take time to figure out why there's a disconnect with studies. Think about whether the current field really fits with interests and goals, or if outside pressure is driving the academic choices.",
        'C1' => "Keep up current habits that support both energy and motivation. Stay aware of any changes in how the body or mind feels, and make small adjustments when needed.",
        'C2' => "Focus on rest without feeling guilty about it. The fact that interest in studies is still there means recovery is possible with proper rest.",
        'C3' => "The issue isn't about needing rest, it's about finding meaning. Consider if boring classes or outside stress are causing the disconnect, or if it's something deeper about the academic path.",
        'C4' => "This level of burnout is serious and consider options like reducing course load or taking a leave of absence to create real space for recovery Focus on basic needs like sleep and eating.",
        'D1' => "Check whether good grades are coming from healthy habits or from sacrificing sleep, health, or personal time. Success in classes doesn't mean struggles with stress or exhaustion aren't real or important.",
        'D2' => "Struggling grades are often a sign that something else needs attention first, exhaustion, stress, or disconnection. Focus on completing work rather than making it perfect. Consider whether the current course load is realistic, and taking time off might help long-term success.",
        'D3' => "Keep up whatever is helping manage stress well. Pay attention to early signs if stress starts increasing, so adjustments can be made before feeling overwhelmed. Take advantage of lower stress periods to get ahead on upcoming work when possible.",
        'D4' => "Some stress is normal in school, but make sure it's not slowly building up. Identify what's most important and what can wait or be skipped. Consider stress-management resources or workshops to build better coping skills.",
        'D5' => "Counseling services is needed, for both practical techniques and emotional support. Look at what's causing stress and see what can be changed or reduced, even temporarily. Make sure to get enough sleep and eat regularly.",
        'D6' => "Protect good sleep by keeping consistent sleep schedules and bedtime routines. Don't sacrifice sleep when workload increases, it makes everything harder.",
        'D7' => "Small improvements to sleep can make a big difference in energy and stress. Try going to bed and waking up at the same time every day. Avoid screens, caffeine, and intense studying in the hour before bed.",
        'D8' => "Fixing sleep needs to be a top priority because everything else is harder without good rest. Set consistent sleep and wake times, make the bedroom dark and cool, and remove screens from the bedtime routine.",
    ];

    public static function generateBasicInterpretations($exhaustionCategory, $disengagementCategory, $exhaustionAverage = 0, $disengagementAverage = 0)
    {
        $exhaustionCode = $exhaustionCategory === 'High' ? 'A2' : 'A1';
        $disengagementCode = $disengagementCategory === 'High' ? 'B2' : 'B1';
        $combinedCode = self::determineCombinedCodeFromCategories($exhaustionCategory, $disengagementCategory);
        
        return [
            'top_card' => [
                'exhaustion' => [
                    'code' => $exhaustionCode,
                    'title' => self::$interpretations[$exhaustionCode]['title'] ?? 'Exhaustion',
                    'text' => self::$interpretations[$exhaustionCode]['text'] ?? ''
                ],
                'disengagement' => [
                    'code' => $disengagementCode,
                    'title' => self::$interpretations[$disengagementCode]['title'] ?? 'Disengagement',
                    'text' => self::$interpretations[$disengagementCode]['text'] ?? ''
                ]
            ],
            'combined_result' => [
                'code' => $combinedCode,
                'title' => self::$interpretations[$combinedCode]['title'] ?? 'Burnout Assessment',
                'text' => self::$interpretations[$combinedCode]['text'] ?? ''
            ],
            'breakdown' => []
        ];
    }
    
    public static function generateBasicRecommendations($exhaustionCategory, $disengagementCategory)
    {
        $exhaustionCode = $exhaustionCategory === 'High' ? 'A2' : 'A1';
        $disengagementCode = $disengagementCategory === 'High' ? 'B2' : 'B1';
        $combinedCode = self::determineCombinedCodeFromCategories($exhaustionCategory, $disengagementCategory);
        
        return [
            'exhaustion' => self::$recommendations[$exhaustionCode] ?? '',
            'disengagement' => self::$recommendations[$disengagementCode] ?? '',
            'combined' => self::$recommendations[$combinedCode] ?? ''
        ];
    }

    public static function validateData($processedData)
    {
        if (!is_array($processedData)) {
            return false;
        }
        
        $hasCategories = isset($processedData['exhaustion_category']) && isset($processedData['disengagement_category']);
        $hasInterpretations = isset($processedData['interpretations']) && is_array($processedData['interpretations']);
        $hasRecommendations = isset($processedData['recommendations']) && is_array($processedData['recommendations']);
        $hasBarGraph = isset($processedData['bar_graph']) && is_array($processedData['bar_graph']);
        
        return $hasCategories && $hasInterpretations && $hasRecommendations && $hasBarGraph;
    }

    // Public API: Python response processing
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
        
        $combinedCode = self::determineCombinedCodeFromCodes($exhaustionCode, $disengagementCode);
        
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
        
        $labelMap = [
            'Non-Burnout' => 'Low',
            'Disengaged' => 'Disengaged',
            'Exhausted' => 'Exhausted',
            'BURNOUT' => 'High'
        ];
        if (isset($labelMap[$predictedLabel])) {
            $predictedLabel = $labelMap[$predictedLabel];
        }
        
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
        
        $result['data_available'] = self::validateData($result);
        
        return $result;
    }

    // Public API: View result processing
    // Uses ML prediction value (0,1,2,3) from database instead of manual calculation
    public static function processResultForView($mlPredictionValue = null, $barGraph = null, $errorMsg = null)
    {
        // If no ML prediction value, return unavailable
        if ($mlPredictionValue === null || $mlPredictionValue === '' || $mlPredictionValue === 'unavailable') {
            return [
                'categoryName' => 'Results Unavailable',
                'category' => null,
                'exhaustionPercent' => 0,
                'disengagementPercent' => 0,
                'academicPercent' => 0,
                'stressPercent' => 0,
                'sleepPercent' => 0,
            ];
        }

        $category = null;
        $categoryName = '';
        
        // Use ML prediction value directly (0,1,2,3) - matches ML model output
        $categoryNum = is_numeric($mlPredictionValue) ? (int)$mlPredictionValue : null;
        
        if ($categoryNum !== null && $categoryNum >= 0 && $categoryNum <= 3) {
            switch ($categoryNum) {
                case 0:
                    $category = 'low';
                    $categoryName = 'Low Burnout';
                    break;
                case 1:
                    $category = 'exhausted';
                    $categoryName = 'Exhausted';
                    break;
                case 2:
                    $category = 'disengaged';
                    $categoryName = 'Disengaged';
                    break;
                case 3:
                    $category = 'high';
                    $categoryName = 'High Burnout';
                    break;
                default:
                    $categoryName = 'Results Unavailable';
            }
        } else {
            $categoryName = 'Results Unavailable';
        }

        $exhaustionPercent = isset($barGraph['Exhaustion']) ? round($barGraph['Exhaustion']) : 0;
        $disengagementPercent = isset($barGraph['Disengagement']) ? round($barGraph['Disengagement']) : 0;
        $academicPercent = isset($barGraph['Academic Performance']) ? round($barGraph['Academic Performance']) : 0;
        $stressPercent = isset($barGraph['Stress']) ? round($barGraph['Stress']) : 0;
        $sleepPercent = isset($barGraph['Sleep']) ? round($barGraph['Sleep']) : 0;

        $exhaustionPercent = max(0, min(100, $exhaustionPercent));
        $disengagementPercent = max(0, min(100, $disengagementPercent));
        $academicPercent = max(0, min(100, $academicPercent));
        $stressPercent = max(0, min(100, $stressPercent));
        $sleepPercent = max(0, min(100, $sleepPercent));

        return [
            'categoryName' => $categoryName,
            'category' => $category,
            'exhaustionPercent' => $exhaustionPercent,
            'disengagementPercent' => $disengagementPercent,
            'academicPercent' => $academicPercent,
            'stressPercent' => $stressPercent,
            'sleepPercent' => $sleepPercent,
        ];
    }

    // Helpers
    private static function determineCombinedCodeFromCategories(string $exhaustionCategory, string $disengagementCategory): string
    {
        $isHighExhaustion = ($exhaustionCategory === 'High');
        $isHighDisengagement = ($disengagementCategory === 'High');
        if (!$isHighExhaustion && !$isHighDisengagement) return 'C1';
        if ($isHighExhaustion && !$isHighDisengagement) return 'C2';
        if (!$isHighExhaustion && $isHighDisengagement) return 'C3';
        return 'C4';
    }

    private static function determineCombinedCodeFromCodes(string $exhaustionCode, string $disengagementCode): string
    {
        $isHighExhaustion = ($exhaustionCode === 'A2');
        $isHighDisengagement = ($disengagementCode === 'B2');
        if (!$isHighExhaustion && !$isHighDisengagement) return 'C1';
        if ($isHighExhaustion && !$isHighDisengagement) return 'C2';
        if (!$isHighExhaustion && $isHighDisengagement) return 'C3';
        return 'C4';
    }
}
