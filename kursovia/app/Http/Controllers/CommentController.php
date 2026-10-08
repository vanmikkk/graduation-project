<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User_in_course;
use App\Models\Course;
use App\Models\Token;
use App\Models\Comment;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function indexByPost(Request $request)
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

        //$comments = Comment::where("post_id", $request->input("post_id"))->latest()->get();

        $comments = DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id') 
            ->where('comments.post_id', $request->input("post_id"))
            ->select(
                'comments.id',
                'comments.text',
                'comments.user_id',
                'comments.post_id',
                'comments.created_at',
                'comments.updated_at',
                'users.FIO'
            )
            ->latest()
            ->get();

        return response()->json([
            "comments" => $comments,
            "uid" => $user_id

        ], 200);
    }

    public function create(Request $request)
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

        $request->validate([
            'text' => 'string|max:255',
        ]);

        $comment = Comment::create([
            'text' => $request->input('text'),
            'user_id' => $user_id,
            'post_id' => $request->input("post_id"),
        ]);

        return response()->json([
            'message' => 'Коммент успешно добавлен',
            'comment' => [
                'id' => $comment->id,
                'text' => $comment->text,
                'user_id' => $comment->user_id,
                'post_id' => $comment->post_id,
                'created_at' => $comment->created_at,
                'updated_at' => $comment->updated_at,
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

        $course = User_in_course::where("user_id", $user_id)->where("course_id", $course_id)->first();
        $ownerCourse = Course::where("user_id", $user_id)->where("id", $course_id)->first();

        if($course == null && $ownerCourse == null){
            return response()->json("У вас нет доступа к этому курсу", 422);
        }

        $comment = Comment::where("id", $request->input("id"))->first();

        $comment->update([
            'text' => $request->input('text'),
        ]);

        return response()->json([
            'message' => 'Комментарий успешно обновлен',
            'comment' => [
                'text' => $comment->text,
                'user_id' => $comment->user_id,
                'post_id' => $comment->post_id,
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

        $course = User_in_course::where("user_id", $user_id)->where("course_id", $course_id)->first();
        $ownerCourse = Course::where("user_id", $user_id)->where("id", $course_id)->first();

        if($course == null && $ownerCourse == null){
            return response()->json("У вас нет доступа к этому курсу", 422);
        }

        Comment::where("id", $request->input("id"))->delete();

        return response()->json([
            'id' => $request->input("id"),
        ], 201);
    }
}
