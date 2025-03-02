<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'description',
        'date',
        'recurring_interval',
        'recurring_interval_unit',
        'receipt'
    ];

    // Relação: cada despesa pertence a uma categoria
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relação: cada despesa pertence a um usuário (se você tiver a tabela users)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
