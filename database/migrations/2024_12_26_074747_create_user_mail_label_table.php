<?php
/**
 * 사용자 메일 라벨
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
        Schema::create('user_mail_label', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->nullable();

            $table->string('name')->nullable();
            $table->string('color')->nullable();

            $table->unsignedBigInteger('cnt')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_mail_label');
    }
};
