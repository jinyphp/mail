<?php
/**
 * 관리자 회원
 */
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
        Schema::create('user_mail', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->bigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            $table->string('label')->nullable();

            $table->string('subject')->nullable();
            $table->text('message')->nullable();

            $table->string('instant')->nullable(); // 즉시발송 여부

            $table->unsignedBigInteger('sended')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_mail');
    }
};
