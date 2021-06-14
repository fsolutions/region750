<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoAccessTimesToContractsToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts_to', function (Blueprint $table) {
            $table->tinyInteger('to_no_access_times')->nullable()->default(0)->after('to_status')->comment('Сколько раз не предоставлен доступ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts_to', function (Blueprint $table) {
            $table->dropColumn('to_no_access_times');
        });
    }
}
