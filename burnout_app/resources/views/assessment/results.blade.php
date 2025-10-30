<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burnout Assessment Results</title>
    @vite('resources/css/app.css')
    <style>
        .bar-chart-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .bar-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .bar-label {
            min-width: 150px;
            font-weight: 500;
            color: #374151;
        }
        
        .bar-wrapper {
            flex: 1;
            background-color: #e5e7eb;
            border-radius: 0.5rem;
            height: 2rem;
            overflow: hidden;
            position: relative;
        }
        
        .bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #f59e0b, #f97316);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 0.75rem;
            transition: width 0.3s ease;
        }
        
        .bar-percentage {
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .insight-card {
            padding: 1rem;
            border-left: 4px solid #3b82f6;
            background-color: #f0f9ff;
            border-radius: 0.375rem;
        }
        
        .insight-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 0.25rem;
        }
        
        .insight-text {
            color: #1e3a8a;
            font-size: 0.875rem;
        }
        
        .component-score-item {
            padding: 1.25rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: #fafafa;
        }
        
        .component-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        
        .component-name {
            font-weight: 600;
            color: #1f2937;
        }
        
        .component-score {
            font-size: 1.25rem;
            font-weight: 700;
            color: #f59e0b;
        }
        
        .component-description {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 0.5rem;
            background-color: #e5e7eb;
            border-radius: 0.25rem;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 0.25rem;
        }
        
        .recommendation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .recommendation-card {
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
        
        .recommendation-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            background-color: #22c55e;
            color: white;
            border-radius: 50%;
            margin-bottom: 0.75rem;
            font-weight: bold;
        }
        
        .recommendation-title {
            font-weight: 600;
            color: #15803d;
            margin-bottom: 0.5rem;
        }
        
        .recommendation-text {
            font-size: 0.875rem;
            color: #166534;
            line-height: 1.5;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .result-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .badge-moderate {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-low {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .badge-high {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Your Burnout Assessment Results</h1>
                <p class="text-gray-600">Based on your responses to the Oldenburg Burnout Inventory and related assessments</p>
            </div>

            <!-- Section 1: Burnout Assessment Results -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="section-title mb-0 pb-0 border-b-0">Your Burnout Assessment Results</h2>
                    <span class="badge-moderate">Moderate Risk</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-6xl font-bold text-amber-500 mb-2">62%</div>
                        <p class="text-gray-600 text-center">Overall Burnout Risk Score</p>
                        <p class="text-sm text-gray-500 text-center mt-2">Your assessment indicates a moderate level of burnout risk</p>
                    </div>
                    
                    <div class="bar-chart-container">
                        <div class="bar-item">
                            <div class="bar-label">Exhaustion</div>
                            <div class="bar-wrapper">
                                <div class="bar-fill" style="width: 68%;">
                                    <span class="bar-percentage">68%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bar-item">
                            <div class="bar-label">Disengagement</div>
                            <div class="bar-wrapper">
                                <div class="bar-fill" style="width: 55%;">
                                    <span class="bar-percentage">55%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bar-item">
                            <div class="bar-label">Stress Level</div>
                            <div class="bar-wrapper">
                                <div class="bar-fill" style="width: 62%;">
                                    <span class="bar-percentage">62%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bar-item">
                            <div class="bar-label">Sleep Issues</div>
                            <div class="bar-wrapper">
                                <div class="bar-fill" style="width: 71%;">
                                    <span class="bar-percentage">71%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bar-item">
                            <div class="bar-label">Procrastination</div>
                            <div class="bar-wrapper">
                                <div class="bar-fill" style="width: 58%;">
                                    <span class="bar-percentage">58%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Performance Insights -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="section-title">Performance Insights</h2>
                
                <div class="space-y-4">
                    <div class="insight-card">
                        <div class="insight-title">🔴 Highest Concern: Sleep Quality</div>
                        <div class="insight-text">Your sleep quality score (71%) is the highest among all factors. Poor sleep significantly impacts academic performance and mental health. Prioritizing sleep improvement could have the most immediate positive effect on your overall wellbeing.</div>
                    </div>
                    
                    <div class="insight-card">
                        <div class="insight-title">⚠️ Secondary Concern: Exhaustion</div>
                        <div class="insight-text">Your exhaustion level (68%) indicates you may be experiencing mental and physical fatigue. This is often interconnected with sleep quality and stress levels. Consider implementing rest and recovery strategies.</div>
                    </div>
                    
                    <div class="insight-card">
                        <div class="insight-title">✅ Positive Note: Engagement</div>
                        <div class="insight-text">Your disengagement score (55%) is relatively lower, suggesting you still maintain some motivation and interest in your academic work. This is a strength to build upon during your recovery.</div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Detailed Component Scores -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="section-title">Detailed Component Scores</h2>
                
                <div class="space-y-4">
                    <div class="component-score-item">
                        <div class="component-header">
                            <div class="component-name">Exhaustion (OLBI-16)</div>
                            <div class="component-score">68%</div>
                        </div>
                        <div class="component-description">Measures feelings of mental and physical fatigue, depletion of emotional resources, and reduced capacity to cope with academic demands.</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 68%;"></div>
                        </div>
                    </div>
                    
                    <div class="component-score-item">
                        <div class="component-header">
                            <div class="component-name">Disengagement (OLBI-16)</div>
                            <div class="component-score">55%</div>
                        </div>
                        <div class="component-description">Assesses loss of interest, motivation, and emotional distance from academic work and studies.</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 55%;"></div>
                        </div>
                    </div>
                    
                    <div class="component-score-item">
                        <div class="component-header">
                            <div class="component-name">Perceived Stress (PSS-4)</div>
                            <div class="component-score">62%</div>
                        </div>
                        <div class="component-description">Evaluates the degree to which situations in your life are perceived as stressful and overwhelming.</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 62%;"></div>
                        </div>
                    </div>
                    
                    <div class="component-score-item">
                        <div class="component-header">
                            <div class="component-name">Sleep Quality (SCI-8)</div>
                            <div class="component-score">71%</div>
                        </div>
                        <div class="component-description">Measures sleep quality, duration, and the impact of sleep issues on daily functioning.</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 71%;"></div>
                        </div>
                    </div>
                    
                    <div class="component-score-item">
                        <div class="component-header">
                            <div class="component-name">Academic Procrastination (APS)</div>
                            <div class="component-score">58%</div>
                        </div>
                        <div class="component-description">Assesses the tendency to delay or postpone academic tasks and assignments.</div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 58%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Personalized Recommendations -->
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="section-title">Personalized Recommendations</h2>
                
                <div class="recommendation-grid mb-8">
                    <div class="recommendation-card">
                        <div class="recommendation-icon">✓</div>
                        <div class="recommendation-title">Prioritize Sleep Hygiene</div>
                        <div class="recommendation-text">Establish a consistent sleep schedule, create a dark and quiet sleeping environment, and limit screen time before bed. Aim for 7-9 hours of quality sleep each night.</div>
                    </div>
                    
                    <div class="recommendation-card">
                        <div class="recommendation-icon">✓</div>
                        <div class="recommendation-title">Implement Stress Management</div>
                        <div class="recommendation-text">Practice mindfulness, meditation, or deep breathing exercises for 10-15 minutes daily. Consider activities like yoga, journaling, or nature walks to reduce stress levels.</div>
                    </div>
                    
                    <div class="recommendation-card">
                        <div class="recommendation-icon">✓</div>
                        <div class="recommendation-title">Break Tasks Into Smaller Steps</div>
                        <div class="recommendation-text">Combat procrastination by dividing assignments into manageable chunks. Use the Pomodoro technique (25 minutes work, 5 minutes break) to maintain focus and momentum.</div>
                    </div>
                    
                    <div class="recommendation-card">
                        <div class="recommendation-icon">✓</div>
                        <div class="recommendation-title">Seek Support & Resources</div>
                        <div class="recommendation-text">Connect with your school's counseling services, academic advisors, or peer support groups. Don't hesitate to reach out for professional help if burnout symptoms persist.</div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
                    <h3 class="font-semibold text-blue-900 mb-3">Next Steps</h3>
                    <ul class="space-y-2 text-blue-800 text-sm">
                        <li class="flex items-start">
                            <span class="mr-3">•</span>
                            <span>Schedule a consultation with your school's guidance counselor to discuss these results in detail</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-3">•</span>
                            <span>Create an action plan focusing on sleep improvement as your primary goal</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-3">•</span>
                            <span>Retake this assessment in 4-6 weeks to track your progress and adjust strategies as needed</span>
                        </li>
                        <li class="flex items-start">
                            <span class="mr-3">•</span>
                            <span>Remember: This tool is supportive and not a replacement for professional mental health assessment</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-gray-500 text-sm mt-12">
                <p>This assessment is designed to provide early indication of burnout risk for guidance and support purposes only.</p>
                <p class="mt-2">For professional mental health services, please contact your school's counseling office.</p>
            </div>

        </div>
    </div>
</body>
</html>
