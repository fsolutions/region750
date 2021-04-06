<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment')->comment('Комменрарий');
            $table->integer('parent_comment_id')->comment('Комментарий родитель')->nullable();
            $table->foreignId('creator_user_id')->comment('Пользователь')->constrained('users');
            $table->foreignId('document_id')->comment('Документ')->nullable()->constrained('documents');
            $table->foreignId('order_id')->comment('Обращение')->nullable()->constrained('orders');
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
        Schema::dropIfExists('user_comments');
    }
}
