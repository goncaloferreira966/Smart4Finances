<?php
namespace App\Http\Controllers\api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Game;
use Brick\Math\RoundingMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\CustomNotification;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    public function getAllGames(Request $request)
    {
        // SECURITY
        $user = auth()->user();
    
        if ($user->type == 'P') {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        // SECURITY-END
    
        // Recupera jogos com usuários relacionados usando eager loading
        $games = Game::with(['createdUser:id,nickname,photo_filename', 'winnerUser:id,nickname,photo_filename'])
            ->orderBy('created_at', 'desc');
    
        // Filtros opcionais
        //data de x ate y 
        if ($request->has('date_from') && $request->has('date_to')) {
            $games->whereBetween('created_at', [$request->input('date_from'), $request->input('date_to')]);
        }

        //board
        if ($request->has('board')) {
            $games->where('board_id', $request->input('board'));
        }
    
        // Paginação
        $games = $games->paginate(6);
    
        // Verifica se há jogos na base de dados
        if ($games->isEmpty()) {
            return response()->json(['message' => 'Nenhum jogo encontrado'], 404);
        }
    
        // Mapeia o resultado para incluir os dados das fotos e evita exceções
        $games->getCollection()->transform(function ($game) {
            return [
                'id' => $game->id,
                'type' => $game->type,
                'board_id' => $game->board_id,
                'created_user_id' => $game->created_user_id,
                'creator_nickname' => optional($game->createdUser)->nickname,
                'creator_photo' => optional($game->createdUser)->photo_filename 
                    ? url('storage/photos/' . $game->createdUser->photo_filename) 
                    : null,
                'winner_user_id' => $game->winner_user_id,
                'winner_nickname' => optional($game->winnerUser)->nickname,
                'winner_photo' => optional($game->winnerUser)->photo_filename 
                    ? url('storage/photos/' . $game->winnerUser->photo_filename) 
                    : null,
                'created_at' => $game->created_at,
                'updated_at' => $game->updated_at,
                // Adicione outros campos necessários
            ];
        });
    
        return response()->json($games);
    }

    
    public function getGamesByUser(Request $request, $userId)
    {
        // SECURITY
        // Obtém o usuário logado
        $user = auth()->user();
    
        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId && $user->type != 'A') {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        // SECURITY-END

        $query1 = Game::select(
            'id',
            'created_user_id',
            'winner_user_id',
            'board_id',
            'created_at',
            'total_time',
            'custom',
            'type',
            'status',
            'ended_at',
            'began_at'
        )->where('created_user_id', $userId);

        // Aplicar o filtro de data à consulta 1 (jogos criados pelo usuário)
        if ($request->has('date_from') && $request->has('date_to')) {
            $query1->whereBetween('created_at', [$request->input('date_from'), $request->input('date_to')]);
        }
        if ($request->has('board')) {
            $query1->where('board_id', $request->input('board'));
        }
        
        // Filtra jogos onde o usuário foi vencedor
        $winnerQuery = Game::select(
            'id',
            'created_user_id',
            'winner_user_id',
            'board_id',
            'created_at',
            'total_time',
            'custom',
            'type',
            'status',
            'ended_at',
            'began_at'
        )->where('winner_user_id', $userId);

        // Aplicar o filtro de data à consulta 2 (jogos onde o usuário foi o vencedor)
        if ($request->has('date_from') && $request->has('date_to')) {
            $winnerQuery->whereBetween('created_at', [$request->input('date_from'), $request->input('date_to')]);
        }
        if ($request->has('board')) {
            $winnerQuery->where('board_id', $request->input('board'));
        }

        //União dos jogos em que sou o vencedor e/ou criador
        $query = $query1->union($winnerQuery);



        // Filtros opcionais
        //data de x ate y 
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('created_at', [$request->input('date_from'), $request->input('date_to')]);
        }

        //board
        if ($request->has('board')) {
            $query->where('board_id', $request->input('board'));
        }

        //Ordernação 
       /* if ($request->has('order')) {
            Log::info('cgeugeui ao filtro');
        }
        else{
            $query->orderBy('created_at', 'desc');
            Log::info('nenhum ao filtro');
        }*/
    
        // Ordenação    
       // Ordenação
    if ($request->has('order_by')) {
        $orderBy = $request->input('order_by');
        
        // Separar a string de ordenação por vírgulas
        $orderParts = explode(',', $orderBy);
        
        foreach ($orderParts as $part) {
            // Remove espaços extras
            $part = trim($part);
            
            // Separar coluna e direção (ex: 'total_time desc')
            $directionParts = explode(' ', $part);
            
            // Verifica se a direção foi fornecida, caso contrário, usa 'asc' como padrão
            $column = $directionParts[0];
            $direction = isset($directionParts[1]) ? strtolower($directionParts[1]) : 'asc';
            
            // Aplica a ordenação
            if ($column == 'custom.moves') {
                $query->orderByRaw("JSON_EXTRACT(custom, '$.moves') $direction");
            } elseif (in_array($column, ['created_at', 'total_time', 'began_at'])) {
                $query->orderBy($column, $direction);
            }
        }
    } else {
        // Ordenação padrão
        $query->orderBy('created_at', 'desc');
        
    }
    
        // Paginação
        $games = $query->with(['createdUser', 'winnerUser'])->paginate(6); // Carrega os relacionamentos
    
        if ($games->isEmpty()) {
            return response()->json(['message' => 'Nenhum jogo encontrado'], 404);
        }
    
            // Manipula os dados dos jogos antes de retornar
            $games->getCollection()->transform(function ($game) {
                // Adiciona o nickname e a photo_filename do criador
                $game->creator_nickname = $game->createdUser->nickname;
                $game->creator_photo = $game->createdUser->photo_filename 
                    ? url('storage/photos/' . $game->createdUser->photo_filename) 
                    : null;
    
            // Adiciona o nickname e a photo_filename do vencedor (se existir)
            $game->winner_nickname = $game->winnerUser ? $game->winnerUser->nickname : null;
            $game->winner_photo = $game->winnerUser && $game->winnerUser->photo_filename 
                ? url('storage/photos/' . $game->winnerUser->photo_filename) 
                : null;
    
            return $game;
        });
    
        return response()->json($games);
    }

    public function getGamesByUserAndroid($userId)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        $games = Game::where('created_user_id', $userId)->orderBy('created_at', 'desc')->paginate(200000);

        if ($games->isEmpty()) {
            return response()->json(['message' => 'Nenhum jogo encontrado'], 404);
        }

        return response()->json($games);
    }

    public function getGamesByUserAndStatus($userId, $status)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        // Filtra jogos por ID do usuário e status
        $games = Game::where('created_user_id', $userId)
                    ->where('status', $status)
                    ->paginate(9);

        if ($games->isEmpty()) {
            return response()->json(['message' => 'Nenhum jogo encontrado'], 404);
        }

        return response()->json($games);
    }

    public function getTop3GamesByBoard()
    {
        // Define os board_ids desejados
        $boardIds = [1, 2, 3];

        $result = collect();

        foreach ($boardIds as $boardId) {
            // Para cada board_id, pega os 3 jogos com menor total_time
            $games = DB::table('games')
                ->select(
                    'games.id',
                    'games.board_id',
                    'games.created_user_id',
                    'games.created_at',
                    'games.total_time',
                    'games.type',
                    'games.status',
                    'games.began_at',
                    'games.ended_at',
                    'games.custom',
                    'users.nickname' // Adiciona o nickname dos usuários
                )
                ->join('users', 'games.created_user_id', '=', 'users.id') // Faz o join com a tabela users
                ->where('games.board_id', $boardId)
                ->whereNotNull('games.total_time') // Ignora registros sem total_time
                ->orderBy('games.total_time', 'asc')
                ->limit(3)
                ->get();

                foreach ($games as $game) {
                    $game->custom = json_decode($game->custom, true);  // Decodifica o campo 'custom' em array
                }

            $result = $result->merge($games);
        }

        return response()->json($result);
    }

    public function getTop3GamesByBoardMoves()
    {
        // Define os board_ids desejados
        $boardIds = [1, 2, 3];

        $result = collect();

        foreach ($boardIds as $boardId) {
            // Para cada board_id, pega os 3 jogos com menor número de movimentos (custom.moves)
            $games = DB::table('games')
                ->select(
                    'games.id',
                    'games.board_id',
                    'games.created_user_id',
                    'games.created_at',
                    'games.total_time',
                    'games.type',
                    'games.status',
                    'games.began_at',
                    'games.ended_at',
                    'games.custom',
                    'users.nickname' // Adiciona o nickname dos usuários
                )
                ->join('users', 'games.created_user_id', '=', 'users.id') // Faz o join com a tabela users
                ->where('games.board_id', $boardId)
                ->whereNotNull('games.custom') // Ignora registros sem custom
                ->get()
                ->filter(function ($game) {
                    $custom = json_decode($game->custom, true);
                    return isset($custom['moves']); // Filtra apenas jogos que têm 'moves'
                })
                ->sortBy(function ($game) {
                    $custom = json_decode($game->custom, true);
                    return $custom['moves']; // Ordena pelo número de movimentos
                })
                ->take(3); // Pega os 3 jogos com menos movimentos

            // Decodifica o campo 'custom' para cada jogo
            foreach ($games as $game) {
                $game->custom = json_decode($game->custom, true);
            }

            $result = $result->merge($games);
        }

        return response()->json($result);
    }


    public function getTopGameByBoardAndUser($userId)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END
        
        // Define os board_ids desejados
        $boardIds = [1, 2, 3];

        $result = collect();

        foreach ($boardIds as $boardId) {
            // Para cada board_id, pega o jogo com o menor total_time para o usuário específico
            $game = DB::table('games')
                ->select('id', 'board_id', 'created_user_id', 'created_at','total_time', 'type', 'status', 'began_at', 'ended_at', 'custom')
                ->where('board_id', $boardId)
                ->where('created_user_id', $userId)  // Filtra pelo user_id
                ->whereNotNull('total_time')  // Ignorar registros sem total_time
                ->orderBy('total_time', 'asc')  // Ordena pelo menor total_time
                ->first();  // Retorna apenas o primeiro (melhor resultado)

            if ($game) {
                $game->custom = json_decode($game->custom, true);
                $result->push($game);  // Adiciona o jogo ao resultado
            }
        }

        return response()->json($result);
    }

    public function getTopGameByBoardAndUserMoves($userId)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'A'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        // Define os board_ids desejados
        $boardIds = [1, 2, 3];

        $result = collect();

        foreach ($boardIds as $boardId) {
            // Busca todos os jogos válidos para o board atual
            $games = DB::table('games')
                ->select('id', 'board_id', 'created_user_id', 'total_time', 'created_at', 'custom', 'type', 'status', 'began_at', 'ended_at')
                ->where('board_id', $boardId)
                ->where('created_user_id', $userId)  // Filtra pelo user_id
                ->whereNotNull('custom')  // Ignora registros com custom nulo
                ->where('custom', '!=', '[]')  // Ignora registros com custom vazio
                ->get();  // Busca todos os jogos válidos para o board

            // Filtra os jogos que têm o campo 'moves' em custom
            $validGames = $games->filter(function ($game) {
                $customData = json_decode($game->custom, true);
                return !empty($customData) && isset($customData['moves']);
            });

            // Se houver jogos válidos para o board, seleciona o melhor (menor número de moves)
            if ($validGames->isNotEmpty()) {
                $bestGame = $validGames->sortBy(function ($game) {
                    $customData = json_decode($game->custom, true);
                    return $customData['moves'];  // Ordena pelo menor número de moves
                })->first();  // Seleciona o melhor jogo (com menos moves)

                $bestGame->custom = json_decode($bestGame->custom, true);  // Atribui o custom decodificado
                $result->push($bestGame);  // Adiciona o jogo ao resultado
            }
        }

        return response()->json($result);
    }

    public function getTop10GamesByUserByDuration($userId)
    {
        // **SEGURANÇA**
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        // **FIM SEGURANÇA**
    
        try {
            // Obtém os 10 jogos com o menor `total_time` do usuário, independentemente do tabuleiro
            $games = DB::table('games')
                ->select(
                    'id',
                    'board_id',
                    'created_user_id',
                    'created_at',
                    'total_time',
                    'type',
                    'status',
                    'began_at',
                    'ended_at',
                    'custom'
                )
                ->where('created_user_id', $userId) // Filtra pelo user_id
                ->whereNotNull('total_time') // Ignora registros sem total_time
                ->orderBy('total_time', 'asc') // Ordena pelo menor total_time
                ->limit(10) // Retorna apenas os 10 melhores jogos
                ->get();

            // Decodifica o campo `custom` de cada jogo
            foreach ($games as $game) {
                $game->custom = json_decode($game->custom, true);
            }

            return response()->json([
                'message' => 'Top 10 jogos obtidos com sucesso!',
                'games' => $games,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao obter os jogos',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getTop10GamesByUserByMoves($userId)
    {
        // **SEGURANÇA**
        // Obtém o usuário logado
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        // **FIM SEGURANÇA**
        
        try {
            // Obtém os 10 jogos com o menor `custom.moves` do usuário
            $games = DB::table('games')
                ->select(
                    'id',
                    'board_id',
                    'created_user_id',
                    'created_at',
                    'total_time',
                    'type',
                    'status',
                    'began_at',
                    'ended_at',
                    'custom'
                )
                ->where('created_user_id', $userId) // Filtra pelo user_id
                ->whereNotNull('custom') // Ignora registros sem o campo custom
                ->get();

            // Filtra e ordena os jogos pelo campo `custom.moves`
            $games = $games->map(function ($game) {
                $game->custom = json_decode($game->custom, true); // Decodifica o campo custom
                return $game;
            })->filter(function ($game) {
                return isset($game->custom['moves']); // Certifica-se de que o campo moves existe
            })->sortBy('custom.moves') // Ordena pelo menor número de moves
            ->take(10); // Pega os 10 melhores jogos

            return response()->json([
                'message' => 'Top 10 jogos por movimentos obtidos com sucesso!',
                'games' => $games->values(), // Garante que a lista retornada seja um array indexado
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao obter os jogos',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        //SECURITY
        // Obtém o usuário logado
        $user = auth()->user();

        if ($user->type == 'A') {
            // Retorna Unauthorized caso o ID não coincidam e o tipo não seja 'P'
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        //SECURITY-END

        // Validação dos dados recebidos
        $validated = $request->validate([
            'created_user_id' => 'required|exists:users,id',
            'type' => 'required|in:S,M', // Verificar o tipo válido
            'status' => 'required|in:PE,PL,E,I', // Verificar status válido
            'began_at' => 'required|date',
            'total_time' => 'required|numeric',
            'board_id' => 'required|exists:boards,id', // Verificar a tabela boards
            // Campos não obrigatórios
            'winner_user_id' => 'nullable|exists:users,id', // Não é obrigatório
            'ended_at' => 'nullable|date', // Não é obrigatório
            'moves' => 'nullable|integer', // Não é obrigatório
            'score' => 'nullable|integer', // Não é obrigatório
        ]);

        // Adicionando os campos moves e score no campo custom, se existirem
        $custom = [];
        if ($request->has('moves')) {
            $custom['moves'] = $validated['moves'];
        }
        if ($request->has('score')) {
            $custom['score'] = $validated['score'];
        }


        //NOTIFICAÇÕES
        // Mapeamento do board_id para nomes de tabuleiros
        $boardNames = [
            1 => '3x4',
            2 => '4x4',
            3 => '6x6',
        ];

        $boardName = $boardNames[$validated['board_id']] ?? 'Desconhecido';

        // Log do boardName para diagnóstico
        \Log::info("Board Name: {$boardName} (board_id: {$validated['board_id']})");

        // Verificar se é um recorde pessoal
        $personalBest = Game::where('created_user_id', $validated['created_user_id'])
            ->where('board_id', $validated['board_id'])
            ->min('total_time');

        $isPersonalRecord = $personalBest === null || $validated['total_time'] < $personalBest;

        // Verificar se é um recorde geral
        $globalBest = Game::where('board_id', $validated['board_id'])->min('total_time');

        $isGlobalRecord = $globalBest === null || $validated['total_time'] < $globalBest;
    

        // Enviar notificações se forem recordes
        if ($isPersonalRecord) {
            \Log::info("Novo recorde pessoal no tabuleiro {$boardName} com tempo de {$validated['total_time']} segundos.");
            $user->notify(new CustomNotification("Parabéns! Você alcançou um novo recorde pessoal no tabuleiro {$boardName} com o tempo de {$validated['total_time']} segundos!"));
            $user->notify(new CustomNotification("Parabéns! Você recebeu 1 moeda bónus, por ter batido um recorde pessoal!"));
            $user->brain_coins_balance += 1;
            $user->save();  

        }

        if ($isGlobalRecord) {
            \Log::info("Novo recorde geral no tabuleiro {$boardName} com tempo de {$validated['total_time']} segundos.");
            $user->notify(new CustomNotification("Incrível! Você quebrou o recorde geral no tabuleiro {$boardName} com o tempo de {$validated['total_time']} segundos!"));
        }
        //NOTIFICAÇÕES-END




        // Criando o jogo
        $game = Game::create([
            'created_user_id' => $validated['created_user_id'],
            'winner_user_id' => $validated['winner_user_id'] ?? null, // Usando null se não fornecido
            'type' => $validated['type'],
            'status' => $validated['status'],
            'began_at' => $validated['began_at'],
            'ended_at' => $validated['ended_at'] ?? null, // Usando null se não fornecido
            'total_time' => $validated['total_time'],
            'board_id' => $validated['board_id'],
            'custom' => $custom,  // Passando o campo custom com moves e score, se fornecidos
        ]);

        // // Agora, buscar os Top 3 jogos para o tabuleiro do jogo recém-criado
        $top3Games = DB::table('games')
        ->select(
            'games.id',
            'games.board_id',
            'games.created_user_id',
            'games.total_time',
            'users.nickname'
        )
        ->join('users', 'games.created_user_id', '=', 'users.id')
        ->where('games.board_id', $validated['board_id'])
        ->whereNotNull('games.total_time')
        ->orderBy('games.total_time', 'asc')
        ->limit(3)
        ->get();

        // Adicionando o novo jogo ao top 3 e ordenando
        $top3Games = $top3Games->push($game)->sortBy('total_time')->take(3);

        // Verificar se o jogo recém-criado está no Top 3
        $isInTop3 = $top3Games->pluck('id')->contains($game->id);

        \Log::info("isInTop3: {$isInTop3}");

        if ($isInTop3) {
            \Log::info("O jogo entrou no Top 3 Global no tabuleiro {$boardName} com tempo de {$validated['total_time']} segundos.");
            $user->notify(new CustomNotification("Parabéns! Você entrou no Top 3 Global no tabuleiro {$boardName} com o tempo de {$validated['total_time']} segundos!"));
            $user->notify(new CustomNotification("Parabéns! Você recebeu 1 moeda bónus, por entrar no Top 3 Global!"));
            $user->brain_coins_balance += 1;
            $user->save();
        }

        // Retornando a resposta com o jogo criado
        return response()->json($game, 201);
    }

    public function getTop5PlayersByVictoriesByBoard()
    {
        try {
            // Define os boards que você quer pesquisar (1, 2, 3)
            $boardIds = [1, 2, 3];
            $result = collect();
    
            foreach ($boardIds as $boardId) {
                // Query para pegar os 5 jogadores com mais vitórias por board
                $topPlayers = DB::table('games')
                    ->select('winner_user_id', DB::raw('count(*) as victories'))
                    ->where('board_id', $boardId) // Filtra por board
                    ->whereNotNull('winner_user_id')  // Garante que o vencedor exista
                    ->groupBy('winner_user_id')  // Agrupa por vencedor
                    ->orderByDesc('victories')  // Ordena pelo número de vitórias (decrescente)
                    ->limit(5)  // Limita aos 5 primeiros jogadores
                    ->get();
    
                // Adiciona os detalhes dos jogadores (nickname, etc.) usando o ID do usuário
                $topPlayersWithDetails = $topPlayers->map(function ($player) {
                    $player->user = DB::table('users')->where('id', $player->winner_user_id)->first();
                    return $player;
                });
    
                // Adiciona os resultados ao array final
                $result->push([
                    'board_id' => $boardId,
                    'topPlayers' => $topPlayersWithDetails
                ]);
            }
    
            return response()->json([
                'message' => 'Top 5 players by victories by board obtained successfully!',
                'boards' => $result
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching top 5 players by victories by board',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getMultiplayerGameResults($userId)
    {
        // SECURITY
        $user = auth()->user();

        // Verifica se o ID do usuário logado é o mesmo que o ID recebido ou se o tipo é 'A' (administrador)
        if ($user->id != $userId) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Busca jogos multiplayer (tipo 'M') em que o jogador participou
        $games = DB::table('games')
            ->select('id', 'created_user_id', 'winner_user_id', 'status')
            ->where(function ($query) use ($userId) {
                // Filtra jogos onde o jogador participou (como criador)
                $query->where('created_user_id', $userId)->orWhere('winner_user_id', $userId); // Vencedor do jogo
            })
            ->where('type', 'M')  // Apenas jogos multiplayer
            ->get();

        // Inicializa contadores de vitórias e derrotas
        $victories = 0;
        $losses = 0;

        foreach ($games as $game) {
            // Verifica se o jogo foi finalizado (você pode verificar o status ou outro critério de jogo finalizado)
            if ($game->status === 'E') {  // 'E' = Terminado
                // Verifica se o jogador venceu
                if ($game->winner_user_id == $userId) {
                    $victories++;
                } else {
                    $losses++;
                }
            }
        }

        // Retorna as vitórias e derrotas
        return response()->json([
            'victories' => $victories,
            'losses' => $losses
        ]);
    }
}


