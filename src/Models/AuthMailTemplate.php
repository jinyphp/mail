<?php

namespace Jiny\Mail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 메일 템플릿 모델
 *
 * 관리자가 등록한 메일 템플릿을 관리합니다.
 */
class AuthMailTemplate extends Model
{
    use HasFactory;

    protected $table = 'auth_mail_templates';

    protected $fillable = [
        'name',
        'type',
        'subject',
        'message',
        'description',
        'is_active',
        'admin_user_id',
        'admin_user_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 활성화된 템플릿만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 타입별 템플릿 조회
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 템플릿 타입 목록
     */
    public static function getTypeOptions()
    {
        return [
            'welcome' => '환영',
            'notice' => '공지',
            'reminder' => '알림',
            'verification' => '인증',
            'password_reset' => '비밀번호 재설정',
            'account_update' => '계정 업데이트',
            'system' => '시스템',
            'custom' => '사용자 정의',
        ];
    }

    /**
     * 템플릿 타입명 반환
     */
    public function getTypeNameAttribute()
    {
        $types = self::getTypeOptions();
        return $types[$this->type] ?? $this->type;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\Jiny\Auth\Models\AuthMailTemplateFactory::new();
    }
}