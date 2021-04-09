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
            $table->text('order_description')->nullable()->comment('Описание обращения');
            $table->foreignId('order_reference_service_id')->default(8)->comment('Ссылка на услугу')->constrained('reference_properties');
            $table->foreignId('order_contract_id')->comment('Ссылка на договор')->constrained('contracts');
            $table->foreignId('order_prescription_id')->nullable()->comment('Ссылка на предписание, если есть')->constrained('prescriptions');
            $table->foreignId('order_master_user_id')->nullable()->comment('Мастер, назначенный на обработку обращения')->constrained('users');
            $table->timestamp('order_start_datetime', 0)->nullable()->comment('Дата исполнения обращения');
            $table->text('order_comment_for_user')->nullable()->comment('Комментарий для клиента');
            $table->text('order_comment')->nullable()->comment('Комментарий для коллег');
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
