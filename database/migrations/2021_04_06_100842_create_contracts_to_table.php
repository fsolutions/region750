<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts_to', function (Blueprint $table) {
            $table->id();
            $table->foreignId('to_contract_id')->comment('Ссылка на договор')->constrained('contracts');
            $table->foreignId('to_master_user_id')->nullable()->comment('Мастер, назначенный на ТО')->constrained('users');
            $table->timestamp('to_start_datetime', 0)->nullable()->comment('Дата запланированного ТО');
            $table->text('to_comment')->nullable()->comment('Комментарий');
            $table->enum('to_status', ['Запланировано', 'Проведено', 'Отменено', 'Перенесено'])->nullable()->comment('Статус ТО');
            $table->timestamp('to_sms_sended', 0)->nullable()->comment('Дата отправки последнего информационного сообщения по SMS');
            $table->timestamp('to_email_sended', 0)->nullable()->comment('Дата отправки последнего информационного сообщения по Email');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts_to');
    }
}
