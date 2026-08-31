{{-- resources/views/emails/admin/ad-hoc.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>Dear {{ $recipientName }},</p>

    <div style="white-space: pre-wrap;">{{ $messageBody }}</div>

    <hr>
    <p style="font-size: 0.85em; color: #666;">
        This message relates to application: <strong>{{ $application->application_number }}</strong>
    </p>

    <p style="font-size: 0.85em; color: #666;">
        If you have any questions, please don't hesitate to contact us.
    </p>
</body>
</html>