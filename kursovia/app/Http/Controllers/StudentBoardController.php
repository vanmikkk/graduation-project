<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User_in_course; // Твоя модель связи
use App\Models\Token;         // Твоя модель токенов
use Illuminate\Http\Request;
use Carbon\Carbon;

class StudentBoardController extends Controller
{
    public function index(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = optional(Token::where("token", $token)->first())->user_id;
        if($user_id == null){
            return response()->json("Неверный токен авторизации", 401);
        }
        
        if (!$token) {
            return response()->json(['message' => 'Токен не предоставлен'], 401);
        }

        $tokenRecord = Token::where("token", $token)->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Неверный токен'], 401);
        }

        $userId = $tokenRecord->user_id;

        $enrollments = User_in_course::where("user_id", $userId)->pluck('course_id');

        if ($enrollments->isEmpty()) {
            return response()->json([
                'done' => [],
                'pending' => [],
                'todo' => []
            ]);
        }

        $assignments = Assignment::whereIn('course_id', $enrollments)
            ->join('courses', 'assignments.course_id', '=', 'courses.id')
            ->leftJoin('submissions', function($join) use ($userId) {
                $join->on('assignments.id', '=', 'submissions.assignment_id')
                     ->where('submissions.user_id', '=', $userId);
            })
            ->select(
                'assignments.id',
                'assignments.text as title',
                'assignments.description',
                'assignments.time as deadline',
                'assignments.file as task_file',
                'assignments.course_id',
                'submissions.id as submission_id',
                'submissions.score',
                'submissions.teacher_comment',
                'courses.name as course_name',
                'courses.img_id as course_img'
            )
            ->orderBy('assignments.time', 'asc') 
            ->get();

        $board = [
            'done' => [],
            'pending' => [],
            'todo' => []
        ];

        $now = Carbon::now();

        foreach ($assignments as $task) {
            $deadline = $task->deadline ? Carbon::parse($task->deadline) : null;
            $isOverdue = $deadline && $deadline < $now;

            $taskData = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description ?? '',
                'deadline' => $task->deadline,
                'course_name' => $task->course_name,
                'course_img' => $task->course_img,
                'course_id' => $task->course_id,
                'score' => $task->score,
                'has_submission' => !is_null($task->submission_id),
                'is_overdue' => $isOverdue,
                'link' => "/checkCourseMain/{$task->course_id}", 
            ];

            if (!is_null($task->score)) {
                $board['done'][] = $taskData;
            } 
            elseif (!is_null($task->submission_id)) {
                $board['pending'][] = $taskData;
            } 
            else {
                $board['todo'][] = $taskData;
            }
        }

        usort($board['todo'], function($a, $b) {
            if ($a['is_overdue'] === $b['is_overdue']) {
                return 0;
            }
            return ($a['is_overdue'] ? -1 : 1);
        });

        return response()->json($board);
    }
}