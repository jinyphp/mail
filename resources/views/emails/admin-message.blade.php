<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - 관리자 메시지</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .greeting {
            font-size: 18px;
            color: #495057;
            margin-bottom: 20px;
        }
        .message-content {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .admin-info {
            background-color: #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                🔐 {{ config('app.name') }}
            </div>
            <div style="color: #6c757d; font-size: 14px;">
                Authentication System
            </div>
        </div>

        <div class="greeting">
            안녕하세요 <strong>{{ $user->name }}</strong>님,
        </div>

        <p>관리자로부터 메시지를 받으셨습니다.</p>

        <div class="message-content">
            {{ $message }}
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.url') }}" class="btn">
                사이트 방문하기
            </a>
        </div>

        <div class="admin-info">
            <strong>📋 메시지 정보</strong><br>
            <div style="margin-top: 10px;">
                <strong>발송자:</strong> {{ $adminUser->name ?? '시스템 관리자' }}<br>
                <strong>발송 일시:</strong> {{ now()->format('Y년 m월 d일 H:i') }}<br>
                <strong>수신자:</strong> {{ $user->email }}
            </div>
        </div>

        <div class="footer">
            <p>
                이 메일은 {{ config('app.name') }} 관리자가 발송한 메시지입니다.<br>
                궁금한 사항이 있으시면 고객센터로 문의해 주세요.
            </p>
            <p style="margin-top: 15px;">
                <strong>{{ config('app.name') }}</strong><br>
                <a href="{{ config('app.url') }}" style="color: #007bff;">{{ config('app.url') }}</a>
            </p>
            <p style="font-size: 12px; color: #adb5bd; margin-top: 20px;">
                이 메일을 받고 싶지 않으시면 관리자에게 문의해 주세요.
            </p>
        </div>
    </div>
</body>
</html>