<?php

namespace App\Http\Controllers;

use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Validator;

use Ferdous\OtpValidator\Object\OtpRequestObject;
use Ferdous\OtpValidator\OtpValidator;
use Ferdous\OtpValidator\Object\OtpValidateRequestObject;




class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    //User Register Api -POST
    public function register(Request $request)
    {

        $rules = [
            'email' => 'required|email|unique:users',
            'password' => 'required',


        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        $user = new User();
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        return response()->json([
            "message" => "User registered successfully"
        ],200);

    }

    //User Login Api -POST
    public function login(Request $request)
    {
        //validation
       $request->validate([
          "email" => "required|email",
          "password" => "required"
       ]);

       // verify user +token

        if(!$token = auth()->attempt(["email" => $request->email, "password"  => $request->password]))
        {
            return response()->json([
                   "message" => "Invalid credentials"
            ],401);
        }

        //return response

        return response()->json([

            "message" => "Login Successful",
            "access_token" => $token
        ]);


    }

    //User Profile Api -GET
    public function profile()
    {
        $user_data = auth()->user();

        return response()->json([
            "message" => "User Profile data",
            "data" => $user_data
        ]);
    }

    //User Logout Api -GET
    public function logout()
    {
       auth()->logout();

       return response()->json([
           "message" => "User logged out"
       ]);
    }




    public function updateProfile(Request $request)
    {
        $user = User::find(auth()->user()->id);
        if(is_null($user)){
            return response()->json(["message" => "Record Not Found!"],404);
        }


        $rules = [
            'name' => 'max:255|min:3',
            'email' => 'email',
            'gender' => 'max:6|min:4',
            'imagePath' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'birthDate' => 'date',
            'area' => 'max:20|min:3',
            'bankName' => 'max:20|min:2',
            'rocket' => 'max:20|min:2',
            'bkash' => 'max:20|min:2',
            'nogod' => 'max:20|min:2',
            'role' => 'max:10|min:4',

        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        if($request->file('imagePath')) {

            if($user->imagePath) {
                $path = public_path() . "/Gallery/" . $user->imagePath;
                unlink($path);
            }
            $file = $request->file('imagePath');
            $filename = time().'.'.$file->extension();
            $file->move(public_path('Gallery'),$filename);
            $user->imagePath= $filename;
        }
        $user->update($request->except('imagePath'));
        return response()->json([
            "message" => "Updated Successfully"
        ],201);
    }

}
