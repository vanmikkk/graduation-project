<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User_in_course;
use App\Models\Course;
use App\Models\Token;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexByCourse(Request $request)
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

        $posts = Post::where("course_id", $request->input("course_id"))->latest()->get();

        return response()->json($posts, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $course_id = $request->input("course_id");

        $ownerCourse = Course::where("user_id", $user_id)->where("id", $course_id)->first();

        if($ownerCourse == null){
            return response()->json("У вас нет доступа к этому курсу", 422);
        }

        $request->validate([
            'text' => 'string|max:255',
        ]);

        $post = Post::create([
            'text' => $request->input('text'),
            'user_id' => $user_id,
            'course_id' => $course_id,
        ]);

        return response()->json([
            'message' => 'Пост успешно добавлен',
            'post' => [
                'text' => $post->text,
                'user_id' => $post->user_id,
                'course_id' => $post->course_id,
            ]
        ], 201);
    }

    public function update(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $course_id = $request->input("course_id");

        $ownerCourse = Course::where("user_id", $user_id)->where("id", $course_id)->first();

        if($ownerCourse == null){
            return response()->json("У вас нет доступа к этому курсу", 422);
        }

        $post = Post::where("id", $request->input("id"))->first();

        $post->update([
            'text' => $request->input('text'),
        ]);

        return response()->json([
            'message' => 'Пост успешно обновлен',
            'post' => [
                'text' => $post->text,
                'user_id' => $post->user_id,
                'course_id' => $post->course_id,
            ]
        ], 201);
    }

    public function delete(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $course_id = $request->input("course_id");

        $ownerCourse = Course::where("user_id", $user_id)->where("id", $course_id)->first();

        if($ownerCourse == null){
            return response()->json("У вас нет доступа к этому курсу", 422);
        }

        Post::where("id", $request->input("id"))->delete();

        return response()->json([
            'message' => 'Пост успешно удален',
        ], 201);
    }
}
