<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateToVentilationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('to_ventilation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('ventilation_region_id')->nullable()->comment('Регион, к которому относится ТО')->constrained('regions');
            $table->foreignId('ventilation_city_id')->nullable()->comment('Город, к которому относится ТО')->constrained('cities');
            $table->foreignId('ventilation_street_id')->nullable()->comment('Улица, к которому относится ТО')->constrained('streets');
            $table->foreignId('ventilation_house_id')->nullable()->comment('Дом, к которому относится ТО')->constrained('houses');
            $table->foreignId('ventilation_master_user_id')->nullable()->comment('Мастер, который проводил ТО')->constrained('users');
            $table->text('ventilation_comment')->nullable()->comment('Комментарий');
            $table->enum('ventilation_status', ['Запланировано', 'Проведено', 'Отменено', 'Перенесено'])->nullable()->comment('Статус ТО');
            $table->timestamp('ventilation_date_of_work', 0)->nullable()->comment('Дата проведения работ');
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
        Schema::dropIfExists('to_ventilation');
    }
}
