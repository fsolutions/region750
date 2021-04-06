<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('contract_on_user_id')->comment('Пользователь')->constrained('users');
            $table->foreignId('creator_user_id')->nullable()->comment('Кто завел?')->constrained('users');
            $table->string('contract_number')->nullable()->comment('Номер договора');
            $table->string('contract_address')->nullable()->comment('Адрес');
            $table->enum('status', ['В обработке', 'Есть бумажный договор', 'Нет бумажного договора', 'Договор расторгнут'])->default('В обработке')->nullable()->comment('Статус договора');
            $table->timestamp('contract_start_datetime', 0)->nullable()->comment('Дата заключения договора');
            $table->text('contract_comment')->nullable()->comment('Комментарий');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
}
