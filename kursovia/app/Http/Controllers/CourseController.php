<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User_in_course;
use App\Models\Token;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::all();

        return response()->json($courses, 200);
    }

    public function indexId(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $course_id = $request->input("course_id");

        $course = User_in_course::where("user_id", $user_id)->where("course_id", $course_id)->first();
        $ownerCourse = Course::where("user_id", $user_id)->where("id", $course_id)->first();

        if($course == null && $ownerCourse == null){
            return response()->json("У вас нет доступа к этому курсу", 422);
        }

        $course = Course::where("id", $request->input("course_id"))->first();

        return response()->json($course, 200);
    }

    public function indexByUserOwner(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $course = Course::where("user_id", $user_id)->latest()->get();

        return response()->json($course, 200);
    }

    public function create(Request $request)
    {
        $name = $request->input("name");
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }

        $randomNumber = rand(1, 10);

        $course = Course::create([
            "name" => $name,
            "access_code" => $this->generateSecureCode(6),
            "user_id" => $user_id,
            "img_id" => $randomNumber,
        ]);

        return response()->json($course, 200);
    }

    private function generateSecureCode($length = 6)
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';

        $code = '';
        $code .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $code .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $code .= $numbers[random_int(0, strlen($numbers) - 1)];

        $allChars = $uppercase . $lowercase . $numbers;
        $remainingLength = $length - 3;

        for ($i = 0; $i < $remainingLength; $i++) {
            $code .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($code);
    }
    
    public function update(Request $request, $id)
    {
        $name = $request->input("name");
        $course = Course::where("id", $id)->update([
            "name" => $name,
        ]);

        return response()->json($course, 200);
    }

    public function delete($id, Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $course_id = $id;

        $ownerCourse = Course::where("user_id", $user_id)->where("id", $course_id)->first();

        if($ownerCourse == null){
            return response()->json("У вас нет доступа к этому курсу", 422);
        }

        $subscribers = User_in_course::where('course_id', $course_id)->get();
    
        // Получаем название курса для письма (пока оно еще есть в БД)
        $courseName = $ownerCourse->name;

        // ✅ ШАГ 2: Отправляем уведомления всем подписчикам
        foreach ($subscribers as $subscriber) {
            // Предполагаем, что у вас есть модель User и связь или метод получения email
            // Если в User_in_course хранится только user_id, нужно найти пользователя:
            $user = \App\Models\User::find($subscriber->user_id);
            
            if ($user && $user->email) {
                Mail::raw("Здравствуйте!\n\nКурс '{$courseName}', на который вы были подписаны, был удален преподавателем.\n\nВсе материалы и прогресс по этому курсу также удалены.", function ($message) use ($user, $courseName) {
                    $message->to($user->email)
                            ->subject("Курс '{$courseName}' удален");
                });
            }
        }

        $course = Course::where("id", $id)->delete();

        return response()->json($course, 200);
    }

    


    
}
