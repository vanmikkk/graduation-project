<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Token;

use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json($users, 200);
    }

    public function indexId(Request $request)
    {
        $token = $request->bearerToken();
        $user_id = Token::where("token", $token)->first()->user_id;

        return response()->json($user_id, 200);
    }

    public function create(Request $request)
    {
        $email = $request->input("email");
        $password = $request->input("password");
        $FIO = $request->input("FIO");
        $role = $request->input("role");

        $user = User::create([
            "email" => $email,
            "password" => Hash::make($password),
            "FIO" => $FIO,
            "role" => $role,
        ]);

        return response()->json($user, 200);
    }
    
    public function update(Request $request, $id)
    {
        $email = $request->input("email");
        $password = $request->input("password");
        $FIO = $request->input("FIO");
        $role = $request->input("role");

        $user = User::where("id", $id)->update([
            "email" => $email,
            "password" => Hash::make($password),
            "FIO" => $FIO,
            "role" => $role,
        ]);

        return response()->json($user, 200);
    }

    public function delete($id)
    {
        $user = User::where("id", $id)->delete();

        return response()->json($user, 200);
    }

}
