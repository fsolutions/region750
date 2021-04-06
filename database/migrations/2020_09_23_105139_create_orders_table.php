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
            $table->bigIncrements('id');
            $table->foreignId('creator_user_id')->constrained('users');
            $table->string('lead_fio')->nullable()->comment('Имя из заявки');
            $table->string('lead_email')->nullable()->comment('Email из заявки');
            $table->string('lead_phone')->nullable()->comment('Телефон из заявки');
            $table->foreignId('reference_order_type_id')->nullable()->constrained('reference_properties');
            $table->foreignId('sales_manager_user_id')->nullable()->constrained('users');
            $table->foreignId('reference_sources_id')->nullable()->constrained('reference_properties');
            $table->foreignId('reference_status_id')->nullable()->constrained('reference_properties');
            $table->string('temp_name')->nullable()->comment('Временное название для коротких обращений или условнго обозначения');
            $table->timestamp('receive_datetime', 0)->nullable()->comment('Дата получения заявки');
            $table->timestamp('processing_end_datetime', 0)->nullable()->comment('Дата отработки заявки (КП направлено/консультация оказана)');
            $table->text('comment')->nullable()->comment('Комментарий к заявке');
            $table->foreignId('reference_close_reason_id')->nullable()->constrained('reference_properties')->comment('Причина закрытия обращение из справочника');
            $table->text('close_comment')->nullable()->comment('Причина закрытия обращение текстом');
            $table->boolean('short_order')->nullable()->default(0)->comment('Обращение с ограничениями');
            $table->softDeletes('deleted_at', 0);
            $table->timestamps();
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
