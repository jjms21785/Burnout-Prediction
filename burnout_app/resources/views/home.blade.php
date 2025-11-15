<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burnalytix - Academic Burnout Prediction</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-white min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="block">
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-indigo-500 to-indigo-600 bg-clip-text text-transparent cursor-pointer hover:opacity-80 transition-opacity">Burnalytics</h1>
                    </a>
                </div>
                <nav class="flex items-center space-x-8">
                    <a href="#about" class="text-gray-600 hover:text-indigo-500 transition-all duration-200 transform hover:scale-105">About</a>
                    @auth
                        <div class="relative" id="userMenuContainer">
                            <button type="button" id="userMenuButton" class="text-gray-600 hover:text-indigo-500 transition-all duration-200 transform hover:scale-105 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </button>
                            <div id="userMenuDropdown" class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg opacity-0 invisible transition-all duration-200 z-50">
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-t-md">Dashboard</a>
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-b-md">Log Out</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-indigo-500 transition-all duration-200 transform hover:scale-105">Log In</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="flex items-center justify-center px-4 py-20 bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">
        <div class="max-w-2xl w-full text-center space-y-8">
            <div class="space-y-6">
                <h2 class="text-5xl md:text-6xl font-bold text-blue-600">
                    Burnalytix
                </h2>
                
                <p class="text-xl text-slate-600 leading-relaxed">
                    Identify academic burnout early using machine learning.
                </p>
                <p class="text-xl text-slate-600 leading-relaxed">
                    Take 5-10 mins test and see your burnout result description.
                </p>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                <a
                    href="{{ route('assessment.index') }}"
                    class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all duration-200 transform hover:scale-105 shadow-md hover:shadow-lg"
                >
                    Take Assessment
                </a>
                <a
                    href="#learn-more"
                    class="px-8 py-3 border-2 border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-all duration-200 transform hover:scale-105"
                >
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="px-4 py-20 bg-white">
        <div class="max-w-4xl mx-auto space-y-12">
            <div class="text-center space-y-4 mb-12">
                <h2 class="text-4xl md:text-5xl font-bold text-blue-600">About Burnalytix</h2>
                <p class="text-lg text-slate-600">Understanding Academic Burnout</p>
            </div>

            <!-- Problem -->
            <div class="space-y-4">
                <p class="text-slate-700 leading-relaxed">
                    Student burnout is a serious issue in higher education. Traditional methods rely on students self-reporting their problems or teachers noticing obvious signs. By the time help arrives, students may already be failing classes or considering dropping out. Many students avoid seeking help due to stigma or not realizing how serious their situation is.
                </p>
            </div>

            <!-- Solution -->
            <div class="space-y-4">
                <p class="text-slate-700 leading-relaxed">
                    Burnalytix uses machine learning (Random Forest algorithm) to predict academic burnout before visible symptoms appear. This tool analyzes student responses to identify who is at risk, enabling counselors to provide early intervention and support even for students who haven't asked for help.
                </p>
            </div>

            <!-- Who Benefits -->
            <div class="space-y-6">
                <h3 class="text-2xl font-bold text-slate-900">Who Benefits</h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="p-6 bg-blue-50 rounded-lg space-y-2">
                        <h4 class="font-bold text-blue-600">Students</h4>
                        <p class="text-slate-700 text-sm">Receive early warning about burnout and actionable recommendations for managing stress before performance declines.</p>
                    </div>
                    <div class="p-6 bg-blue-50 rounded-lg space-y-2">
                        <h4 class="font-bold text-blue-600">Counselors</h4>
                        <p class="text-slate-700 text-sm">Identify at-risk students automatically, prioritize interventions, and make data-driven decisions with visual dashboards.</p>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="space-y-6">
                <h3 class="text-2xl font-bold text-slate-900">How It Works</h3>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Complete the Assessment</h4>
                            <p class="text-slate-700">Answer validated questions about your exhaustion, engagement, stress, and sleep quality.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Machine Learning Analysis</h4>
                            <p class="text-slate-700">Our Random Forest model analyzes your responses and identifies patterns in your burnout risk.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">3</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Get Your Results</h4>
                            <p class="text-slate-700">Receive a detailed breakdown of your burnout level with personalized recommendations.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">4</div>
                        <div>
                            <h4 class="font-bold text-slate-900">Connect with Support</h4>
                            <p class="text-slate-700">Counselors review high-risk assessments and reach out with personalized guidance and resources.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Features -->
            <div class="space-y-4">
                <h3 class="text-2xl font-bold text-slate-900">Key Features</h3>
                <ul class="space-y-2 text-slate-700">
                    <li class="flex gap-2">
                        <span class="text-blue-600 font-bold">✓</span>
                        <span><strong>94.68% Accuracy</strong> - Random Forest model validated on 467 students</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-blue-600 font-bold">✓</span>
                        <span><strong>Four Burnout Categories</strong> - Low Burnout, Exhausted, Disengaged, or High Burnout</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-blue-600 font-bold">✓</span>
                        <span><strong>Validated Assessment</strong> - Based on OLBI-S, PSS-4, and SCI-8 instruments</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-blue-600 font-bold">✓</span>
                        <span><strong>Instant Feedback</strong> - Immediate results with personalized recommendations</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Learn More Section -->
    <section id="learn-more" class="px-4 py-20 bg-slate-50">
        <div class="max-w-4xl mx-auto space-y-12">
            <div class="text-center space-y-4 mb-12">
                <h2 class="text-4xl md:text-5xl font-bold text-blue-600">Learn More</h2>
                <p class="text-lg text-slate-600">Deep Dive into the System</p>
            </div>

            <!-- The Assessment -->
            <div class="space-y-6">
                <h3 class="text-2xl font-bold text-slate-900">The Assessment</h3>
                <p class="text-slate-700 leading-relaxed">
                    Burnalytix uses a comprehensive assessment that measures four key dimensions of student well-being:
                </p>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="p-6 bg-white rounded-lg border border-slate-200 space-y-3">
                        <h4 class="font-bold text-slate-900">Burnout (OLBI-S)</h4>
                        <p class="text-slate-700 text-sm">Measuring exhaustion and disengagement, the two core dimensions of academic burnout.</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg border border-slate-200 space-y-3">
                        <h4 class="font-bold text-slate-900">Stress (PSS-4)</h4>
                        <p class="text-slate-700 text-sm">Assessing perceived stress and ability to manage life events.</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg border border-slate-200 space-y-3">
                        <h4 class="font-bold text-slate-900">Sleep (SCI-8)</h4>
                        <p class="text-slate-700 text-sm">Evaluating sleep quality and insomnia symptoms that affect academic performance.</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg border border-slate-200 space-y-3">
                        <h4 class="font-bold text-slate-900">Academic Performance</h4>
                        <p class="text-slate-700 text-sm">About grades and study habits to contextualize burnout within academic success.</p>
                    </div>
                </div>
            </div>

            <!-- Burnout Categories -->
            <div class="space-y-6">
                <h3 class="text-2xl font-bold text-slate-900">Burnout Categories</h3>
                <p class="text-slate-700 leading-relaxed">
                    Your results are classified into one of four categories based on exhaustion and disengagement levels:
                </p>
                <div class="space-y-4">
                    <div class="p-6 bg-white rounded-lg border-l-4 border-green-500 space-y-2">
                        <h4 class="font-bold text-green-600">Low Burnout (Green)</h4>
                        <p class="text-slate-700 text-sm">Low exhaustion and low disengagement. You're managing well; maintain your current strategies.</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg border-l-4 border-yellow-500 space-y-2">
                        <h4 class="font-bold text-yellow-600">Exhausted (Yellow)</h4>
                        <p class="text-slate-700 text-sm">High exhaustion but low disengagement. You're still engaged but feeling tired, prioritize rest and recovery.</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg border-l-4 border-yellow-500 space-y-2">
                        <h4 class="font-bold text-yellow-600">Disengaged (Yellow)</h4>
                        <p class="text-slate-700 text-sm">Low exhaustion but high disengagement. You may be losing motivation, explore what's affecting your engagement.</p>
                    </div>
                    <div class="p-6 bg-white rounded-lg border-l-4 border-red-500 space-y-2">
                        <h4 class="font-bold text-red-600">High Burnout (Red)</h4>
                        <p class="text-slate-700 text-sm">High exhaustion and high disengagement. Seek support from counselors immediately.</p>
                    </div>
                </div>
            </div>

            <!-- Machine Learning -->
            <div class="space-y-6">
                <h3 class="text-2xl font-bold text-slate-900">How Machine Learning Works</h3>
                <p class="text-slate-700 leading-relaxed">
                    Burnalytix uses the <strong>Random Forest algorithm</strong>, which combines hundreds of decision trees to make accurate predictions. Each tree learns patterns from student assessment data, and they "vote" on the final result. This approach:
                </p>
                <ul class="space-y-2 text-slate-700">
                    <li class="flex gap-2">
                        <span class="text-blue-600 font-bold">•</span>
                        <span>Identifies complex patterns that humans might miss</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-blue-600 font-bold">•</span>
                        <span>Handles missing or incomplete data gracefully</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-blue-600 font-bold">•</span>
                        <span>Provides explainable results (not a "black box")</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-blue-600 font-bold">•</span>
                        <span>Achieved 94.68% accuracy on validation testing</span>
                    </li>
                </ul>
            </div>

            <!-- For Students -->
            <div class="space-y-6 bg-white p-8 rounded-lg">
                <h3 class="text-2xl font-bold text-slate-900">For Students</h3>
                <ol class="space-y-4 text-slate-700">
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">1.</span>
                        <span>Click "Take Assessment" to begin the 10-minute questionnaire</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">2.</span>
                        <span>Enter your personal details (name, age, year level, program)</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">3.</span>
                        <span>Answer all questions honestly on the provided scale</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">4.</span>
                        <span>Receive your burnout classification and personalized recommendations</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">5.</span>
                        <span>Save your unique results code to revisit your assessment anytime</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">6.</span>
                        <span>Connect with guidance counselors if recommended based on your results</span>
                    </li>
                </ol>
            </div>

            <!-- For Counselors -->
            <div class="space-y-6 bg-white p-8 rounded-lg">
                <h3 class="text-2xl font-bold text-slate-900">For Guidance Counselors</h3>
                <ol class="space-y-4 text-slate-700">
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">1.</span>
                        <span>Log in to view the dashboard with all student assessments</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">2.</span>
                        <span>See burnout distribution and demographic breakdowns at a glance</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">3.</span>
                        <span>Filter students by burnout category to prioritize high-risk cases</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">4.</span>
                        <span>View detailed assessment results including exhaustion, disengagement, stress, and sleep quality scores</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">5.</span>
                        <span>Send personalized emails to students with guidance and appointment scheduling</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="font-bold text-blue-600">6.</span>
                        <span>Export reports in Excel or PDF for institutional analysis and documentation</span>
                    </li>
                </ol>
            </div>

            <!-- Important Disclaimers -->
            <div class="space-y-4 p-6 bg-slate-100 rounded-lg border border-slate-300">
                <h3 class="text-xl font-bold text-slate-900">Important Disclaimers</h3>
                <ul class="space-y-2 text-slate-700 text-sm">
                    <li class="flex gap-2">
                        <span class="text-slate-600">•</span>
                        <span>Burnalytix does NOT provide clinical diagnosis. Only qualified mental health professionals can diagnose psychological conditions.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-slate-600">•</span>
                        <span>This system is designed to support counselors, not replace them. Results should always be reviewed by qualified professionals.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-slate-600">•</span>
                        <span>All student data is anonymized, encrypted, and stored securely with access restricted to authorized personnel.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="text-slate-600">•</span>
                        <span>High-risk students identified by the system are referred to professional counselors and mental health services.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Footer CTA -->
    <section class="px-4 py-16 bg-blue-600">
        <div class="max-w-2xl mx-auto text-center space-y-6">
            <h3 class="text-3xl md:text-4xl font-bold text-white">Ready to know your burnout results?</h3>
            <a
                href="{{ route('assessment.index') }}"
                class="inline-block px-8 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition-colors shadow-md hover:shadow-lg"
            >
                Take Assessment
            </a>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuButton = document.getElementById('userMenuButton');
            const userMenuDropdown = document.getElementById('userMenuDropdown');
            
            if (userMenuButton && userMenuDropdown) {
                userMenuButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isVisible = userMenuDropdown.classList.contains('opacity-100');
                    
                    if (isVisible) {
                        userMenuDropdown.classList.remove('opacity-100', 'visible');
                        userMenuDropdown.classList.add('opacity-0', 'invisible');
                    } else {
                        userMenuDropdown.classList.remove('opacity-0', 'invisible');
                        userMenuDropdown.classList.add('opacity-100', 'visible');
                    }
                });
                
                document.addEventListener('click', function(e) {
                    const container = document.getElementById('userMenuContainer');
                    if (container && !container.contains(e.target)) {
                        userMenuDropdown.classList.remove('opacity-100', 'visible');
                        userMenuDropdown.classList.add('opacity-0', 'invisible');
                    }
                });
            }
        });
    </script>
</body>
</html>
