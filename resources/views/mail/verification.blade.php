<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>이메일 인증</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 40px 30px; }
        .content h2 { color: #667eea; margin-top: 0; font-size: 20px; }
        .button { display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 20px 0; transition: transform 0.2s; }
        .button:hover { transform: translateY(-2px); }
        .code-box { background: #f8f9fa; border: 2px dashed #667eea; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
        .code { font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #667eea; font-family: 'Courier New', monospace; }
        .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        ul { padding-left: 20px; }
        li { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✉️ 이메일 인증</h1>
        </div>
        
        <div class="content">
            <h2>안녕하세요, {{ $user->name }}님!</h2>
            
            <p>{{ config('app.name') }}에 가입해 주셔서 감사합니다.</p>
            <p>회원가입을 완료하기 위해 이메일 인증이 필요합니다.</p>

            @if($verificationCode)
            <div class="code-box">
                <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">인증 코드</p>
                <div class="code">{{ $verificationCode }}</div>
            </div>
            @endif

            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">
                    이메일 인증하기
                </a>
            </div>

            <div class="info-box">
                <strong>📌 인증 안내</strong>
                <ul style="margin: 10px 0 0 0;">
                    <li>위 버튼을 클릭하여 이메일 인증을 완료해주세요</li>
                    <li>인증 링크는 <strong>24시간</strong> 동안 유효합니다</li>
                    <li>링크가 작동하지 않으면 아래 URL을 복사하여 브라우저에 붙여넣으세요</li>
                </ul>
            </div>

            <p style="word-break: break-all; background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px; color: #666;">
                {{ $verificationUrl }}
            </p>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                본인이 요청하지 않은 경우 이 이메일을 무시하셔도 됩니다.
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>이 이메일은 자동으로 발송되었습니다. 회신하지 마세요.</p>
        </div>
    </div>
</body>
</html>
