<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'user_id'];

    // Relação: uma categoria pode ter várias despesas
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    // Relação: uma categoria pertence a um usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
