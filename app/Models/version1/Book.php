<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'book_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'book_id', 
        'book_sys_id',
        'book_title',
        'book_author',
        'book_ratings',
        'book_description_short',
        'book_description_long',
        'book_pages',
        'book_cover_photo',
        'book_pdf',
        'book_summary_pdf',
        'book_audio',
        'book_summary_audio',
        'book_cost_usd',
        'book_summary_cost_usd',
        'book_audio_cost_usd',
        'book_audio_summary_cost_usd',
        'bookfull_flagged',
        'booksummary_flagged',
        'created_at',
        'updated_at',
    ];
}
