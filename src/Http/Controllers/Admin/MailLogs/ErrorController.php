<?php

namespace Jiny\Mail\Http\Controllers\Admin\MailLogs;

use App\Http\Controllers\Controller;
use Jiny\Mail\Models\AuthMailLog;

/**
 * MailLogs ErrorController
 *
 * 메일 로그의 오류 정보를 조회하는 기능을 제공합니다.
 */
class ErrorController extends Controller
{
    /**
     * 메일 오류 정보 조회
     */
    public function __invoke($id)
    {
        $mailLog = AuthMailLog::findOrFail($id);

        return response()->json([
            'success' => true,
            'error' => $mailLog->error_message ?? '오류 메시지가 없습니다.',
            'created_at' => $mailLog->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $mailLog->updated_at->format('Y-m-d H:i:s'),
            'attempts' => $mailLog->attempts,
            'status' => $mailLog->status,
            'type' => $mailLog->type,
            'recipient_email' => $mailLog->recipient_email,
            'subject' => $mailLog->subject,
            'user_agent' => $mailLog->user_agent,
            'ip_address' => $mailLog->ip_address,
        ]);
    }
}


