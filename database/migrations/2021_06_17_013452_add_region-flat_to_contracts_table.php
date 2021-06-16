<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegionFlatToContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('contract_region_id')->nullable()->after('contract_address')->comment('Регион, к которому относится Договор')->constrained('regions');
            $table->foreignId('contract_city_id')->nullable()->after('contract_address')->comment('Город, к которому относится Договор')->constrained('cities');
            $table->foreignId('contract_street_id')->nullable()->after('contract_address')->comment('Улица, к которому относится Договор')->constrained('streets');
            $table->foreignId('contract_house_id')->nullable()->after('contract_address')->comment('Дом, к которому относится Договор')->constrained('houses');
            $table->foreignId('contract_flat_id')->nullable()->after('contract_address')->comment('Квартира, к которой относится Договор')->constrained('flats');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['contract_region_id']);
            $table->dropForeign(['contract_city_id']);
            $table->dropForeign(['contract_street_id']);
            $table->dropForeign(['contract_house_id']);
            $table->dropForeign(['contract_flat_id']);
            $table->dropColumn('contract_region_id');
            $table->dropColumn('contract_city_id');
            $table->dropColumn('contract_street_id');
            $table->dropColumn('contract_house_id');
            $table->dropColumn('contract_flat_id');
        });
    }
}
