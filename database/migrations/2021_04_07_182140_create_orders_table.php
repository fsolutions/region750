<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_contract_id')->comment('Ссылка на договор')->constrained('contracts');
            $table->foreignId('order_prescription_id')->nullable()->comment('Ссылка на предписание, если есть')->constrained('prescriptions');
            $table->foreignId('order_master_user_id')->nullable()->comment('Мастер, назначенный на обработку обращения')->constrained('users');
            $table->timestamp('order_start_datetime', 0)->nullable()->comment('Дата исполнения обращения');
            $table->text('order_comment')->nullable()->comment('Комментарий');
            $table->enum('order_status', ['В обработке', 'Запланировано исполнение', 'Исполнено', 'Отменено'])->nullable()->comment('Статус обращения');
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
        Schema::dropIfExists('orders');
    }
}
