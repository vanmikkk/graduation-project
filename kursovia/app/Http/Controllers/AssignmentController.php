<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Token;
use App\Models\User_in_course;
use App\Models\Course;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
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

        $assignments = Assignment::where("course_id", $request->input("course_id"))->latest()->get();

        return response()->json($assignments, 200);
    }

    public function indexId(Request $request, $id)
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

        $assignment = Assignment::where("course_id", $request->input("course_id"))->where("id", $id)->first();

        return response()->json($assignment, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $id)
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
            'file' => 'nullable|file|max:10240',
            'text' => 'string|max:100',
            'time' => 'string|max:100',
            'description' => 'nullable|string|max:500',
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('assignments', $fileName, 'public');
            $assignment = Assignment::create([
                'file' => $filePath,
                'text' => $request->input('text'),
                'time' => $request->input('time'),
                'description' => $request->input('description'),
                'user_id' => $user_id,
                'course_id' => $id,
            ]);
            return response()->json([
                'message' => 'Файл успешно загружен',
                'attachment' => [
                    'id' => $assignment->id,
                    'file' => asset('storage/' . $assignment->file),
                    'text' => $assignment->text,
                    'time' => $assignment->time,
                    'description' => $assignment->description,
                    'created_at' => $assignment->created_at,
                    'course_id' => $id,
                ]
            ], 201);
        } else {
            $assignment = Assignment::create([
                'file' => NULL,
                'text' => $request->input('text'),
                'time' => $request->input('time'),
                'description' => $request->input('description'),
                'user_id' => $user_id,
                'course_id' => $id,
            ]);
            return response()->json([
                'message' => 'Задание создано',
                'attachment' => [
                    'id' => $assignment->id,
                    'file' => $assignment->file,
                    'text' => $assignment->text,
                    'time' => $assignment->time,
                    'description' => $assignment->description,
                    'created_at' => $assignment->created_at,
                    'course_id' => $id,
                ]
            ], 201);
        }

        return response()->json(['error' => 'Ошибка'], 400);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
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

        $assignment = Assignment::where('id', $id)->firstOrFail(); 
        $request->validate([
            'file' => 'nullable|file|max:10240',
            'text' => 'string|max:100',
            'time' => 'string|max:100',
            'description' => 'nullable|string|max:500',
        ]);
        $dataToUpdate = [
            'text' => $request->input('text'),
            'time' => $request->input('time'),
            'description' => $request->input('description'),
        ];
        if ($request->hasFile('file')) {
            if ($assignment->file) {
                Storage::disk('public')->delete($assignment->file);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('assignments', $fileName, 'public');
            $dataToUpdate['file'] = $filePath;
        }
        $assignment->update($dataToUpdate);
        return response()->json([
            'message' => 'Задание обновлено',
            'attachment' => [
                'id' => $assignment->id,
                'file' => $assignment->file ? asset('storage/' . $assignment->file) : null,
                'text' => $assignment->text,
                'time' => $assignment->time,
                'description' => $assignment->description,
                'created_at' => $assignment->created_at,
                'course_id' => $assignment->course_id, 
            ]
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, $id)
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

        $assignment = Assignment::where('id', $id)->firstOrFail();

        if($assignment->file != null)
            Storage::disk('public')->delete($assignment->file);

        $assignment->delete();

        return response()->json(['message' => 'Вложение удалено']);
    }
}
