<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Game extends Model
{
    use HasFactory;

    // Especifica a tabela associada ao modelo, caso o nome não seja o padrão
    protected $table = 'games';

    // Define os atributos que podem ser atribuídos em massa
    protected $fillable = [
        'created_user_id',
        'winner_user_id',
        'type',
        'status',
        'began_at',
        'ended_at',
        'total_time',
        'board_id',
        'custom',
    ];

    // Define os atributos que devem ser convertidos para tipos específicos
    protected $casts = [
        'began_at' => 'datetime',
        'ended_at' => 'datetime',
        'total_time' => 'decimal:2',
        'custom' => 'array', // Converte JSON para array
    ];

    // Relacionamento com o modelo Board
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    // Relacionamento com o modelo User (criador do jogo)
    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    // Relacionamento com o modelo User (vencedor do jogo)
    public function winnerUser()
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }
}
