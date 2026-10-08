<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserInCourseController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\StudentBoardController;
use App\Http\Controllers\PersonalBoardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, "login"]);
Route::post('/logout', [AuthController::class, "logout"]);
Route::post('/register', [AuthController::class, "register"]);
Route::post('/password', [AuthController::class, "password"]);

Route::get('/users', [UserController::class, "index"]);
Route::post('/users', [UserController::class, "indexId"]);
Route::post('/users/create', [UserController::class, "create"]);
Route::patch('/users/{id}', [UserController::class, "update"]);
Route::delete('/users/{id}', [UserController::class, "delete"]);

Route::get('/courses', [CourseController::class, "index"]);
Route::get('/courses/owner', [CourseController::class, "indexByUserOwner"]);
Route::post('/course', [CourseController::class, "indexId"]); // id тут course_id
Route::post('/courses', [CourseController::class, "create"]);
Route::patch('/courses/{id}', [CourseController::class, "update"]); // id тут course_id
Route::delete('/courses/{id}', [CourseController::class, "delete"]); // id тут course_id

// Route::get('/userincourses', [UserInCourseController::class, "index"]);
Route::post('/courses/students/count', [UserInCourseController::class, "studentCount"]); 
Route::get('/coursebyuser', [UserInCourseController::class, "indexByUser"]); // id тут user_id
Route::get('/userbycourses/{id}', [UserInCourseController::class, "indexByCourse"]); // id тут course_id
Route::post('/userincourses', [UserInCourseController::class, "create"]);
Route::delete('/course/leave', [UserInCourseController::class, "delete"]); // id тут user_in_course_id

Route::post('/assignment', [AssignmentController::class, "indexByCourse"]); // id тут course_id
Route::post('/assignment/get/{id}', [AssignmentController::class, "indexId"]); // id тут course_id
Route::post('/assignment/{id}', [AssignmentController::class, "create"]); // id тут course_id
Route::patch('/assignment/{id}', [AssignmentController::class, "update"]); // id тут assignment_id
Route::delete('/assignment/{id}', [AssignmentController::class, "delete"]); // id тут assignment_id

Route::post('/submission/one/{id}', [SubmissionController::class, "indexByAssignment"]); // id тут assignment_id
Route::post('/submission/user/{id}', [SubmissionController::class, "indexByUser"]); // id тут assignment_id
Route::post('/submission/{id}', [SubmissionController::class, "create"]); // id тут assignment_id
Route::patch('/submission/{id}', [SubmissionController::class, "update"]); // id тут submission_id
Route::delete('/submission/{id}', [SubmissionController::class, "delete"]); // id тут submission_id

Route::patch('/submission/updateScore/{id}', [SubmissionController::class, "updateScore"]); // id тут submission_id
Route::patch('/submission/updateComment/{id}', [SubmissionController::class, "updateComment"]); // id тут submission_id

Route::get('/feedback', [FeedbackController::class, 'index']);
Route::post('/feedback', [FeedbackController::class, 'store']);

Route::post('/posts', [PostController::class, "indexByCourse"]);
Route::post('/posts/create', [PostController::class, "create"]);
Route::delete('/posts/delete', [PostController::class, "delete"]);
Route::patch('/posts/update', [PostController::class, "update"]);

Route::post('/comments', [CommentController::class, "indexByPost"]);
Route::post('/comments/create', [CommentController::class, "create"]);
Route::delete('/comments/delete', [CommentController::class, "delete"]);
Route::patch('/comments/update', [CommentController::class, "update"]);

Route::post('/student/board', [StudentBoardController::class, 'index']);

// Личная доска (Trello)
Route::post('/personal/board', [PersonalBoardController::class, 'index']);
Route::post('/personal/card', [PersonalBoardController::class, 'storeCard']);
Route::post('/personal/update-order', [PersonalBoardController::class, 'update']); // Для Drag-and-Drop
Route::post('/personal/column', [PersonalBoardController::class, 'storeColumn']);
Route::put('/personal/column/{id}', [PersonalBoardController::class, 'updateColumn']);
Route::delete('/personal/column/{id}', [PersonalBoardController::class, 'destroyColumn']);
Route::delete('/personal/card/{id}', [PersonalBoardController::class, 'destroyCard']);
Route::put('/personal/card/{id}', [PersonalBoardController::class, 'updateCard']);