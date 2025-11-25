<?php

namespace Jiny\Mail\Http\Controllers\Admin\AuthUsers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\AuthUser;
use Jiny\Auth\Models\ShardTable;
use Jiny\Mail\Facades\UserMail;

/**
 * 관리자 - 사용자 개별 메일 발송 컨트롤러
 *
 * - GET : 메일 작성 폼 표시
 * - POST: UserMail 파사드를 통해 즉시 메일 발송
 */
class MailController extends Controller
{
    /**
     * 메일 작성 화면
     */
    public function create(Request $request, $id)
    {
        $shardId = $request->get('shard_id');
        $user = $this->findUser($id, $shardId);
        if (!$user) {
            abort(404, '사용자를 찾을 수 없습니다.');
        }

        return view('jiny-auth::admin.auth-users.mail', [
            'user' => $user,
            'fromUser' => Auth::user(),
            'shardId' => $shardId
        ]);
    }

    /**
     * 메일 발송 처리
     */
    public function send(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $shardId = $request->get('shard_id');
        $user = $this->findUser($id, $shardId);
        if (!$user) {
            return back()->with('error', '사용자를 찾을 수 없습니다.');
        }

        // 메일 설정 로드 및 검증
        $config = $this->loadAuthMailConfig();
        $invalidReason = $this->validateAuthMailConfig($config);
        if ($invalidReason) {
            return back()->with('error', '메일 설정 오류: '.$invalidReason);
        }
        $this->applyAuthMailConfig($config);

        $subject = $request->input('subject');
        $message = $request->input('message');
        $adminUser = Auth::user();
        $html = $this->buildMailHtml($user, $message, $adminUser);

        try {
            $result = UserMail::sendByHtml(
                $user->email,
                $subject,
                $html,
                $user->name ?? $user->email,
                $config
            );

            if (!($result['success'] ?? false)) {
                throw new \RuntimeException($result['message'] ?? '메일 발송에 실패했습니다.');
            }

            return back()->with('success', '메일이 성공적으로 발송되었습니다.');
        } catch (\Throwable $e) {
            \Log::error('Admin user mail send failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', '메일 발송에 실패했습니다: '.$e->getMessage());
        }
    }

    /**
     * 사용자 조회 (샤딩 지원)
     */
    protected function findUser($id, $shardId = null)
    {
        if ($shardId) {
            $shardTable = ShardTable::where('table_name', 'users')->first();
            if (!$shardTable) { return null; }

            $tableName = $shardTable->getShardTableName($shardId);
            if (!DB::getSchemaBuilder()->hasTable($tableName)) { return null; }

            $userData = DB::table($tableName)->where('id', $id)->first();
            if (!$userData) { return null; }

            $user = AuthUser::hydrate([(array)$userData])->first();
            $user->setTable($tableName);
            return $user;
        }

        return AuthUser::find($id);
    }

    protected function loadAuthMailConfig(): array
    {
        try {
            return UserMail::loadConfig();
        } catch (\Throwable $e) {
            \Log::error('UserMail 설정 로드 실패', ['error' => $e->getMessage()]);
            return [];
        }
    }

    protected function validateAuthMailConfig(array $config): ?string
    {
        if (empty($config['mailer'])) {
            return 'mailer 값이 비어 있습니다.';
        }
        if (empty($config['from_address'])) {
            return '발신자 이메일이 설정되지 않았습니다.';
        }
        if (($config['mailer'] ?? 'smtp') === 'smtp') {
            if (empty($config['host']) || empty($config['port'])) {
                return 'SMTP 호스트/포트가 올바르지 않습니다.';
            }
        }
        return null;
    }

    protected function applyAuthMailConfig(array $config): void
    {
        UserMail::applyConfig($config);
    }

    protected function buildMailHtml($user, string $message, $adminUser = null): string
    {
        $appName = config('app.name', 'JinyPHP');
        $adminName = $adminUser->name ?? '관리자';
        $sentAt = now()->format('Y-m-d H:i');
        $body = nl2br(e($message));

        return <<<HTML
<div style="font-family: Arial, sans-serif; background:#f5f6f8; padding:24px;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;box-shadow:0 12px 30px rgba(15,30,140,.12);overflow:hidden;">
        <div style="background:#4c6fff;color:#fff;padding:28px;text-align:center;">
            <h2 style="margin:0;font-size:22px;">{$appName} 관리자 공지</h2>
            <p style="margin:8px 0 0;opacity:.85;">{$adminName} 님이 보낸 메일입니다.</p>
        </div>
        <div style="padding:32px;">
            <p style="font-size:16px;">안녕하세요 <strong>{$user->name}</strong>님,</p>
            <p style="margin-top:16px;line-height:1.7;color:#333;">{$body}</p>
            <div style="margin-top:24px;padding:18px;border-left:4px solid #4c6fff;background:#f4f6ff;border-radius:6px;">
                <p style="margin:0;font-size:14px;color:#4c5673;">
                    발신자: <strong>{$adminName}</strong><br>
                    발송 시각: {$sentAt}
                </p>
            </div>
        </div>
        <div style="background:#f8f9fb;padding:18px;text-align:center;font-size:13px;color:#6b7280;">
            이 메일은 {$appName} 관리자 시스템에서 발송되었습니다.
        </div>
    </div>
</div>
HTML;
    }
}

