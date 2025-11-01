@extends('layouts.app')

@section('title', 'Burnout Assessment Results - Burnalytics')
@section('subtitle', 'Assessment Results')

@section('content')
@php
    // Sample data - replace with actual assessment data
    $exhaustionCategory = request('exhaustion', 'High'); // High or Low
    $disengagementCategory = request('disengagement', 'High'); // High or Low
    
    // Determine burnout category
    if ($exhaustionCategory == 'Low' && $disengagementCategory == 'Low') {
        $category = 'low';
        $categoryName = 'Low Burnout';
        $categoryCode = 'Healthy Functioning';
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
@endphp

<div class="min-h-screen bg-gradient-to-b from-indigo-50 to-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Result Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg p-8 mb-6 text-white relative overflow-hidden shadow-lg">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-10 rounded-full -mr-48 -mt-48"></div>
            <h1 class="text-4xl font-bold mb-2 relative z-10">{{ $categoryName }}</h1>
            <p class="text-sm opacity-95 relative z-10">{{ $categoryCode }}</p>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <!-- Section 1: Main Result & Description -->
                <div class="bg-white rounded-xl p-7 shadow-sm border border-indigo-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-7 h-7 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white flex items-center justify-center text-sm font-bold mr-3">1</span>
                        Your Burnout Assessment Result
                    </h2>

                    @if($category == 'low')
                        <div class="text-sm text-gray-600 leading-relaxed space-y-3">
                            <p><strong class="text-gray-900">Result: Low Burnout</strong></p>
                            <p>You're currently maintaining a healthy balance between academic demands and personal resources. Energy levels are adequate, and engagement with studies remains positive. This indicates that current demands are manageable and sustainable.</p>
                            <p><strong class="text-gray-900">Your Assessment Results:</strong></p>
                            <p>Based on your responses, you scored below the threshold for both exhaustion and disengagement. This indicates Low Exhaustion and Low Disengagement, which is interpreted as <strong>Low Burnout</strong> or healthy academic functioning.</p>
                        </div>
                    @elseif($category == 'exhausted')
                        <div class="text-sm text-gray-600 leading-relaxed space-y-3">
                            <p><strong class="text-gray-900">Result: Exhausted</strong></p>
                            <p>You're experiencing significant physical and emotional fatigue while your interest in academic work remains intact. This "running on empty" pattern suggests sustained effort without adequate recovery time.</p>
                            <p><strong class="text-gray-900">Your Assessment Results:</strong></p>
                            <p>Based on your responses, you scored above the threshold for exhaustion but below the threshold for disengagement. This indicates High Exhaustion and Low Disengagement, which is interpreted as <strong>Exhausted</strong> - energy depletion with preserved motivation.</p>
                        </div>
                    @elseif($category == 'disengaged')
                        <div class="text-sm text-gray-600 leading-relaxed space-y-3">
                            <p><strong class="text-gray-900">Result: Disengaged</strong></p>
                            <p>You have adequate energy levels, but your connection to and interest in academic work have diminished. This "capacity without engagement" pattern suggests concerns about meaning, fit, or motivation rather than physical depletion.</p>
                            <p><strong class="text-gray-900">Your Assessment Results:</strong></p>
                            <p>Based on your responses, you scored below the threshold for exhaustion but above the threshold for disengagement. This indicates Low Exhaustion and High Disengagement, which is interpreted as <strong>Disengaged</strong> - preserved energy with reduced motivation and connection.</p>
                        </div>
                    @else
                        <div class="text-sm text-gray-600 leading-relaxed space-y-3">
                            <p><strong class="text-gray-900">Result: High Burnout</strong></p>
                            <p>You're experiencing both significant physical/emotional exhaustion and psychological withdrawal from academic work. This represents advanced burnout affecting multiple areas of your functioning and requires immediate attention.</p>
                            <p><strong class="text-gray-900">Your Assessment Results:</strong></p>
                            <p>Based on your responses, you scored above the threshold for both exhaustion and disengagement. This indicates High Exhaustion and High Disengagement, which is interpreted as <strong>High Burnout</strong> - the most serious burnout pattern combining energy depletion with motivational loss.</p>
                        </div>
                    @endif
                </div>
                
                <!-- Section 3: Interpretation -->
                <div class="bg-white rounded-xl p-7 shadow-sm border border-indigo-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-7 h-7 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white flex items-center justify-center text-sm font-bold mr-3">3</span>
                        Interpretation: What This Means
                    </h2>

                    @if($category == 'low')
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Low Exhaustion Results:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">Your energy levels are within a healthy range. You're able to manage study demands without experiencing chronic fatigue or emotional depletion. This suggests adequate rest, effective time management, and sustainable study habits.</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Low Disengagement Results:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">You maintain positive interest and connection to your academic work. Your motivation remains intact, and you continue to find meaning and value in your studies. This indicates good alignment between your academic path and personal interests or goals.</p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Combined Interpretation:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">With both dimensions in the healthy range, you're functioning well academically. You have the energy to engage with your studies and the motivation to sustain that engagement. This is the ideal state for academic success and personal wellbeing.</p>
                            </div>
                        </div>
                    @elseif($category == 'exhausted')
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">High Exhaustion Results:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">You're experiencing significant tiredness and emotional fatigue related to your studies. You may feel drained, need more recovery time than usual, experience physical symptoms (headaches, sleep problems), or feel mentally depleted. This suggests you've been operating without sufficient rest or recovery periods.</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Low Disengagement Results:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">Despite your exhaustion, you maintain positive interest in your studies. You still find your academic work meaningful and want to succeed. Your motivation remains intact - you haven't "checked out" emotionally or psychologically from your studies. This is actually a protective factor.</p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Combined Interpretation:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">You're in a state of energy depletion without motivational loss. Think of it like a phone battery at 10% - the device still works and wants to run apps, but doesn't have the power. The good news is that your preserved interest means that with proper rest and workload adjustment, you can recover without developing deeper burnout symptoms.</p>
                            </div>
                        </div>
                    @elseif($category == 'disengaged')
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Low Exhaustion Results:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">Your energy levels are adequate - you're not chronically tired or physically depleted. You have the capacity to engage with academic work from a physical and mental energy standpoint. This means the issue isn't about needing more rest or reducing workload.</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">High Disengagement Results:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">You're experiencing psychological and emotional disconnection from your studies. You may feel detached, indifferent, or cynical toward your coursework. Academic tasks might feel meaningless or mechanical. You may be questioning the value or relevance of what you're studying, or feeling that your studies don't align with your interests or goals.</p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Combined Interpretation:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">You're in a state where you have the energy to do the work, but you lack the interest or motivation to engage meaningfully with it. Think of it like having a full phone battery but no desire to use any apps. The challenge isn't capacity - it's connection. This often signals questions about academic fit, career direction, or life priorities that need exploration.</p>
                            </div>
                        </div>
                    @else
                        <div class="space-y-5">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">High Exhaustion Results:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">You're experiencing severe physical and emotional fatigue. You may feel constantly drained, unable to recover even with rest, experience frequent physical symptoms (headaches, illness, sleep problems), and feel mentally depleted. Your energy reserves are critically low, making even basic tasks feel overwhelming.</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">High Disengagement Results:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">You've psychologically withdrawn from your academic work. You likely feel detached, cynical, or indifferent toward your studies. Academic tasks may feel meaningless or pointless. You may be questioning your ability to continue, considering dropping out, or feeling that nothing about your studies matters anymore.</p>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Combined Interpretation:</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">You're in a crisis state where you're both physically exhausted and emotionally disconnected - the "I can't and I don't want to" combination. This is the most serious burnout pattern. You're running on empty without the motivation to refuel. This affects not just academics, but likely your relationships, health, and overall quality of life. This is beyond normal academic stress and requires immediate professional support.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Section 2: Predictive Model Analysis -->
                <div class="bg-white rounded-xl p-7 shadow-sm border border-indigo-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-7 h-7 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white flex items-center justify-center text-sm font-bold mr-3">2</span>
                        Predictive Model Analysis
                    </h2>

                    @if($category == 'low')
                        <div class="text-sm text-gray-600 leading-relaxed">
                            <p>According to the predictive model analysis, you are experiencing minimal burnout indicators with both low exhaustion and low disengagement scores. This suggests that your current academic workload is well-balanced with adequate recovery time, and your connection to your studies remains strong. The model predicts a low risk for developing burnout symptoms in the near future if current patterns are maintained.</p>
                        </div>
                    @elseif($category == 'exhausted')
                        <div class="text-sm text-gray-600 leading-relaxed">
                            <p>According to the predictive model analysis, you are experiencing elevated exhaustion levels while maintaining engagement with your studies. This pattern indicates that you're working hard and still care about your academic success, but you're physically and emotionally depleted. The model identifies this as a "still caring but too tired" profile, which, if unaddressed, may progress to more severe burnout with both exhaustion and disengagement present.</p>
                        </div>
                    @elseif($category == 'disengaged')
                        <div class="text-sm text-gray-600 leading-relaxed">
                            <p>According to the predictive model analysis, you are experiencing elevated disengagement while maintaining adequate energy levels. This pattern suggests that the issue isn't about being too tired, but rather about feeling disconnected from your academic work. The model identifies concerns about meaning, purpose, or fit between your academic path and your interests or values. Without intervention, this disengagement may eventually lead to exhaustion as continuing through unmotivating work becomes increasingly draining.</p>
                        </div>
                    @else
                        <div class="text-sm text-gray-600 leading-relaxed">
                            <p>According to the predictive model analysis, you are experiencing elevated levels across both burnout dimensions. This "exhausted and don't care anymore" pattern indicates that burnout has progressed beyond early stages. The model predicts significant risk to your academic performance, mental health, and overall wellbeing if immediate intervention is not pursued. This level of burnout can lead to academic failure, health problems, or progression to clinical depression or anxiety without proper support.</p>
                        </div>
                    @endif
                </div>

                <!-- Section 4: Recommendations -->
                <div class="bg-white rounded-xl p-7 shadow-sm border border-indigo-100">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="w-7 h-7 rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 text-white flex items-center justify-center text-sm font-bold mr-3">4</span>
                        Recommendations
                    </h2>

                    @if($category == 'low')
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Maintain Your Current Approach</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">You're doing well with your current strategies. Continue the study routines, self-care practices, and time management techniques that are working for you.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Preserve the balance between academic work, rest, and personal activities that supports your wellbeing.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Build Preventive Resilience</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">While you're currently in a good place, consider building resilience for future high-demand periods.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Develop stress management techniques such as deep breathing, mindfulness, or brief relaxation breaks.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Identify campus resources (counseling, tutoring, health services) before you need them urgently.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Stay Aware</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Monitor your energy and motivation levels, especially during exam periods or when facing heavy assignment loads.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Early recognition of changes allows for timely adjustment before burnout develops.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Set boundaries around study time and personal time, and practice saying no to commitments that would create overload.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Keep Healthy Habits</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Maintain a regular sleep schedule (7-9 hours nightly), stay physically active, and preserve social connections.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">These protective factors help prevent burnout even when academic demands increase.</li>
                                </ul>
                            </div>
                        </div>

                    @elseif($category == 'exhausted')
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Immediate Priority - Address Fatigue</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Sleep must become non-negotiable. Aim for 7-9 hours nightly with a consistent bedtime.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Take scheduled 10-minute breaks during every hour of study.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Your exhaustion won't improve without dedicated recovery time, so reduce commitments where possible to create space for rest.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Energy Management</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Schedule your most demanding academic tasks during your peak energy times of day.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Break large projects into smaller, manageable portions so you can maintain momentum without overwhelming yourself.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Use brief physical movement (walks, stretching) to combat mental fatigue rather than pushing through exhaustion.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Restore Balance</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Engage in activities completely unrelated to academics that you find enjoyable or relaxing.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Protect time for social connections and relationships - isolation worsens exhaustion.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Practice relaxation techniques like progressive muscle relaxation or guided imagery.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">If you're working alongside studying, evaluate whether your work hours are sustainable.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Seek Support</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Connect with academic advisors about course load or timeline adjustments.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Consider counseling services to develop stress management strategies.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">If you're struggling with specific subjects, tutoring or study groups can reduce the individual burden.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Don't wait until exhaustion becomes severe - early intervention is more effective.</li>
                                </ul>
                            </div>

                            <div class="mt-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Warning Signs to Monitor</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">If exhaustion continues despite rest efforts, if your academic performance declines, or if physical symptoms persist (frequent headaches, illness, sleep problems), professional support may be needed to identify underlying causes or develop more comprehensive recovery strategies.</p>
                            </div>
                        </div>
                    
                    @elseif($category == 'disengaged')
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Explore the Disconnection</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Take time to identify specifically what feels least engaging - is it certain courses, assignments, teaching styles, or the field itself?</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Reflect on what initially drew you to this field of study and whether those reasons still resonate.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Consider whether external factors (relationships, family stress, financial concerns) might be affecting your ability to connect with academics.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Reconnect with Purpose</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Look for ways to connect your coursework to real-world applications or future career plans that excite you.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Seek opportunities to apply learning in practical ways - internships, volunteer work, projects.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Consider informational interviews with professionals in your field to understand career possibilities and reignite interest.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Vary Your Approach</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Change your study methods or environment to reduce monotony - try different locations, times, or formats.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Form study groups to add a social dimension to learning.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Look for the most challenging or novel aspects of your courses that might spark curiosity.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Set small, achievable goals to rebuild a sense of progress and accomplishment.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Seek Guidance</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Meet with your academic advisor to discuss concerns about your major or program fit.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Access career counseling services to clarify goals and explore options.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Talk with faculty or mentors about their experiences in the field and what keeps them engaged.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Consider whether counseling services could help you work through what's creating the disconnection.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Consider Path Exploration</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">If disengagement persists, explore whether a different specialization, minor, or elective courses might improve your connection to studies.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Understand that changing academic direction is common and acceptable - many students adjust their path.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Remember that addressing disengagement early prevents more serious problems, including eventual exhaustion from forcing yourself through unmotivating work.</li>
                                </ul>
                            </div>

                            <div class="mt-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Important Note</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">Disengagement may signal a need for program re-evaluation, but it can also result from temporary factors like a difficult semester, challenging instructor, or personal stressors. Professional guidance can help you distinguish between needing a temporary adjustment versus a more significant path change.</p>
                            </div>
                        </div>
                    
                    @else
                        <div class="space-y-4">
                            <div class="mt-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-3">Immediate Action Required (Within Next Few Days)</h3>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Schedule an appointment with campus counseling services immediately - do not wait.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Meet with your academic advisor to review your current status and discuss available options (reduced course load, incomplete grades, withdrawal, leave of absence).</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Inform at least one trusted person (friend, family member, mentor) about what you're experiencing - do not try to handle this alone.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Assess whether your basic needs (sleep, food, safety) are being adequately met.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Evaluate Academic Sustainability</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Honestly assess whether continuing at your current pace is possible this semester.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Explore institutional options: Can you reduce your course load? Take incomplete grades and finish later? Withdraw from specific courses? Take a temporary leave?</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Understand the policies and deadlines for these options.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Recognize that temporary academic adjustment now may prevent longer-term negative consequences like academic dismissal or serious health problems.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Crisis Management</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Identify your most pressing stressors beyond academics (financial problems, relationship issues, family concerns, health problems).</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Determine which stressors can be modified, reduced, or temporarily set aside.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">This is survival mode - focus on minimum requirements, not ideal performance.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Accept that you cannot maintain previous standards right now, and that's okay in crisis situations.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Access Multiple Support Systems</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Don't rely on just one resource. Utilize campus counseling, health services, academic support centers, and financial aid offices as needed.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Connect with instructors individually to explain your situation and discuss potential accommodations.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Reach out to friends or family rather than isolating yourself.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Consider whether you need professional mental health treatment beyond campus resources.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Non-Negotiable Self-Care</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Sleep must be prioritized even if assignments remain unfinished - sleep deprivation worsens everything.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Maintain basic nutrition with regular meals, even if they're simple.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Engage in brief daily movement, even just a 10-minute walk.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Absolutely avoid using alcohol or drugs as coping mechanisms - they worsen burnout and can trigger additional problems.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Develop Concrete Recovery Plan</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Work with counselors or advisors to create specific, realistic action steps - not vague goals.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Identify concrete changes: "Drop one class" not "reduce stress." "Meet counselor weekly" not "get help." "Sleep by midnight" not "rest more."</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Set very modest short-term goals (today, this week) rather than overwhelming long-term plans.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Understand that recovery takes weeks to months, not days.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Monitor Mental Health Closely</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Track symptoms of depression: persistent sadness lasting most of the day, loss of interest in all activities (not just studies), feelings of hopelessness or worthlessness, thoughts of death or self-harm.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Notice anxiety symptoms: constant worry, panic attacks, inability to concentrate on anything.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Be aware of significant changes in sleep, appetite, or social withdrawal beyond your academic stress.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Understand that burnout can co-exist with or develop into depression or anxiety disorders that require clinical treatment.</li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Reconsider Your Academic Path</h4>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Ask yourself honestly: Is this program right for me? Is this institution a good fit? Is now the right time for this degree?</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Recognize that changing direction, transferring, or taking time off is not failure - it's making a mature decision about your wellbeing and future.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Mental and physical health must take priority over academic timelines or external expectations.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">Many successful people have changed paths, transferred schools, or taken breaks during their education.</li>
                                </ul>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-3">Critical Warning - Seek Emergency Help If:</h3>
                                <ul class="space-y-2 text-sm text-gray-600 list-none pl-6">
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">You experience thoughts of self-harm or feeling that life is not worth living (call campus emergency services, 988 Suicide & Crisis Lifeline, or 911).</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">You feel complete inability to function or get out of bed.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">You experience severe physical symptoms or significant health changes.</li>
                                    <li class="relative pl-5 before:content-['•'] before:absolute before:left-0 before:text-gray-400">You feel completely isolated with no one to turn to.</li>
                                </ul>
                            </div>

                            <div class="mt-6">
                                <h4 class="text-sm font-semibold text-gray-900 mb-2">Remember</h4>
                                <p class="text-sm text-gray-600 leading-relaxed">This level of burnout is a serious condition that requires professional intervention. Reaching out for help is not weakness - it's the necessary and courageous action needed to recover.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
