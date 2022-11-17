<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->bigIncrements('book_id');
            $table->string('book_sys_id', 255)->unique();
            $table->string('book_title', 255);
            $table->string('book_author');
            //$table->datetime('passcode_set_time')->nullable();
            $table->integer('book_ratings')->default(0);
            $table->string('book_description_short', 255)->default("");
            $table->text('book_description_long')->nullable();
            $table->integer('book_pages')->default(0);
            $table->string('book_cover_photo', 255)->unique();
            $table->string('book_pdf', 255)->unique();
            $table->string('book_summary_pdf', 255)->default("");
            $table->string('book_audio', 255)->default("");
            $table->string('book_summary_audio', 255)->default("");
            $table->integer('read_count')->default(0);
            $table->integer('buy_count')->default(0);
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
        Schema::dropIfExists('books');
    }
};
