<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auth_mail_logs', function (Blueprint $table) {
            $table->id();

            // 메일 기본 정보
            $table->string('type', 50)->default('notification'); // verification, password_reset, welcome, notification, test
            $table->string('status', 50)->default('pending'); // pending, sent, failed, read, bounced

            // 수신자 정보
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();

            // 발신자 정보
            $table->string('sender_email');
            $table->string('sender_name')->nullable();

            // 메일 내용
            $table->string('subject');
            $table->longText('content')->nullable();

            // 읽기 추적
            $table->timestamp('read_at')->nullable();
            $table->unsignedInteger('read_count')->default(0);

            // 사용자 정보
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address', 45)->nullable();

            // 에러 및 재시도 정보
            $table->text('error_message')->nullable();
            $table->unsignedTinyInteger('attempts')->default(1);

            $table->timestamps();

            // 인덱스
            $table->index(['type', 'status']);
            $table->index(['recipient_email']);
            $table->index(['user_id']);
            $table->index(['created_at']);
            $table->index(['status', 'created_at']);

            // 외래키 (선택적 - users 테이블이 있는 경우)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_mail_logs');
    }
};