<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->nullable()->comment('Регион')->constrained('regions');
            $table->foreignId('city_id')->nullable()->comment('Город')->constrained('cities');
            $table->foreignId('street_id')->nullable()->comment('Улица')->constrained('streets');
            $table->foreignId('house_id')->nullable()->comment('Дом')->constrained('houses');
            $table->string('name', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('flats');
    }
}
