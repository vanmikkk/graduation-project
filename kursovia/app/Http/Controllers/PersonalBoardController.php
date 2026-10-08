<?php

namespace App\Http\Controllers;

use App\Models\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Board;
use App\Models\Column;
use App\Models\Card;

class PersonalBoardController extends Controller
{
    private function getUserIdFromToken(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) return null;
        
        $tokenRecord = Token::where("token", $token)->first();
        return $tokenRecord ? $tokenRecord->user_id : null;
    }

    public function index(Request $request)
    {
        $userId = $this->getUserIdFromToken($request);
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);
        $board = Board::where('user_id', $userId)->first();

        if (!$board) {
            $board = Board::create([
                'user_id' => $userId,
                'config' => json_encode(['theme' => 'default']) 
            ]);
        }

        $columns = Column::where('board_id', $board->id)
            ->orderBy('sort')
            ->get()
            ->map(function ($column) use ($userId) {
                $cards = Card::where('column_id', $column->id)
                    ->orderBy('sort')
                    ->get();

                return [
                    'id' => $column->id,
                    'name' => $column->name,
                    'sort' => $column->sort,
                    'cards' => $cards
                ];
            });

        return response()->json([
            'board_id' => $board->id,
            'columns' => $columns
        ]);
    }


    public function storeCard(Request $request)
    {
        $userId = $this->getUserIdFromToken($request);
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'column_id' => 'required|exists:columns,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date', 
        ]);

        $column = Column::findOrFail($validated['column_id']);
        $board = Board::findOrFail($column->board_id);
        
        if ($board->user_id !== $userId) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $maxSort = Card::where('column_id', $column->id)->max('sort');

        $card = Card::create([
            'user_id' => $userId,
            'column_id' => $column->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'date' => $validated['date'] ?? null,
            'sort' => ($maxSort ?? 0) + 1,
        ]);

        return response()->json($card, 201);
    }


    public function update(Request $request)
    {
        $userId = $this->getUserIdFromToken($request);
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);
        
        $data = $request->all();
        
        if (!isset($data['type']) || !isset($data['items'])) {
            return response()->json(['message' => 'Invalid data format'], 422);
        }

        DB::transaction(function () use ($data, $userId) {
            foreach ($data['items'] as $item) {
                if ($data['type'] === 'cards') {
                    $card = Card::where('id', $item['id'])->first();
                    if ($card && $card->user_id == $userId) {
                        $card->update([
                            'column_id' => $item['column_id'],
                            'sort' => $item['sort']
                        ]);
                    }
                } elseif ($data['type'] === 'columns') {
                    $column = Column::join('boards', 'columns.board_id', '=', 'boards.id')
                        ->where('columns.id', $item['id'])
                        ->where('boards.user_id', $userId)
                        ->select('columns.*')
                        ->first();
                    
                    if ($column) {
                        $column->update(['sort' => $item['sort']]);
                    }
                }
            }
        });

        return response()->json(['message' => 'Updated successfully']);
    }

    public function destroyCard(Request $request, $id)
    {
        $userId = $this->getUserIdFromToken($request);
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);

        $card = Card::where('id', $id)->where('user_id', $userId)->first();
        if (!$card) return response()->json(['message' => 'Card not found'], 404);

        $card->delete();
        return response()->json(['message' => 'Deleted']);
    }
    

    public function updateCard(Request $request, $id)
    {
        $userId = $this->getUserIdFromToken($request);
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);

        $card = Card::where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

        if (!$card) {
            return response()->json(['message' => 'Карточка не найдена'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $card->update($validated);

        return response()->json($card);
    }
    
    public function storeColumn(Request $request)
    {
        $userId = $this->getUserIdFromToken($request);
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'board_id' => 'required|exists:boards,id',
        ]);

        $board = Board::where('id', $validated['board_id'])
                      ->where('user_id', $userId)
                      ->firstOrFail();

        $maxSort = Column::where('board_id', $board->id)->max('sort');

        $column = Column::create([
            'board_id' => $board->id,
            'name' => $validated['name'],
            'sort' => (int)($maxSort ?? 0) + 1,
        ]);

        return response()->json($column, 201);
    }

    public function updateColumn(Request $request, $id)
    {
        $userId = $this->getUserIdFromToken($request);
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $column = Column::join('boards', 'columns.board_id', '=', 'boards.id')
            ->where('columns.id', $id)
            ->where('boards.user_id', $userId)
            ->select('columns.*')
            ->first();

        if (!$column) {
            return response()->json(['message' => 'Колонка не найдена или доступ запрещен'], 404);
        }

        $column->update(['name' => $validated['name']]);

        return response()->json($column);
    }

    public function destroyColumn(Request $request, $id)
    {
        $userId = $this->getUserIdFromToken($request);
        if (!$userId) return response()->json(['message' => 'Unauthorized'], 401);

        $column = Column::join('boards', 'columns.board_id', '=', 'boards.id')
            ->where('columns.id', $id)
            ->where('boards.user_id', $userId)
            ->select('columns.*')
            ->first();

        if (!$column) {
            return response()->json(['message' => 'Колонка не найдена или доступ запрещен'], 404);
        }

        Card::where('column_id', $column->id)->delete();

        $column->delete();

        return response()->json(['message' => 'Колонка удалена']);
    }
}