<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewStatusesToContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contracts', function (Blueprint $table) {
            DB::statement("ALTER TABLE contracts MODIFY status ENUM('В обработке','Есть бумажный договор','Нет бумажного договора','Договор расторгнут', 'Передать в ГЖИ', 'Передано в ГЖИ') NOT NULL");
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
            DB::statement("ALTER TABLE contracts MODIFY status ENUM('В обработке','Есть бумажный договор','Нет бумажного договора','Договор расторгнут') NOT NULL");
        });
    }
}
