<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        // Берем только одобренные, сортируем по новизне
        $feedbacks = Feedback::where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($feedbacks);
    }

    // Сохранение нового отзыва
    public function store(Request $request)
    {
        // Валидация
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        // Создаем запись
        // is_approved по умолчанию false (нужна модерация)
        $feedback = Feedback::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => $validated['message'],
            'rating' => $validated['rating'] ?? 5,
            'is_approved' => true, 
        ]);

        return response()->json([
            'message' => 'Спасибо! Ваш отзыв отправлен на модерацию.',
            'data' => $feedback
        ], 201);
    }
    
    // (Опционально) Метод для админа: одобрить отзыв
    public function approve($id) {
        $feedback = Feedback::findOrFail($id);
        $feedback->update(['is_approved' => true]);
        return response()->json(['message' => 'Отзыв одобрен']);
    }
}
