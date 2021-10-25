<?php

namespace App\Http\Controllers;

use App\Models\Eptool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class EptoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($request)
    {
        $tool_query = Eptool::with('epcategory');

        if ($request->category) {
            //$tool_query->where('epcategory_id',$request->category);
            $tool_query->whereHas('epcategory', function ($query) use ($request) {
                $query->where('name', $request->category);
            });
        }
        if ($request->name) {
            $tool_query->where('name', $request->name);
        }
        $tools = $tool_query->get();
        // dd($tools);
        return response()->json(
            $tools,
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function postEptool(Request $request)
    {
        $userId = auth()->user()->id;

        $r = [
            'name' => $request->name,
            'image_id' => $request->image_id,
            'user_id' => $userId,
            'model' => $request->model,
            'amount' => $request->amount,
        ];


        $validator = Validator::make(
            $r,
            [
                'name' => 'required|max:255|min:3',
                'amount' => 'max:255|min:3',
                'model' => 'max:255|min:3',
                'image_id' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'user_id' => 'required|exists:users,id',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $eptool = new Eptool();
        if ($request->file('image_id')) {
            $file = $request->file('image_id');
            $filename = time() . '.' . $file->extension();
            $file->move(public_path('Eptools'), $filename);
            $eptool->image_id = $filename;
        }

        $eptool->name = $request->name;
        $eptool->model = $request->model;
        $eptool->amount = $request->amount;
        $eptool->user_id = $userId;
        $eptool->save();
        return response()->json(
            [
                "message" => "Success",
                "eptools" => $eptool->name
            ],
            201
        );
    }


    public function updateEptool(Request $request, $eptoolId)
    {
        $userId = auth()->user()->id;
        $eptool = Eptool::find($eptoolId);
        if (is_null($eptool) || $eptool->user_id != $userId) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'max:255|min:3',
            'amount' => 'max:255|min:3',
            'model' => 'max:255|min:3',
            'image_id' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        if ($request->file('image_id')) {
            if (isset($eptool['image_id'])) {
                $path = public_path() . "/Eptools/" . $eptool->image_id;
                unlink($path);
            }
            $file = $request->file('image_id');
            $filename = time() . '.' . $file->extension();
            $file->move(public_path('Eptools'), $filename);
            $eptool->image_id = $filename;
        }
        $eptool->name = is_null($request->name) ? $eptool->name : $request->name;
        $eptool->model = is_null($request->model) ? $eptool->model : $request->model;
        $eptool->amount = is_null($request->amount) ? $eptool->amount : $request->amount;
        $eptool->save();
        return response()->json([
            "message" => 'Updated Successfully',
        ], 200);
    }

    public function getEptool()
    {
        $userId = auth()->user()->id;


        $eptools = Eptool::select(['name', 'image_id', 'amount', 'model'])->where('user_id', $userId)->get();
        if ($eptools->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Insurences"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "eptools " => $eptools
            ],
            200
        );
    }


    public function deleteEptool($eptoolId)
    {
        $userId = auth()->user()->id;
        $eptool = Eptool::find($eptoolId);

        if (is_null($eptool) || $eptool->user_id != $userId) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        if (isset($eptoole['image_id'])) {
            $path = public_path() . "/Eptools/" . $eptool->file_id;
            unlink($path);
        }
        $eptool->delete();
        return response()->json([
            "messsage" => "Deleted successfully"
        ], 201);
    }



    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255|min:3|unique:eptools,name',
            'image_id' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'epcategory_id' => 'required|exists:epcategories,id'

        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }
        //        if($image = $request->file('image_id')) {
        //            dd($image);
        //            $request->image_id = $request->file->store('public/images');//it will be the path of photo as string
        //
        //        }
        $tool = Eptool::create($request->all());
        $tool->save();
        return response()->json(Eptool::with('epcategory')->get(), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tool = Eptool::find($id);
        if (is_null($tool)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        return response()->json($tool, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $tool = Eptool::find($id);
        if (is_null($tool)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'required|max:255|min:3|unique:eptools,name',
            'image_id' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'epcategory_id' => 'required|exists:epcategories,id'

        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $tool->update($request->all());
        return response()->json($tool, 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tool = Eptool::find($id);
        if (is_null($tool)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $tool->delete();
        return response()->json(null, 204);
    }
}
