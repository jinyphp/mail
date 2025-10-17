<?php

namespace Jiny\Mail\Http\Controllers\Admin\Mail\MailLogs;

use App\Http\Controllers\Controller;
use Jiny\Mail\Models\AuthMailLog;

/**
 * MailLogs ContentController
 *
 * 메일 로그의 내용을 조회하는 기능을 제공합니다.
 */
class ContentController extends Controller
{
    /**
     * 메일 내용 조회
     */
    public function __invoke($id)
    {
        $mailLog = AuthMailLog::findOrFail($id);

        // 조회 시 읽음 카운트 증가 (선택적)
        // $mailLog->increment('read_count');

        // HTML 컨텐츠를 그대로 반환
        if ($mailLog->content) {
            return response($mailLog->content)
                ->header('Content-Type', 'text/html; charset=utf-8');
        }

        // 컨텐츠가 없는 경우 기본 메시지
        $html = '
        <div class="alert alert-info">
            <h6>메일 정보</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>발송일시:</strong></td>
                    <td>' . $mailLog->created_at->format('Y-m-d H:i:s') . '</td>
                </tr>
                <tr>
                    <td><strong>타입:</strong></td>
                    <td>' . $mailLog->type . '</td>
                </tr>
                <tr>
                    <td><strong>수신자:</strong></td>
                    <td>' . $mailLog->recipient_email . '</td>
                </tr>
                <tr>
                    <td><strong>제목:</strong></td>
                    <td>' . $mailLog->subject . '</td>
                </tr>
                <tr>
                    <td><strong>상태:</strong></td>
                    <td>' . $mailLog->status . '</td>
                </tr>
            </table>
            <p class="mb-0 text-muted">메일 내용이 저장되지 않았습니다.</p>
        </div>';

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}