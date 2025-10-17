<?php

namespace Jiny\Mail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * AuthMailLog Model
 *
 * 인증 관련 메일 발송 로그를 관리합니다.
 */
class AuthMailLog extends Model
{
    use HasFactory;

    protected $table = 'auth_mail_logs';

    protected $fillable = [
        'type',
        'status',
        'recipient_email',
        'recipient_name',
        'sender_email',
        'sender_name',
        'subject',
        'content',
        'read_at',
        'read_count',
        'user_id',
        'user_agent',
        'ip_address',
        'error_message',
        'attempts'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'read_count' => 'integer',
        'attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 메일 타입 상수
     */
    const TYPE_VERIFICATION = 'verification';      // 이메일 인증
    const TYPE_PASSWORD_RESET = 'password_reset';  // 비밀번호 재설정
    const TYPE_WELCOME = 'welcome';                // 가입 환영
    const TYPE_NOTIFICATION = 'notification';      // 알림
    const TYPE_TEST = 'test';                      // 테스트

    /**
     * 메일 상태 상수
     */
    const STATUS_PENDING = 'pending';              // 발송 대기
    const STATUS_SENT = 'sent';                    // 발송 완료
    const STATUS_FAILED = 'failed';                // 발송 실패
    const STATUS_READ = 'read';                    // 읽음 확인
    const STATUS_BOUNCED = 'bounced';              // 반송

    /**
     * 사용자와의 관계
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * 메일 읽음 처리
     */
    public function markAsRead()
    {
        $this->update([
            'status' => self::STATUS_READ,
            'read_at' => now(),
            'read_count' => $this->read_count + 1
        ]);
    }

    /**
     * 스코프: 타입별 필터
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 스코프: 상태별 필터
     */
    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 스코프: 수신자별 필터
     */
    public function scopeForRecipient($query, $email)
    {
        return $query->where('recipient_email', $email);
    }

    /**
     * 스코프: 날짜 범위 필터
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * 검색 스코프
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('recipient_email', 'like', '%' . $search . '%')
              ->orWhere('subject', 'like', '%' . $search . '%')
              ->orWhere('recipient_name', 'like', '%' . $search . '%');
        });
    }

    /**
     * 타입 한글명 반환
     */
    public function getTypeNameAttribute()
    {
        $types = [
            self::TYPE_VERIFICATION => '이메일 인증',
            self::TYPE_PASSWORD_RESET => '비밀번호 재설정',
            self::TYPE_WELCOME => '가입 환영',
            self::TYPE_NOTIFICATION => '알림',
            self::TYPE_TEST => '테스트',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * 상태 한글명 반환
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => '발송 대기',
            self::STATUS_SENT => '발송 완료',
            self::STATUS_FAILED => '발송 실패',
            self::STATUS_READ => '읽음 확인',
            self::STATUS_BOUNCED => '반송',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * 상태 색상 반환
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_SENT => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_READ => 'info',
            self::STATUS_BOUNCED => 'secondary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}