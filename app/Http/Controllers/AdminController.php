<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Eptool;
use App\Models\Certificate;
use App\Models\Insurance;
use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    //Users

    public function getUser($user_id)
    {
        $user = User::find($user_id);
        if (is_null($user)) {
            return response()->json(["message" => "No users available"], 404);
        }
        return response()->json(
            [
                "user" => $user
            ],
            200
        );
    }

    public function getUsers()
    {

        $userId = auth()->user()->id;
        // $user = User::where('id', $userId)->first();
        // if ($user->role != 'Admin') {
        //     return  response()->json(
        //         [
        //             "message" => 'No Permission',
        //         ],
        //         404
        //     );
        // }
        $users = User::all();
        if ($users->isEmpty()) {
            return response()->json(["message" => "No users available"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "users" => $users
            ],
            200
        );
    }

    public function updateUser(Request $request, $userId)
    {
        $user = User::where('id', $userId)->first();
        $user = User::find($userId);
        if (is_null($user)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }


        $rules = [
            // 'name' => 'max:255|min:3',
            'email' => 'email',
            // 'gender' => 'max:6|min:4',
            'imagePath' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'birthDate' => 'date',
            // 'area' => 'max:20|min:3',
            // 'bankName' => 'max:20|min:2',
            // 'rocket' => 'max:20|min:2',
            // 'bkash' => 'max:20|min:2',
            // 'nogod' => 'max:20|min:2',
            // 'role' => 'max:10|min:4',

        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        if ($request->file('imagePath')) {

            if ($user->imagePath) {
                $path = public_path() . "/" . $user->imagePath;
                unlink($path);
            }
            $file = $request->file('imagePath');
            $filename = time() . '.' . $file->extension();
            $file->move(public_path('Photos'), $filename);
            $user->imagePath = 'Photos/' . $filename;
        }
        $user->update($request->except('imagePath'));
        return response()->json([
            "message" => "Updated Successfully"
        ], 204);
    }
}
