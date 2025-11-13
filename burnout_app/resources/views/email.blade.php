<x-mail::message>
# Burnalytics - Predicting Academic Burnout

Hi {{ $emailData['studentName'] ?? 'Student' }},

Thank you for completing the burnout assessment through Burnalytics. We have reviewed your assessment results and would like to follow up with you{{ ($emailData['sendAppointment'] ?? false) ? ' regarding your counseling appointment' : '' }}.

---

**Assessment Result:** {{ $emailData['category'] ?? 'Unavailable' }}

---

@if(!empty($emailData['sendMessage']) && !empty($emailData['additionalMessage']))
{{ $emailData['additionalMessage'] }}

@endif
@if(!empty($emailData['sendAppointment']) && !empty($emailData['appointmentDatetime']))
**Scheduled Counseling Session:** {{ \Carbon\Carbon::parse($emailData['appointmentDatetime'])->format('F d, Y \a\t g:i A') }}

@endif
---

**Burnalytics**
</x-mail::message>
