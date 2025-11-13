<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burnalytics Intervention</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #6366f1;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #6366f1;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #6366f1;
            margin-bottom: 10px;
            border-left: 4px solid #6366f1;
            padding-left: 10px;
        }
        .assessment-summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .assessment-summary p {
            margin: 8px 0;
            font-size: 14px;
        }
        .assessment-summary strong {
            color: #333;
        }
        .category-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .category-high {
            background-color: #fee2e2;
            color: #dc2626;
        }
        .category-exhausted {
            background-color: #fed7aa;
            color: #ea580c;
        }
        .category-disengaged {
            background-color: #fed7aa;
            color: #ea580c;
        }
        .category-low {
            background-color: #dcfce7;
            color: #16a34a;
        }
        .recommendations {
            background-color: #f0f9ff;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        .recommendations ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .recommendations li {
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.5;
        }
        .appointment-box {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .appointment-box p {
            margin: 5px 0;
            font-size: 15px;
        }
        .appointment-box strong {
            color: #92400e;
        }
        .message-box {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin: 15px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
        .contact-info {
            background-color: #f0fdf4;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .contact-info p {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Burnalytics</h1>
            <p>Student Burnout Assessment & Support</p>
        </div>

        <!-- Greeting -->
        <div class="greeting">
            <p>Dear {{ $emailData['studentName'] }},</p>
        </div>

        <!-- Introduction -->
        <div class="section">
            @if(!empty($emailData['appointmentDatetime']))
                <p>We hope this message finds you well. Following your recent burnout assessment, we would like to schedule a counseling session with you to provide support and guidance.</p>
            @else
                <p>We're reaching out regarding your recent burnout assessment. Your well-being is important to us, and we want to ensure you're getting the support you need. Please review the information below carefully.</p>
            @endif
        </div>

        <!-- Assessment Summary -->
        <div class="section">
            <div class="section-title">Assessment Summary</div>
            <div class="assessment-summary">
                <p><strong>Burnout Category:</strong> 
                    @php
                        $categoryClass = 'category-low';
                        if ($emailData['category'] === 'High Burnout') {
                            $categoryClass = 'category-high';
                        } elseif ($emailData['category'] === 'Exhausted') {
                            $categoryClass = 'category-exhausted';
                        } elseif ($emailData['category'] === 'Disengaged') {
                            $categoryClass = 'category-disengaged';
                        }
                    @endphp
                    <span class="category-badge {{ $categoryClass }}">{{ $emailData['category'] }}</span>
                </p>
                <p><strong>Exhaustion Score:</strong> {{ $emailData['exhaustionScore'] ?? 'N/A' }}/32</p>
                <p><strong>Disengagement Score:</strong> {{ $emailData['disengagementScore'] ?? 'N/A' }}/32</p>
            </div>
        </div>

        <!-- Appointment Details (if scheduled) -->
        @if(!empty($emailData['appointmentDatetime']))
        <div class="appointment-box">
            <p style="margin-top: 0;"><strong> Scheduled Counseling Session</strong></p>
            <p><strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($emailData['appointmentDatetime'])->format('F d, Y \a\t g:i A') }}</p>
            <p style="margin-bottom: 0;">Please mark this in your calendar. If you need to reschedule, please contact us as soon as possible.</p>
        </div>
        @endif

        <!-- Recommended Interventions -->
        @if(!empty($emailData['recommendations']))
        <div class="section">
            <div class="section-title">Recommended Interventions</div>
            <div class="recommendations">
                <p style="margin-top: 0;">Based on your assessment results, we recommend the following interventions:</p>
                @if(is_array($emailData['recommendations']))
                    <ul>
                        @foreach($emailData['recommendations'] as $recommendation)
                            <li>{{ $recommendation }}</li>
                        @endforeach
                    </ul>
                @elseif(is_string($emailData['recommendations']))
                    <p>{{ $emailData['recommendations'] }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Additional Message from Counselor -->
        @if(!empty($emailData['additionalMessage']))
        <div class="section">
            <div class="section-title">Message from Your Counselor</div>
            <div class="message-box">
                <p style="margin: 0; white-space: pre-line;">{{ $emailData['additionalMessage'] }}</p>
            </div>
        </div>
        @endif


        <!-- Footer -->
        <div class="footer">
            <p>This is an automated message from the Burnalytics system.</p>
            <p>© {{ date('Y') }} Burnalytics. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

