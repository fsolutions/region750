<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('path');
            $table->boolean('folder')->default(0)->comment('true = файлы хранятся в одной дериктории');
            $table->string('name');
            $table->string('description')->nullable();
            $table->foreignId('creator_user_id')->constrained('users');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('reference_document_type_id')->nullable()->constrained('reference_properties');
            $table->foreignId('reference_property_document_status_id')
                ->comment('Статус')
                ->nullable()
                ->constrained('reference_properties');
            $table->date('date_of_document')->nullable()->comment('Дата заключения документа');
            $table->softDeletes('deleted_at');
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
        Schema::dropIfExists('documents');
    }
}
