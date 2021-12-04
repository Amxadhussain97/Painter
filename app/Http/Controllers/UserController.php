<?php

namespace App\Http\Controllers;

use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;





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
            'password' => 'required|min:5',


        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        $user = new User();
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        $token = auth()->attempt(["email" => $request->email, "password"  => $request->password]);
        return response()->json([
            "message" => "success",
            "token" => $token
        ], 201);
    }

    //User Login Api -POST
    public function login(Request $request)
    {


        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        // verify user +token

        if (!$token = auth()->attempt(["email" => $request->email, "password"  => $request->password])) {
            return response()->json([
                "message" => "Invalid credentials"
            ], 401);
        }

        //return response

        return response()->json([

            "message" => "success",
            "token" => $token
        ], 201);
    }

    //User Profile Api -GET
    public function profile()
    {

        $user_data = auth()->user();

        return response()->json([
            "message" => "User Profile data",
            "data" => $user_data
        ], 200);
    }

    //User Logout Api -GET
    public function logout()
    {
        auth()->logout();

        return response()->json([
            "message" => "User logged out"
        ], 200);
    }




    public function updateProfile(Request $request)
    {
        // return public_path();
        $user = User::find(auth()->user()->id);
        if (is_null($user)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }


        $rules = [
            'name' => 'max:255|min:3',
            'email' => 'email',
            'gender' => 'max:6',
            'imagePath' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'birthDate' => 'date',
            'area' => 'max:20|',
            'bankName' => 'max:20|',
            'rocket' => 'max:20|',
            'bkash' => 'max:20|',
            'nogod' => 'max:20|',
            'role' => 'max:10|',

        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        if ($request->file('imagePath')) {

            if ($user->imagePath) {
                $path = public_path() . "/" . $user->imagePath;
                unlink($path);
            }
            $file = $request->file('imagePath');
            $filename = $file->getClientOriginalName();
            $file->move(public_path('Photos'), $filename);
            $user->imagePath = "Photos/" . $filename;
        }
        $user->update($request->except(['imagePath', 'id']));
        return response()->json([
            "message" => "Updated Successfully"
        ], 204);
    }


    //SubPainter Starts



    public function postSubpainter(Request $request)
    {
        $userId = $request->user_id;

        $r = [
            'name' => $request->name,
            'user_id' => $userId,
            'location' => $request->location,
            'phone' => $request->phone,
            'email' => $request->email,
        ];


        $validator = Validator::make(
            $r,
            [
                'name' => 'required|max:255|min:3',
                'user_id' => 'required|exists:users,id',
                'phone' => 'required|max:255|min:6',
                'email' => 'required|email',
                'location' => 'required',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        $subpainter = new Certificate();


        $subpainter->name = $request->name;
        $subpainter->phone =  $request->phone;
        $subpainter->email =  $request->email;
        $subpainter->location =  $request->location;
        $subpainter->save();
        return response()->json(
            [
                "message" => "Success",
                "subpainter" => $subpainter->makeHidden(['user_id'])
            ],
            201
        );
    }



    public function updateSubpainter(Request $request, $subpainterId)
    {
        $subpainter = Certificate::find($subpainterId);
        if (is_null($subpainter)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'max:255|',
            'phone' => 'max:255|min:6',
            'email' => 'email',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }



        $subpainter->name = is_null($request->name) ? $subpainter->name : $request->name;
        $subpainter->email = is_null($request->email) ? $subpainter->email : $request->email;
        $subpainter->phone = is_null($request->phone) ? $subpainter->phone : $request->phone;
        $subpainter->location = is_null($request->location) ? $subpainter->location : $request->location;

        $subpainter->save();
        return response()->json([
            "message" => 'Updated Successfully',
            "subpainter" => $subpainter,
        ], 204);
    }

    public function deleteSubpainter(Request $request, $subpainterId)
    {
        $subpainter = Certificate::find($subpainterId);

        if (is_null($subpainter)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        $subpainter->delete();
        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
    }

    //Subpainter Ends

    //Dealer Starts

    public function postDealer(Request $request)
    {
        $userId = $request->user_id;

        $r = [
            'name' => $request->name,
            'user_id' => $userId,
            'location' => $request->location,
            'phone' => $request->phone,
            'email' => $request->email,
        ];


        $validator = Validator::make(
            $r,
            [
                'name' => 'required|max:255|min:3',
                'user_id' => 'required|exists:users,id',
                'phone' => 'required|max:255|min:6',
                'email' => 'required|email',
                'location' => 'required',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        $dealer = new Certificate();


        $dealer->name = $request->name;
        $dealer->phone =  $request->phone;
        $dealer->email =  $request->email;
        $dealer->location =  $request->location;
        $dealer->save();
        return response()->json(
            [
                "message" => "Success",
                "dealer" => $dealer->makeHidden(['user_id'])
            ],
            201
        );
    }



    public function updateDealer(Request $request, $dealerId)
    {
        $dealer = Certificate::find($dealerId);
        if (is_null($dealer)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'max:255|',
            'phone' => 'max:255|min:6',
            'email' => 'email',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }



        $dealer->name = is_null($request->name) ? $dealer->name : $request->name;
        $dealer->email = is_null($request->email) ? $dealer->email : $request->email;
        $dealer->phone = is_null($request->phone) ? $dealer->phone : $request->phone;
        $dealer->location = is_null($request->location) ? $dealer->location : $request->location;

        $dealer->save();
        return response()->json([
            "message" => 'Updated Successfully',
            "dealer" => $dealer,
        ], 204);
    }

    public function deleteDealer(Request $request, $dealerId)
    {
        $dealer = Certificate::find($dealerId);

        if (is_null($dealer)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        $dealer->delete();
        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
    }





    //Lead Starts

    public function postLead(Request $request)
    {
        $userId = $request->user_id;

        $r = [
            'name' => $request->name,
            'file_id' => $request->file_id,
            'user_id' => $userId,
        ];

        $validator = Validator::make(
            $r,
            [
                'name' => 'required|max:255|min:3',
                'file_id' => 'required|mimes:doc,docx,pdf,txt|max:2048',
                'user_id' => 'required|exists:users,id',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        $lead = new Certificate();


        $lead->name = $request->name;
        $lead->user_id = $userId;
        $lead->save();
        return response()->json(
            [
                "message" => "Success",
                "lead" => $lead->makeHidden(['user_id'])
            ],
            201
        );
    }



    public function updateLead(Request $request, $leadId)
    {
        $lead = User::find($leadId);
        if (is_null($lead)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'max:255|',
            // 'file_id' => 'mimes:doc,docx,pdf,txt|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }



        $lead->name = is_null($request->name) ? $certificate->name : $request->name;
        $certificate->save();
        return response()->json([
            "message" => 'Updated Successfully',
            "lead" => $lead,
        ], 204);
    }

    public function deleteLead(Request $request, $leadId)
    {
        $lead = Certificate::find($leadId);

        if (is_null($lead)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        $lead->delete();
        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
    }
}
