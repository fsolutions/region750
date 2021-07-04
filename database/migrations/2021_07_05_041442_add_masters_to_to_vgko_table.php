<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMastersToToVgkoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('to_vgko', function (Blueprint $table) {
            $table->foreignId('vgko_master_user_id_4')->nullable()->after('vgko_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
            $table->foreignId('vgko_master_user_id_3')->nullable()->after('vgko_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
            $table->foreignId('vgko_master_user_id_2')->nullable()->after('vgko_master_user_id')->comment('Мастер, назначенный на ТО')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('to_vgko', function (Blueprint $table) {
            $table->dropForeign(['vgko_master_user_id_2']);
            $table->dropForeign(['vgko_master_user_id_3']);
            $table->dropForeign(['vgko_master_user_id_4']);
            $table->dropColumn('vgko_master_user_id_2');
            $table->dropColumn('vgko_master_user_id_3');
            $table->dropColumn('vgko_master_user_id_4');
        });
    }
}
