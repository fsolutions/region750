<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_contract_id')->comment('Ссылка на договор')->constrained('contracts');
            $table->foreignId('prescription_master_user_id')->nullable()->comment('Мастер, составивший предписание')->constrained('users');
            $table->timestamp('prescription_start_datetime', 0)->nullable()->comment('Дата исполнения предписания');
            $table->text('prescription_comment')->nullable()->comment('Комментарий');
            $table->enum('prescription_status', ['Запланировано', 'Проведено', 'Отменено', 'Перенесено'])->nullable()->comment('Статус предписания');
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
        Schema::dropIfExists('prescriptions');
    }
}
