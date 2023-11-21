<?php

namespace App\Models\version1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'transaction_sys_id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction_id', 
        'transaction_sys_id',
        'transaction_type',
        'transaction_referenced_item_id',
        'transaction_buyer_email',
        'transaction_buyer_phone',
        'transaction_payment_type',
        'transaction_payment_ref_id',
        'transaction_payment_date',
        'transaction_payment_status',
        'created_at',
        'updated_at',
    ];
}
