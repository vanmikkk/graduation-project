<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\Token;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input("email");
        $password = $request->input("password");
        try{
            $user = User::where('email', $email)->first();
            if(Hash::check($password, $user->password)){
                $token = Token::updateOrCreate(['user_id'=> $user->id], ['token' => Hash::make($user->email.time())]);
                return response()->json(["token" => $token->token], 200);
            }else{
                return response()->json([
                    "message" => "Invalid data",
                    "errors" =>[
                        "email" => "Invalid data"
                ]], 422); 
            }
        } catch(\Exception $e){
            return response()->json([
                'error' => 'Ошибка сервера при авторизации',
                'message' => $e->getMessage() 
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            'FIO' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Ошибка валидации',
                'messages' => $validator->errors()
            ], 422);
        }
        try {
            $user = User::create([
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'FIO' => $request->input('FIO'),
                'role' => 'user',
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Пользователь успешно зарегистрирован',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'FIO' => $user->FIO
                ]
            ], 201); 
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ошибка сервера при регистрации',
                'message' => $e->getMessage() 
            ], 500);
        }
    }

    public function logout(Request $request){
        try{
            $token = Token::where("token", $request->bearerToken())->first();
            $token->delete();
            return response()->json(["message" => "Пользователь вышел из аккаунта"], 200);
        }
        catch(\Exception $e){
            return response()->json([
                'error' => 'Ошибка сервера',
                'message' => $e->getMessage() 
            ], 500);
        } 
    }

    public function password(Request $request)
    {
        // 1. Валидация
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
    
        // 2. Поиск пользователя
        $user = User::where('email', $request->email)->first();
    
        // 3. Генерация нового случайного пароля (8-10 символов)
        $newPassword = Str::random(10); 
    
        // 4. Обновление пароля в базе данных (обязательно хешируем!)
        $user->password = Hash::make($newPassword);
        $user->save();
    
        // 5. Отправка письма с новым паролем
        Mail::raw("Здравствуйте!\n\nВаш новый пароль для входа в ClassMan: {$newPassword}", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Восстановление пароля ClassMan');
        });
    
        // 6. Ответ клиенту
        return response()->json([
            'message' => 'Новый пароль успешно отправлен на вашу почту.'
        ], 200);
    }
}
