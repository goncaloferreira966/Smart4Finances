<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    // Atributos atribuíveis em massa
    protected $fillable = [
        'user_id',
        'source',
        'amount',
        'date',
        'receipt',
        'recurring_interval_unit',
        'recurring_interval',
    ];

    // Converte os campos para os tipos apropriados
    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
