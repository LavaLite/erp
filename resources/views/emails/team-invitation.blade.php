<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }
        .content {
            background-color: white;
            padding: 25px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .invitation-details {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .invitation-details p {
            margin: 8px 0;
        }
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            margin: 10px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            font-size: 16px;
        }
        .button-accept {
            background-color: #10b981;
            color: white;
        }
        .button-accept:hover {
            background-color: #059669;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-top: 30px;
        }
        .expires {
            color: #f59e0b;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👥 Team Invitation</h1>
        </div>

        <div class="content">
            <p>Hello,</p>
            
            <p><strong>{{ $inviterName }}</strong> has invited you to join the <strong>{{ $teamName }}</strong> team in <strong>{{ $organizationName }}</strong>.</p>

            <div class="invitation-details">
                <p><strong>Organization:</strong> {{ $organizationName }}</p>
                <p><strong>Team:</strong> {{ $teamName }}</p>
                <p><strong>Role:</strong> {{ ucfirst($role) }}</p>
                <p><strong>Invited by:</strong> {{ $inviterName }}</p>
            </div>

            <p>Click the button below to review and accept or decline this invitation:</p>

            <div class="actions">
                <a href="{{ $confirmUrl }}" class="button button-accept">View Invitation</a>
            </div>

            <p class="expires">⏰ This invitation expires on {{ $expiresAt }}</p>

            <p style="color: #6b7280; font-size: 14px;">
                If you weren't expecting this invitation, you can safely ignore this email.
            </p>
        </div>

        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
