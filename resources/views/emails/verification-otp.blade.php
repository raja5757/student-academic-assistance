<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Verification</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #e9ecef; padding-bottom: 15px; }
        .header h1 { margin: 0; color: #0d6efd; }
        .otp { font-size: 36px; font-weight: bold; letter-spacing: 5px; color: #0d6efd; text-align: center; margin: 30px 0; padding: 15px; background: #f8f9fa; border-radius: 4px; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #6c757d; border-top: 1px solid #e9ecef; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SAA-EIS</h1>
        </div>
        <p>Hello {{ $user->name }},</p>
        <p>Please use the following 6-digit verification code to complete your request on the Student Academic Assistance & Early Intervention System:</p>
        <div class="otp">{{ $otp }}</div>
        <p>This code will expire in 10 minutes. For your security, do not share this code with anyone.</p>
        <div class="footer">
            <p>If you did not request this code, you can safely ignore this email.</p>
            <p>&copy; {{ date('Y') }} SAA-EIS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
