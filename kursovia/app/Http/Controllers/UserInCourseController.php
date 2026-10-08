<?php

namespace App\Http\Controllers;

use App\Models\User_in_course;
use App\Models\Course;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;

class UserInCourseController extends Controller
{
    public function indexByCourse(Request $request, $id)
    {
        // $users = User_in_course::with('user')->where("course_id", $id)->latest()->get()->pluck('user');
        $users = User_in_course::where("course_id", $id)->latest()->get()->map->user;

        return response()->json($users, 200);
    }

    public function indexByUser(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $courses = User_in_course::where("user_id", $user_id)->latest()->get()->map->course;
        return response()->json($courses, 200);
    }

    public function create(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $course = Course::where("access_code", $request->access_code)->first();
        if($course == null){
            return response()->json("Код не верный", 422);
        }
        if(User_in_course::where("user_id", $user_id)->where("course_id", $course->id)->first() != null){
            return response()->json("Пользователь существует", 422);
        }
        if ($course->user_id == $user_id) {
            return response()->json("Нельзя добавить пользователя в свой же курс", 403);
        }
        User_in_course::create([
            "user_id" => $user_id,
            "course_id" => $course->id,
        ]);
        return response()->json("Пользователь добавлен", 200);
    }

    public function delete(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $tokenRecord = Token::where("token", $token)->first();
        
        if (!$tokenRecord) {
            return response()->json(['message' => 'Неавторизован'], 401);
        }
        
        $course_id = $request->input("course_id");

        // 1. Находим курс, чтобы проверить, не является ли пользователь его автором
        $course = Course::find($course_id);
        
        if (!$course) {
            return response()->json(['message' => 'Курс не найден'], 404);
        }

        // 2. Проверка: автор не может отписаться от своего курса (он должен его удалить или закрыть)
        if ($course->user_id == $user_id) {
            return response()->json(['message' => 'Создатель курса не может отписаться от него. Удалите курс, если он больше не нужен.'], 403);
        }

        // 3. Ищем запись о подписке
        $enrollment = User_in_course::where("user_id", $user_id)
                                    ->where("course_id", $course_id)
                                    ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Вы не зачислены на этот курс'], 404);
        }

        // 4. Удаляем запись
        $enrollment->delete();

        return response()->json(['message' => 'Вы успешно отписались от курса'], 200);
    }

    public function studentCount(Request $request){
        $courseId = $request->input("course_id");
        $count = User_in_course::where('course_id', $courseId)->count();

        return response()->json([
            'count' => $count
        ], 200);
    }

}
