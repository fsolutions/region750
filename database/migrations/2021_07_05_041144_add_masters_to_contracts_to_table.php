<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMastersToContractsToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts_to', function (Blueprint $table) {
            $table->foreignId('to_master_user_id_4')->nullable()->after('to_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
            $table->foreignId('to_master_user_id_3')->nullable()->after('to_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
            $table->foreignId('to_master_user_id_2')->nullable()->after('to_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
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
            $table->dropForeign(['to_master_user_id_2']);
            $table->dropForeign(['to_master_user_id_3']);
            $table->dropForeign(['to_master_user_id_4']);
            $table->dropColumn('to_master_user_id_2');
            $table->dropColumn('to_master_user_id_3');
            $table->dropColumn('to_master_user_id_4');
        });
    }
}
