<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Token;
use App\Models\User_in_course;
use App\Models\Course;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexByAssignment(Request $request, $id){
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        $course_id = $request->input("course_id");

        $course = User_in_course::where("user_id", $user_id)->where("course_id", $course_id)->first();
        $ownerCourse = Course::where("user_id", $user_id)->where("id", $course_id)->first();

        if($course == null && $ownerCourse == null){
            return response()->json([
                "message" => "У вас нет доступа к этому курсу",
            ], 422);
        } 

        $submissions = DB::table('submissions')
            ->join('users', 'submissions.user_id', '=', 'users.id') 
            ->where('submissions.assignment_id', $id)
            ->select(
                'submissions.id',
                'submissions.file',
                'submissions.score',
                'submissions.user_comment',
                'submissions.teacher_comment',
                'submissions.user_id',
                'submissions.assignment_id',
                'submissions.created_at',
                'submissions.updated_at',
                'users.FIO' 
            )
            ->latest()
            ->get();

        return response()->json($submissions, 200);
    }

    public function indexByUser(Request $request, $id)
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
            return response()->json([
                "message" => "У вас нет доступа к этому курсу",
                "user_id" => $user_id,
                "course_id" => $course_id,
                "курс id" => $course,
                "123" => $ownerCourse
            ], 422);
        } 

        $submission = Submission::where("assignment_id", $id)->where("user_id", $user_id)->first();

        return response()->json($submission, 200);
    }

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
            'user_comment' => 'nullable|string|max:100',
        ]);


        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('submissions', $fileName, 'public');
            $submission = Submission::create([
                'file' => $filePath,
                'user_comment' => $request->input('user_comment'),
                'user_id' => $user_id,
                'assignment_id' => $id,
            ]);
            return response()->json([
                'message' => 'Файл успешно загружен',
                'attachment' => [
                    'id' => $submission->id,
                    'file' => asset('storage/' . $submission->file),
                    'score' => $submission->score,
                    'user_comment' => $submission->user_comment,
                    'teacher_comment' => $submission->teacher_comment,
                    'created_at' => $submission->created_at,
                    'user_id' => $submission->user_id,
                    'assignment_id' => $id,
                ]
            ], 201);
        }

        return response()->json(['error' => 'Файл не был получен'], 400);
    }

    public function updateScore(Request $request, $id){
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

        Submission::where("id", $id)->update([
            "score" => $request->score,
        ]);
    }

    public function updateComment(Request $request, $id){
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

        Submission::where("id", $id)->update([
            "teacher_comment" => $request->teacher_comment,
        ]);
    }

    public function update(Request $request, $id)
    {
        $submission = Submission::where('id', $id)->firstOrFail(); 
        //dd($request);

        $request->validate([
            'file' => 'nullable|file|max:10240', // макс 10 МБ
            'score' => 'nullable|regex:/^[0-5]$/',
            'user_comment' => 'string|max:100',
            'teacher_comment' => 'string|max:100',
        ]);

        $dataToUpdate = [
            'score' => $request->input('score'),
            'user_comment' => $request->input('user_comment'),
            'teacher_comment' => $request->input('teacher_comment'),
        ];

        if ($request->hasFile('file')) {
            if ($submission->file) {
                Storage::disk('public')->delete($submission->file);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('submissions', $fileName, 'public');
            $dataToUpdate['file'] = $filePath;
        }

        $submission->update($dataToUpdate);

        return response()->json([
            'message' => 'Задание обновлено',
            'attachment' => [
                'id' => $submission->id,
                'file' => $submission->file ? asset('storage/' . $submission->file) : null,
                'score' => $submission->score,
                'user_comment' => $submission->user_comment,
                'teacher_comment' => $submission->teacher_comment,
                'created_at' => $submission->created_at,
            ]
        ], 200);
    }

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
        
        $submission = Submission::where('id', $id)->firstOrFail();
        Storage::disk('public')->delete($submission->file);
        $submission->delete();

        return response()->json(['message' => 'Ответ удален']);
    }
}
