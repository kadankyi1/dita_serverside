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
            $table->string('book_categories_ids', 255)->default("");
            $table->integer('book_pages')->default(0);
            $table->string('book_cover_photo', 255)->unique();
            $table->string('book_pdf', 255)->default("");
            $table->string('book_summary_pdf', 255)->unique();
            $table->string('book_audio', 255)->default("");
            $table->string('book_summary_audio', 255)->default("");
            $table->decimal('book_cost_usd', 12, 2);
            $table->decimal('book_summary_cost_usd', 12, 2);
            $table->decimal('book_audio_cost_usd', 12, 2);
            $table->decimal('book_audio_summary_cost_usd', 12, 2);
            $table->integer('read_count')->default(0);
            $table->integer('buy_count')->default(0);
            $table->boolean('bookfull_flagged')->default(false);
            $table->boolean('booksummary_flagged')->default(false);
            $table->timestamps();
        });


        /*
        // FOREIGN KEY
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedBigInteger('book_category_id');
            $table->foreign('book_category_id')->references('category_id')->on('categories');
        });
        */
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
