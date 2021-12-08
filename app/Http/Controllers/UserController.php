<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lead;
use App\Models\LinkedDealer;
use App\Models\LinkedSubpainter;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            "token" => $token,
            "id" => $user->id,
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
            'phone' => 'min:8',
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
            'phone' => $request->phone,
        ];


        $validator = Validator::make(
            $r,
            [
                // 'name' => 'required|max:255|min:3',
                // 'area' => 'required',
                'phone' => 'required|max:255|min:8|exists:users,phone',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }

        $subpainter = User::where('phone', $request->phone)->first();

        // if (is_null($subpainter)) {
        //     $subpainter = new User();
        //     $subpainter->name = $request->name;
        //     $subpainter->area = $request->area;
        //     $subpainter->phone = $request->phone;
        //     $subpainter->save();
        // }

        $link = LinkedSubpainter::where('subpainter', $subpainter->id)->first();
        if (is_null($link)) {
            $link = new LinkedSubpainter();
            $link->painter = $userId;
            $link->subpainter = $subpainter->id;
            $link->save();
        }



        return response()->json(
            [
                "message" => "Success",
                "subpainter" => $subpainter
            ],
            201
        );
    }



    public function updateSubpainter(Request $request, $subpainterId)
    {
        $userId = $request->user_id;
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
    public function getSubpainters(Request $request)
    {
        $userId = $request->user_id;
        // $link = LinkedSubpainter::where('painter', $userId)->all();
        $subpainters = DB::table('linked_subpainters')->where('painter', '=', $userId)
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'linked_subpainters.subpainter');
            })
            ->get();

        if ($subpainters->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Subpainter"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "list" => $subpainters
            ],
            200
        );
    }

    public function deleteSubpainter(Request $request, $subpainterId)
    {
        $userId = $request->user_id;
        $link = LinkedSubpainter::where('subpainter', $subpainterId)->where('painter', $userId)->first();

        if (is_null($link)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        $link = LinkedSubpainter::where('subpainter', $subpainterId)->where('painter', $userId)->delete();

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
            'area' => $userId,
            'phone' => $request->phone,
            'email' => $request->email,
        ];


        $validator = Validator::make(
            $r,
            [
                'name' => 'required|max:255|min:3',
                'area' => 'required',
                'phone' => 'required|max:255|min:8',
                'email' => 'email',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }

        $dealer = User::where('phone', $request->phone)->first();

        if (is_null($dealer)) {
            $dealer = new User();
            $dealer->name = $request->name;
            $dealer->area = $request->area;
            $dealer->phone = $request->phone;
            $dealer->email = $request->email;
            $dealer->save();
        }


        $link = LinkedDealer::where('dealer', $dealer->id)->first();
        if (is_null($link)) {
            $link = new LinkedDealer();
            $link->painter = $userId;
            $link->dealer = $dealer->id;
            $link->save();
        }

        return response()->json(
            [
                "message" => "Success",
                "dealer" => $dealer
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
            'area' => 'max:255|',
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
    public function getDealers(Request $request)
    {
        $userId = $request->user_id;

        $dealers = DB::table('linked_dealers')->where('painter', '=', $userId)
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'linked_dealers.dealer');
            })
            ->get();

        if ($dealers->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Subpainter"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "list" => $dealers
            ],
            200
        );
    }

    public function deleteDealer(Request $request, $dealerId)
    {
        $userId = $request->user_id;
        $link = LinkedDealer::where('dealer', $dealerId)->where('painter', $userId)->first();

        if (is_null($link)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        $link = LinkedDealer::where('dealer', $dealerId)->where('painter', $userId)->delete();

        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
    }





    //Lead Starts

    public function postLead(Request $request)
    {
        $userId = $request->user_id;

        $r = [
            'area' => $request->area,
            'running_leads' => $request->running_leads,
            'phone' => $request->phone,
            'user_id' => $userId,
        ];
        $validator = Validator::make(
            $r,
            [
                'area' => 'required|max:255|min:3',
                'running_leads' => 'required',
                'phone' => 'required|min:8',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }


        $lead = new Lead();

        $lead->area = $request->area;
        $lead->phone = $request->phone;
        $lead->running_leads = $request->running_leads;
        $lead->user_id = $request->user_id;
        $lead->save();
        return response()->json([
            "message" => "Success",
            "lead" => $lead
        ], 201);
    }





    public function getLeads(Request $request)
    {
        $userId = $request->user_id;


        $lead = Lead::where('user_id', $userId)->get();
        if ($lead->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Leads"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "list" => $lead
            ],
            200
        );
    }



    public function updateLead(Request $request, $leadId)
    {
        $lead = Lead::find($leadId);
        if (is_null($lead)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'running_leads' => 'max:255|min:3required',
            'phone' => 'min:8',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }



        $lead->area = is_null($request->area) ? $lead->area : $request->area;
        $lead->running_leads = is_null($request->running_leads) ? $lead->running_leads : $request->running_leads;
        $lead->phone = is_null($request->phone) ? $lead->phone : $request->phone;

        $lead->save();

        return response()->json([
            "message" => 'Updated Successfully',
            "Lead" => $lead,
        ], 204);
    }

    public function deleteLead(Request $request, $leadId)
    {
        $lead = Lead::find($leadId);

        if (is_null($lead)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        $lead->delete();
        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
    }
}
