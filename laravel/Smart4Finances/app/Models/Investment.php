<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'roi',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'roi' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}