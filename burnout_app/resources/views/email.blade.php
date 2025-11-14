<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Burnalytics - Burnout Assessment Follow-up</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px;">
        <h1 style="color: #4f46e5; margin-top: 0;">Burnalytics - Predicting Academic Burnout</h1>
        
        <p>Hi {{ $emailData['studentName'] ?? 'Student' }},</p>
        
        <p>{{ $emailData['additionalMessage'] ?? '' }}</p>
        
        @if(!empty($emailData['sendAppointment']) && !empty($emailData['appointmentDatetime']))
        <p style="margin-top: 20px;">
            <strong>Scheduled Counseling Session:</strong> {{ \Carbon\Carbon::parse($emailData['appointmentDatetime'])->format('F d, Y \a\t g:i A') }}
        </p>
        @endif
        
        <p style="margin-top: 30px; color: #666;">
            <strong>Burnalytics</strong>
        </p>
    </div>
</body>
</html>
