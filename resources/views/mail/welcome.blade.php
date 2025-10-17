<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 축하</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 600; }
        .content { padding: 40px 30px; }
        .content h2 { color: #667eea; margin-top: 0; font-size: 22px; }
        .button { display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: 600; margin: 20px 0; }
        .feature-box { background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .feature { display: flex; align-items: start; margin-bottom: 15px; }
        .feature-icon { background: #667eea; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 환영합니다!</h1>
        </div>
        
        <div class="content">
            <h2>{{ $user->name }}님, 회원가입을 축하드립니다!</h2>
            
            <p>{{ config('app.name') }}의 회원이 되신 것을 진심으로 환영합니다.</p>
            <p>이제 다양한 서비스를 이용하실 수 있습니다.</p>

            <div class="feature-box">
                <h3 style="margin-top: 0; color: #333; font-size: 16px;">주요 기능</h3>
                
                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <div>
                        <strong>개인 대시보드</strong><br>
                        <span style="color: #666; font-size: 14px;">나만의 맞춤형 대시보드에서 모든 활동을 관리하세요</span>
                    </div>
                </div>

                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <div>
                        <strong>프로필 관리</strong><br>
                        <span style="color: #666; font-size: 14px;">프로필 정보를 자유롭게 수정하고 관리하세요</span>
                    </div>
                </div>

                <div class="feature">
                    <div class="feature-icon">✓</div>
                    <div>
                        <strong>보안 설정</strong><br>
                        <span style="color: #666; font-size: 14px;">2단계 인증, 비밀번호 관리 등 강화된 보안 기능</span>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}/login" class="button">
                    지금 시작하기
                </a>
            </div>

            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <strong style="color: #856404;">📧 계정 정보</strong>
                <p style="margin: 10px 0 0 0; color: #856404;">
                    이메일: <strong>{{ $user->email }}</strong><br>
                    가입일시: {{ $user->created_at->format('Y-m-d H:i') }}
                </p>
            </div>

            <p style="margin-top: 30px; color: #666; font-size: 14px;">
                문의사항이 있으시면 언제든지 고객센터로 연락 주시기 바랍니다.
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>이 이메일은 자동으로 발송되었습니다. 회신하지 마세요.</p>
        </div>
    </div>
</body>
</html>
