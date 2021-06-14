<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equip_user_id')->nullable()->comment('Клиент-владелец оборудования')->constrained('users');
            $table->foreignId('equip_contract_id')->nullable()->comment('Ссылка на договор')->constrained('contracts');
            $table->foreignId('equip_type_reference_id')->nullable()->comment('Ссылка на свойство Тип прибора')->constrained('reference_properties');
            $table->string('equip_mark')->nullable()->comment('Марка оборудования');
            $table->timestamp('equip_date_of_release', 0)->nullable()->comment('Дата выпуска');
            $table->string('equip_passport')->nullable()->comment('Номер паспорта оборудования');
            $table->text('equip_comment')->nullable()->comment('Комментарий');
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
        Schema::dropIfExists('equipment');
    }
}
