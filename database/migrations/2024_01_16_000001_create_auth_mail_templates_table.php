<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('템플릿 이름');
            $table->string('type', 50)->comment('템플릿 타입 (welcome, notice, reminder 등)');
            $table->string('subject')->comment('메일 제목');
            $table->text('message')->comment('메일 내용');
            $table->text('description')->nullable()->comment('템플릿 설명');
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->unsignedBigInteger('admin_user_id')->nullable()->comment('등록한 관리자 ID');
            $table->string('admin_user_name')->nullable()->comment('등록한 관리자 이름');
            $table->timestamps();

            // 인덱스
            $table->index(['type', 'is_active']);
            $table->index('admin_user_id');
        });

        // 기본 템플릿 데이터 삽입
        $this->insertDefaultTemplates();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_mail_templates');
    }

    /**
     * 기본 템플릿 데이터 삽입
     */
    private function insertDefaultTemplates()
    {
        $templates = [
            [
                'name' => '환영 메시지 템플릿',
                'type' => 'welcome',
                'subject' => '{{SITE_NAME}}에 가입을 환영합니다!',
                'message' => "안녕하세요 {{USER_NAME}}님,\n\n{{SITE_NAME}}에 가입해 주셔서 감사합니다.\n\n저희 서비스를 통해 많은 도움을 받으시길 바라며, 궁금한 사항이 있으시면 언제든지 문의해 주세요.\n\n감사합니다.\n\n{{SITE_NAME}} 팀",
                'description' => '신규 회원가입 시 발송하는 환영 메시지 템플릿입니다.',
                'is_active' => true,
                'admin_user_id' => null,
                'admin_user_name' => 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '공지사항 템플릿',
                'type' => 'notice',
                'subject' => '[공지] 중요한 안내사항입니다',
                'message' => "안녕하세요 {{USER_NAME}}님,\n\n중요한 공지사항을 안내드립니다.\n\n[공지 내용을 여기에 작성해주세요]\n\n자세한 내용은 사이트에서 확인하실 수 있습니다.\n\n감사합니다.\n\n{{SITE_NAME}} 팀",
                'description' => '일반적인 공지사항을 발송할 때 사용하는 템플릿입니다.',
                'is_active' => true,
                'admin_user_id' => null,
                'admin_user_name' => 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '알림 메시지 템플릿',
                'type' => 'reminder',
                'subject' => '중요한 알림이 있습니다',
                'message' => "안녕하세요 {{USER_NAME}}님,\n\n알림 내용을 확인해 주세요.\n\n[알림 내용을 여기에 작성해주세요]\n\n감사합니다.\n\n{{SITE_NAME}} 팀",
                'description' => '사용자에게 알림을 발송할 때 사용하는 템플릿입니다.',
                'is_active' => true,
                'admin_user_id' => null,
                'admin_user_name' => 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            DB::table('auth_mail_templates')->insert($template);
        }
    }
};