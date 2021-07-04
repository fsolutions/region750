<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMastersToToVentilationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('to_ventilation', function (Blueprint $table) {
            $table->foreignId('ventilation_master_user_id_4')->nullable()->after('ventilation_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
            $table->foreignId('ventilation_master_user_id_3')->nullable()->after('ventilation_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
            $table->foreignId('ventilation_master_user_id_2')->nullable()->after('ventilation_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('to_ventilation', function (Blueprint $table) {
            $table->dropForeign(['ventilation_master_user_id_2']);
            $table->dropForeign(['ventilation_master_user_id_3']);
            $table->dropForeign(['ventilation_master_user_id_4']);
            $table->dropColumn('ventilation_master_user_id_2');
            $table->dropColumn('ventilation_master_user_id_3');
            $table->dropColumn('ventilation_master_user_id_4');
        });
    }
}
