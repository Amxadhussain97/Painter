<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\User;
use App\Models\Lead;
use App\Models\LinkedDealer;
use App\Models\LinkedSubdealer;
use App\Models\LinkedSubpainter;
use App\Models\LinkedUser;
use App\Models\Subdistrict;
use App\Models\Subuser;
use Exception;
use GuzzleHttp\Psr7\Message as Psr7Message;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Nette\Schema\Message as SchemaMessage;
use Illuminate\Auth\Events\PasswordReset;
use Symfony\Component\Mime\Message as MimeMessage;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Support\Str;



class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }

    public function showResetPasswordForm($token)
    {
        return view('auth.forgetPasswordLink', ['token' => $token])->with('message', 'Your password has been changed!');;
    }

    public function submitResetPasswordForm(Request $request)
    {


        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', RulesPassword::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );
        if ($status == Password::PASSWORD_RESET) {
            return back()->withInput()->with('message', 'Password reset successfully');
        }

        // return response([
        //     'message'=> __($status)
        // ], 500);
        return back()->withInput()->with('other', __($status));






        //  $request->validate([
        //      'email' => 'required|email|exists:users',
        //      'password' => 'required|string|min:6|confirmed',
        //      'password_confirmation' => 'required'
        //  ]);

        //  $updatePassword = DB::table('password_resets')
        //                      ->where([
        //                        'email' => $request->email,
        //                        'token' => $request->token
        //                      ])
        //                      ->first();

        //  if(!$updatePassword){
        return back()->withInput()->with('message', 'Invalid token!');
        //  }

        //  $user = User::where('email', $request->email)
        //              ->update(['password' => Hash::make($request->password)]);

        //  DB::table('password_resets')->where(['email'=> $request->email])->delete();

        //  return redirect('/login')->with('message', 'Your password has been changed!');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', RulesPassword::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response([
                'message' => 'Password reset successfully'
            ]);
        }

        return response([
            'message' => __($status)
        ], 500);
    }

    public function forgot(Request $request)
    {
        // $credentials = request()->validate(['email' => 'required|email']);
        // $response = Password::sendResetLink($credentials, function (Message $message) {
        //     $message->subject($this->getEmailSubject());
        // });

        // switch ($response) {
        //     case Password::RESET_LINK_SENT:
        //         return response()->json([
        //             'status'        => 'success',
        //             'message' => 'Password reset link send into mail.',
        //             'data' =>''], 201);
        //     case Password::INVALID_USER:
        //         return response()->json([
        //             'status'        => 'failed',
        //             'message' =>   'Unable to send password reset link.'
        //         ], 401);
        // }

        $credentials = request()->validate(['email' => 'required|email']);

        Password::sendResetLink($credentials);

        return response()->json(["msg" => 'Reset password link sent on your email id.']);
    }

    //User Register Api -POST
    public function register(Request $request)
    {


        $rules = [
            'name' => 'required|min:2',
            'phone' => 'required|min:5',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }

        $user = User::where('phone', $request->phone)->first();

        if (is_null($user)) {
            $user = new User();
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            if ($request->role) $user->role = $request->role;
            $user->password = bcrypt($request->password);
            $user->save();
        } else if ($user->password != null) {
            return response()->json(["message" => "user already registered"], 401);
        } else {
            $user = User::where('phone', $request->phone)->first();
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            if ($request->role) $user->role = $request->role;
            $user->password = bcrypt($request->password);
            $user->save();
        }



        $token = auth()->attempt(["email" => $request->email, "password"  => $request->password]);
        return response()->json([
            "message" => "success",
            "token" => $token,
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
        // ->leftjoin('subdistricts', function ($join) {
        //     $join->on('users.subdistrict_id', '=', 'subdistricts.id');
        // })->select('users.*','district_id','subdistrict')
        // ->leftjoin('districts', function ($join) {
        //     $join->on('subdistricts.district_id', '=', 'districts.id');
        // })->select('users.*','subdistrict','district')


        $user = User::where('users.id',auth()->user()->id)->leftjoin('subdistricts', 'users.subdistrict_id', '=', 'subdistricts.id')
        ->select('subdistrict','district')
        ->leftjoin('districts', 'subdistricts.district_id', '=', 'districts.id')
        ->select('users.*','subdistrict','district')
        ->get();

    //     $filtered = $user->filter(function ($value, $key)  {
    //         return $value['id'] == auth()->user()->id;
    //    });

        return response()->json([
            "message" => "User Profile data",
            "data" => $user
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
            'imagePath' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2050',
            'phone' => 'min:5',
            'area' => 'max:20|',
            'bankName' => 'max:20|',
            'rocket' => 'max:20|',
            'bkash' => 'max:20|',
            'nogod' => 'max:20|',
            'role' => 'max:10|',

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
            $filename = $file->getClientOriginalName();
            $file->move(public_path('Photos'), $filename);
            $user->imagePath = "Photos/" . $filename;
        }
        $user->update($request->except(['imagePath', 'id', 'district', 'subdistrict']));
        if ($request->district) {

            $district = District::where('district', $request->district)->first();
            if (is_null($district)) {
                $district = new District();
                $district->district = $request->district;
                $district->save();
            }
            $subdistrict = Subdistrict::where('district_id', $district->id)->where('subdistrict', $request->subdistrict)->first();
            if (is_null($subdistrict)) {
                $subdistrict = new Subdistrict();
                $subdistrict->subdistrict = $request->subdistrict;
                $subdistrict->district_id = $district->id;
                $subdistrict->save();
            }
            $user->subdistrict_id = $subdistrict->id;
            $user->save();
        }

        return response()->json([
            "message" => "Updated Successfully"
        ], 204);
    }


    //SubPainter Starts



    public function postSubuser(Request $request)
    {
        $userId = $request->user_id;
        $user = User::where('id', $userId)->firstorfail();
        $rules = [
            'phone' => 'required|max:255|min:5|exists:users,phone',
            'link' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        } else if ($user->phone == $request->phone) {
            return response()->json(["message" => "Provided phone number can't be matched with your own phone number"], 401);
        } else {

            $subuser = User::where('phone', $request->phone)->first();
            $subuser->role = strtolower($subuser->role);
            $request->link = strtolower($request->link);
            if ($subuser->role != $request->link) {
                return response()->json(["message" => "This user doesn't have your desired role"], 401);
            }
            // if (is_null($subpainter)) {
            //     $subpainter = new User();
            //     $subpainter->name = $request->name;
            //     $subpainter->area = $request->area;
            //     $subpainter->phone = $request->phone;
            //     $subpainter->save();
            // }

            $link = Subuser::where('subuser', $subuser->id)->where('user', $userId)->first();
            if (is_null($link)) {
                $link = new Subuser();
                $link->user = $userId;
                $link->subuser = $subuser->id;
                $link->link = $request->link;
                $link->save();
            }




            return response()->json(
                [
                    "message" => "Success",
                    "subuser" => $subuser
                ],
                201
            );
        }
    }



    public function updateSubuser(Request $request, $subpainterId)
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
    public function getSubusers(Request $request)
    {
        $userId = $request->user_id;
        // $link = LinkedSubpainter::where('painter', $userId)->all();
        $subusers = DB::table('subusers')->where('user', '=', $userId)
            ->join('users', function ($join) {
                $join->on('users.id', '=', 'subusers.subuser');
            })
            ->leftjoin('subdistricts', function ($join) {
                $join->on('users.subdistrict_id', '=', 'subdistricts.id');
            })->leftjoin('districts', function ($join) {
                $join->on('subdistricts.district_id', '=', 'districts.id');
            })
            ->get();


        if ($subusers->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Subpainter"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "list" => $subusers
            ],
            200
        );
    }

    public function deleteSubuser(Request $request, $subuserId)
    {
        $userId = $request->user_id;
        //dd($userId,$subpainterId);
        $link = Subuser::where('subuser', $subuserId)->where('user', $userId)->first();
        if (is_null($link)) {
            return response()->json([
                "message" => "Record Not Found!"


            ], 404);
        }

        $link = Subuser::where('subuser', $subuserId)->where('user', $userId)->delete();

        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
    }

    //Subpainter Ends

    //Dealer Starts

    public function postLinkeduser(Request $request)
    {
        $userId = $request->user_id;
        $user = User::where('id', $userId)->firstorfail();
        $rules = [
            'name' => 'max:255|min:3',
            'area' => 'max:255|min:3',
            'phone' => 'required|max:255|min:5',
            'email' => 'email|unique:users',
            'link' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        } else if ($user->phone == $request->phone) {
            return response()->json(["message" => "Provided phone number can't be matched with your own phone number"], 401);
        } else {

            $linkeduser = new User();
            $linkeduser->name = $request->name;
            $linkeduser->area = $request->area;
            $linkeduser->phone = $request->phone;
            $linkeduser->email = $request->email;

            if ($request->district) {
                $district = District::where('district', $request->district)->first();
                if (is_null($district)) {
                    $district = new District();
                    $district->district = $request->district;
                    $district->save();
                }
                $subdistrict = Subdistrict::where('district_id', $district->id)->where('subdistrict', $request->subdistrict)->first();
                if (is_null($subdistrict)) {
                    $subdistrict = new Subdistrict();
                    $subdistrict->subdistrict = $request->subdistrict;
                    $subdistrict->district_id = $district->id;
                    $subdistrict->save();
                }
                $linkeduser->subdistrict_id = $subdistrict->id;
            }
            $linkeduser->save();


            $link = LinkedUser::where('linkeduser', $linkeduser->id)->where('user', $userId)->first();
            if (is_null($link)) {
                $link = new LinkedUser();
                $link->user = $userId;
                $link->linkeduser = $linkeduser->id;
                $link->link = strtolower($request->link);
                $link->save();
            }


            return response()->json(
                [
                    "message" => "Success",
                    "linkeduser" => $linkeduser
                ],
                201
            );
        }
    }




    public function updateLinkeduser(Request $request, $dealerId)
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
    public function getLinkedusers(Request $request)
    {
        $userId = $request->user_id;

        $linkedusers = DB::table('linked_users')->where('user', '=', $userId)
            ->join('users', function ($join) {
                $join->on('linked_users.linkeduser', '=', 'users.id');
            })
            ->leftjoin('subdistricts', function ($join) {
                $join->on('users.subdistrict_id', '=', 'subdistricts.id');
            })->leftjoin('districts', function ($join) {
                $join->on('subdistricts.district_id', '=', 'districts.id');
            })
            ->get();

        if ($linkedusers->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Linked Users"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "list" => $linkedusers
            ],
            200
        );
    }

    public function checkLinkeduser(Request $request)
    {
        $userId = $request->user_id;
        $user = User::where('id', $userId)->firstorfail();
        $rules = [
            'phone' => 'required|max:255|min:5|exists:users,phone',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        } else if ($user->phone == $request->phone) {
            return response()->json(["message" => "Provided phone number can't be matched with your own phone number"], 401);
        } else {

            $linkeduser = User::where('phone', $request->phone)->first();
            $linkeduser->role = strtolower($linkeduser->role);
            $request->link = strtolower($request->link);
            if ($linkeduser->role != $request->link) {
                return response()->json(["message" => "This user doesn't have your desired role"], 401);
            }



            $link = LinkedUser::where('linkeduser', $linkeduser->id)->where('user', $userId)->first();
            if (is_null($link)) {
                $link = new LinkedUser();
                $link->user = $userId;
                $link->linkeduser = $linkeduser->id;
                $link->link = $request->link;
                $link->save();
            }

            return response()->json(
                [
                    "message" => "Success",
                    "dealer" => $linkeduser
                ],
                201
            );
        }
    }



    public function deleteLinkeduser(Request $request, $linkeduserId)
    {
        $userId = $request->user_id;
        $link = LinkedUser::where('linkeduser', $linkeduserId)->where('user', $userId)->first();

        if (is_null($link)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        $link = LinkedUser::where('linkeduser', $linkeduserId)->where('user', $userId)->delete();

        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
    }





    //Lead Starts

    public function postLead(Request $request)
    {
        $userId = $request->user_id;

        $r = [
            'number' => $request->number,

        ];
        $validator = Validator::make(
            $r,
            [
                'number' => 'required|max:255|min:1',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }

        $lead = Lead::where('user_id', $userId)->first();
        if ($lead) {
            $lead->number = $request->number;
        } else {
            $lead = new Lead();
            $lead->number = $request->number;
        }
        $lead->user_id = $userId;
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
                "lead" => $lead
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
            'running_leads' => 'max:255|min:3',
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


    public function searchUsers(Request $request)
    {


        if(count($request->all()) == 0)
        {
            return response()->json(["message" => "No query given"], 404);
        
        $users = User::where('role', '<>', 'Admin')->where('users.id', '<>',  auth()->user()->id);


        if (!is_null($request->district)) {
            $district = $request->district;
            $users = $users->whereHas('subdistrict.district', function ($q) use ($district) {
                $q->where('district', '=', $district);
            });
        }



        if (!is_null($request->subdistrict)) {
            $subdistrict = $request->subdistrict;
            $users = $users->whereHas('subdistrict', function ($q) use ($subdistrict) {
                $q->where('subdistrict', '=', $subdistrict);
            });
        }

        if (!is_null($request->type)) {
            $type = $request->type;
            if ($type) {
                $users = $users->where('role', $type);
            }
        }
        if (!is_null($request->q)) {
            $users = $users->where('name', 'like', '%' . $request->q . '%');
        }



        $users = $users->leftjoin('subdistricts', function ($join) {
            $join->on('users.subdistrict_id', '=', 'subdistricts.id');
        })->select('users.*','district_id','subdistrict')
        ->leftjoin('districts', function ($join) {
            $join->on('subdistricts.district_id', '=', 'districts.id');
        })->select('users.*','subdistrict','district');

        $users = $users->get();


        return response()->json(
            [
                "message" => 'success',
                "users" => $users
            ],
            200
        );
    }


    public function getDistrictsSubdistricts(Request $request)
    {
        $districts = District::all();
        $subdistricts = Subdistrict::all();
        return response()->json(
            [
                "message" => 'success',
                "districts" => $districts,
                "subdistricts" => $subdistricts
            ],
            200
        );
    }
    public function getSubDistricts(Request $request, $districtId)
    {



        $subdistricts = Subdistrict::where('district_id', $districtId)->get();
        if ($subdistricts->isEmpty()) {
            return response()->json(["message" => "No subdistrict found"], 404);
        }
        return response()->json(
            [
                "message" => 'success',
                "subdistricts" => $subdistricts
            ],
            200
        );
    }
}
