<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; padding: 20px; direction: rtl; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #1e293b; color: #ffffff; text-align: center; padding: 20px; }
        .content { padding: 30px; color: #333333; line-height: 1.6; }
        .footer { background-color: #f8fafc; text-align: center; padding: 15px; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #3b82f6; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2 style="margin: 0;">🚗 نظام SpotLy للمواقف التفاعلية</h2>
        </div>
        <div class="content">
            <h3 style="color: #0f172a;">{{ $mailDetails['title'] }}</h3>
            <p style="font-size: 16px;">مرحباً،</p>
            <p style="font-size: 16px;">{{ $mailDetails['body'] }}</p>
            
            <div style="text-align: center;">
                <a href="{{ url('/') }}" class="btn">الانتقال للوحة التحكم</a>
            </div>
        </div>
        <div class="footer">
            <p>هذه رسالة تلقائية من نظام SpotLy، الرجاء عدم الرد عليها.</p>
            <p>&copy; {{ date('Y') }} جميع الحقوق محفوظة لمشروع SpotLy.</p>
        </div>
    </div>
</body>
</html>