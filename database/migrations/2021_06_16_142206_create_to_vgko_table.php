<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateToVgkoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('to_vgko', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('vgko_region_id')->nullable()->comment('Регион, к которому относится ТО')->constrained('regions');
            $table->foreignId('vgko_city_id')->nullable()->comment('Город, к которому относится ТО')->constrained('cities');
            $table->foreignId('vgko_street_id')->nullable()->comment('Улица, к которому относится ТО')->constrained('streets');
            $table->foreignId('vgko_house_id')->nullable()->comment('Дом, к которому относится ТО')->constrained('houses');
            $table->foreignId('vgko_master_user_id')->nullable()->comment('Мастер, который проводил ТО')->constrained('users');
            $table->text('vgko_comment')->nullable()->comment('Комментарий');
            $table->enum('vgko_status', ['Запланировано', 'Проведено', 'Отменено', 'Перенесено'])->nullable()->comment('Статус ТО');
            $table->timestamp('vgko_date_of_work', 0)->nullable()->comment('Дата проведения работ');
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
        Schema::dropIfExists('to_vgko');
    }
}
