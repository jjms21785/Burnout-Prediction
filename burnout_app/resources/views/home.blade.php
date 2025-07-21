@extends('layouts.app')

@section('title', 'Burnalytix - Predicting Academic Burnout')

@section('content')
<!-- Hero Section -->
<section class="py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">Predicting Academic Burnout</h1>
        <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
            Academic burnout is a psychological syndrome characterized by emotional exhaustion, cynicism and reduced
            academic efficacy among students. Our system helps identify burnout risk early, enabling timely intervention
            and support for the student wellbeing.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="#how-burnalytix-helps" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition-colors scroll-smooth">
                Get Started
            </a>
            <a href="{{ route('assessment.index') }}" class="border border-green-600 text-green-600 hover:bg-green-50 px-8 py-3 rounded-lg font-medium transition-colors">
                Take Assessment Now
            </a>
        </div>
    </div>
</section>

<!-- How Burnalytix Helps -->
<section id="how-burnalytix-helps" class="py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">How Burnalytix Helps</h2>
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white border border-green-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-green-800 mb-4">Assessment</h3>
                    <p class="text-gray-600">
                        A science-backed learning algorithm analyze 22 psychometric indicators to predict burnout risk with high accuracy
                    </p>
                </div>
            </div>

            <div class="bg-white border border-green-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-green-800 mb-4">Early Intervention</h3>
                    <p class="text-gray-600">
                        Identify at-risk students before burnout becomes severe, enabling timely support and intervention strategies.
                    </p>
                </div>
            </div>

            <div class="bg-white border border-green-200 rounded-lg p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-green-800 mb-4">Personalized Recommendation</h3>
                    <p class="text-gray-600">
                        Receive tailored guidance and resources based on your specific burnout risk level and assessment results.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Understanding Academic Burnout -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-green-50">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-8">Understanding Academic Burnout</h2>
        <p class="text-center text-gray-600 mb-12 max-w-4xl mx-auto">
            Academic burnout impacts millions of students globally, resulting in lower performance, mental health
            challenges, and even academic dropout. Our system is designed to detect early warning signs and offer
            support before burnout escalates.
        </p>

        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white border border-red-200 rounded-lg p-6">
                <h3 class="text-xl font-semibold text-green-800 text-center mb-6">Warning Signs</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Emotional exhaustion from academic demands
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Cynical attitudes towards studies
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Reduced sense of academic accomplishment
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Physical and mental fatigue
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Decreased motivation and engagement
                    </li>
                </ul>
            </div>

            <div class="bg-white border border-green-200 rounded-lg p-6">
                <h3 class="text-xl font-semibold text-green-800 text-center mb-6">Our Solution</h3>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Quick 22-question assessment
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Personalized recommendations
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Evidence-based intervention strategies
                    </li>
                    <li class="flex items-start">
                        <span class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        Confidential and secure assessment
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 px-4 sm:px-6 lg:px-8 bg-green-600">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl font-bold text-white mb-8">Ready to Assess your Burnout Risk?</h2>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('assessment.index') }}" class="bg-white text-green-600 hover:bg-gray-100 px-8 py-3 rounded-lg font-medium transition-colors">
                Start Assessment Now?
            </a>
            <a href="{{ route('admin.dashboard') }}" class="border border-white text-white hover:bg-white hover:text-green-600 px-8 py-3 rounded-lg font-medium transition-colors">
                Go to Admin Portal?
            </a>
        </div>
    </div>
</section>
@endsection