<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions'; // Nome da tabela

    protected $fillable = [
        'transaction_datetime',
        'user_id',
        'game_id',
        'type',
        'euros',
        'brain_coins',
        'payment_type',
        'payment_reference',
        'custom'
    ];

    protected $casts = [
        'transaction_datetime' => 'datetime',
        'euros' => 'decimal:2',
        'brain_coins' => 'integer',
        'custom' => 'array',
    ];

    public $timestamps = false; // Remove timestamps padrão, se a tabela não tiver created_at e updated_at
}
