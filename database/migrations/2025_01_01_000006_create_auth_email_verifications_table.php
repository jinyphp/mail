<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 이메일 인증 관리 테이블
     */
    public function up(): void
    {
        Schema::create('auth_email_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // 사용자 ID
            $table->string('email'); // 인증할 이메일
            $table->string('token')->unique(); // 인증 토큰
            $table->string('verification_code', 6)->nullable(); // 6자리 인증 코드
            $table->enum('type', ['register', 'change', 'reset'])->default('register'); // 인증 유형
            $table->boolean('verified')->default(false); // 인증 완료 여부
            $table->timestamp('expires_at'); // 만료 시간
            $table->timestamp('verified_at')->nullable(); // 인증 완료 시간
            $table->integer('attempts')->default(0); // 시도 횟수
            $table->string('ip_address', 45)->nullable(); // IP 주소
            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('email');
            $table->index('token');
            $table->index('verification_code');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_email_verifications');
    }
};