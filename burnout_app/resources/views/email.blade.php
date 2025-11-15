<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burnalytics - Academic Wellbeing Support</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f5f5f5; margin: 0; padding: 20px;">
    
    <!-- Main Container -->
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 0.5px;">
                Burnalytics
            </h1>
            <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px;">
                Academic Burnout Support System
            </p>
        </div>
        
        <!-- Content -->
        <div style="padding: 30px 20px;">
            
            <!-- Greeting -->
            <p style="font-size: 16px; margin-top: 0;">
                Dear <strong>{{ $emailData['studentName'] ?? 'Student' }}</strong>,
            </p>
            
            <!-- Introduction -->
            <p style="font-size: 15px; color: #555; line-height: 1.7;">
                I hope this message finds you well. I'm reaching out from the Guidance Counseling Office regarding your recent burnout assessment completed through Burnalytics on 
                <strong>{{ isset($emailData['assessmentDate']) ? \Carbon\Carbon::parse($emailData['assessmentDate'])->format('F d, Y') : '' }}</strong>.
            </p>
            
            <!-- Assessment Summary (Optional - if data available) -->
            @if(!empty($emailData['burnoutCategory']))
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid 
                @if($emailData['burnoutCategory'] === 'BURNOUT' || $emailData['burnoutCategory'] === 'Burnout')
                    #dc2626
                @elseif($emailData['burnoutCategory'] === 'Exhausted')
                    #f59e0b
                @elseif($emailData['burnoutCategory'] === 'Disengaged')
                    #3b82f6
                @else
                    #10b981
                @endif
            ;">
                <p style="margin: 0 0 10px 0; font-size: 14px; color: #666; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    Your Assessment Result
                </p>
                <p style="margin: 0; font-size: 18px; font-weight: 600; color: 
                    @if($emailData['burnoutCategory'] === 'BURNOUT' || $emailData['burnoutCategory'] === 'Burnout')
                        #dc2626
                    @elseif($emailData['burnoutCategory'] === 'Exhausted')
                        #f59e0b
                    @elseif($emailData['burnoutCategory'] === 'Disengaged')
                        #3b82f6
                    @else
                        #10b981
                    @endif
                ;">
                    {{ $emailData['burnoutCategory'] }}
                </p>
            </div>
            @endif
            
            <!-- Counselor's Message -->
            @if(!empty($emailData['additionalMessage']))
            <div style="margin: 25px 0;">
                <p style="font-size: 15px; color: #333; line-height: 1.7; white-space: pre-line;">
                    {{ $emailData['additionalMessage'] }}
                </p>
            </div>
            @endif
            
            <!-- Appointment Information -->
            @if(!empty($emailData['sendAppointment']) && !empty($emailData['appointmentDatetime']))
            <div style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); padding: 20px; border-radius: 8px; margin: 25px 0; border: 2px solid #667eea;">
                <p style="margin: 0 0 5px 0; font-size: 14px; color: #667eea; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                    Scheduled Counseling Session
                </p>
                <p style="margin: 0; font-size: 20px; font-weight: 600; color: #333;">
                    {{ \Carbon\Carbon::parse($emailData['appointmentDatetime'])->format('l, F d, Y') }}
                </p>
                <p style="margin: 5px 0 0 0; font-size: 18px; color: #667eea; font-weight: 600;">
                    {{ \Carbon\Carbon::parse($emailData['appointmentDatetime'])->format('g:i A') }}
                </p>
                <p style="margin: 15px 0 0 0; font-size: 14px; color: #666; line-height: 1.5;">
                    <strong>Location:</strong> Guidance Counseling Office<br>
                    <strong>Duration:</strong> Approximately 45-60 minutes<br>
                    <strong>Note:</strong> Please arrive 5 minutes early. If you need to reschedule, kindly inform us at least 24 hours in advance.
                </p>
            </div>
            @endif
            
            <!-- Key Recommendations (Optional - if available) -->
            @if(!empty($emailData['recommendations']) && is_array($emailData['recommendations']))
            <div style="margin: 25px 0;">
                <h3 style="color: #333; font-size: 16px; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb;">
                    Recommended Actions for Your Wellbeing
                </h3>
                <ul style="padding-left: 20px; margin: 0; color: #555; font-size: 14px; line-height: 1.7;">
                    @foreach(array_slice($emailData['recommendations'], 0, 3) as $recommendation)
                    <li style="margin-bottom: 10px;">{{ $recommendation }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <!-- Closing Message -->
            <p style="font-size: 15px; color: #555; margin-top: 20px;">
                Thank you for using Burnalytics. 
            </p>
            
            <!-- Signature -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
                <p style="margin: 0; font-size: 15px; color: #333; font-weight: 600;">
                    {{ $emailData['counselorName'] ?? 'Guidance Counseling Office' }}
                </p>
                <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">
                    Pamantasan ng Lungsod ng Pasig
                </p>
            </div>
            
        </div>
        
        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0; font-size: 13px; color: #666;">
                <strong>Burnalytics</strong> - Academic Burnout Prediction & Support System
            </p>
        </div>
        
    </div>
    
    <!-- Spacer -->
    <div style="height: 40px;"></div>
    
</body>
</html>