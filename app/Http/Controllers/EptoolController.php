<?php

namespace App\Http\Controllers;

use App\Models\Epphoto;
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
            $tool_query->whereHas('epcategory', function ($query) use ($request) {
                $query->where('name', $request->category);
            });
        }
        if ($request->name) {
            $tool_query->where('name', $request->name);
        }
        $tools = $tool_query->get();
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

        $userId = $request->user_id;

        $r = [
            'name' => $request->name,
            'user_id' => $userId,
            'model' => $request->model,
            'description' => $request->description,
        ];


        $validator = Validator::make(
            $r,
            [
                'name' => 'required|max:255|min:3',
                'description' => 'max:255',
                'model' => 'max:255',
                'user_id' => 'required|exists:users,id',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        $eptool = new Eptool();


        $eptool->name = $request->name;
        $eptool->model = $request->model;
        $eptool->description = $request->description;
        $eptool->user_id = $userId;
        $eptool->save();
        return response()->json(
            [
                "message" => "Success",
                "eptools" => $eptool->makeHidden(['user_id'])
            ],
            201
        );
    }


    public function updateEptool(Request $request, $eptoolId)
    {
        $userId = $request->user_id;
        $eptool = Eptool::find($eptoolId);
        if (is_null($eptool)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'max:255',
            'description' => 'max:255',
            'model' => 'max:255',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }


        // if ($request->file('image_id')) {
        //     if (isset($eptool['image_id'])) {
        //         $path = public_path() . "/" . $eptool->image_id;
        //         unlink($path);
        //     }
        //     $file = $request->file('image_id');
        //     $filename = time() . '.' . $file->extension();
        //     $file->move(public_path('Eptools'), $filename);
        //     $eptool->image_id = 'Eptools/' . $filename;
        // }
        $eptool->name = is_null($request->name) ? $eptool->name : $request->name;
        $eptool->model = is_null($request->model) ? $eptool->model : $request->model;
        $eptool->description = is_null($request->description) ? $eptool->description : $request->description;
        $eptool->save();
        return response()->json([
            "message" => 'Updated Successfully',
        ], 201);
    }

    public function getEptools(Request $request)
    {

        $userId = $request->user_id;


        $eptools = Eptool::select(['id', 'name', 'description', 'model'])->where('user_id', $userId)->get();
        if ($eptools->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Eptools"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "eptools" => $eptools
            ],
            200
        );
    }


    public function deleteEptool(Request $request, $eptoolId)
    {
        $eptool = Eptool::find($eptoolId);

        if (is_null($eptool)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $photo_query = Epphoto::where('eptool_id', $eptoolId);

        $photos = $photo_query->get();
        foreach ($photos as $photo) {
            if (isset($photo['image_id'])) {
                $path = public_path() . "/" . $photo->image_id;
                unlink($path);
            }
        }
        $photo_query->delete();
        $eptool->delete();
        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
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

    //EpToolsPhoto

    public function postEpphoto(Request $request, $eptoolId)
    {


        $r = [
            'image_id' => $request->image_id,
            'eptool_id' => $eptoolId,
        ];
        $validator = Validator::make(
            $r,
            [
                'image_id.*' => 'mimes:jpeg,jpg,png,gif,csv,txt,pdf',
                'eptool_id' => 'required|exists:eptools,id',

            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }


        foreach ($request->file('image_id') as $file) {
            $epphoto = new Epphoto();
            $filename =  time() . $file->getClientOriginalName();
            $file->move(public_path('EpPhotos'), $filename);
            $epphoto->image_id = 'EpPhotos/' . $filename;
            $epphoto->eptool_id = $eptoolId;
            $epphoto->save();
        }

        $list = Epphoto::where('eptool_id', $eptoolId)->get();

        return response()->json([
            "message" => "Success",
            "list" => $list,

        ], 201);
    }

    public function getEpphotos(Request $request, $eptoolId)
    {

        $photos  = Epphoto::where('eptool_id', $eptoolId)->get();
        if ($photos->isEmpty()) {
            return response()->json(["message" => "No photo available"], 404);
        }
        return response()->json(
            [
                'message' => 'success',
                'photos' => $photos
            ],
            200
        );
    }

    public function updateEpphoto(Request $request, $eptoolId, $EpphotoId)
    {

        $photo = Epphoto::find($EpphotoId);

        if (is_null($photo)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        $rules = [
            'image_id' => 'image|mimes:jpeg,png,jpg,gif,svg',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        if (isset($photo['image_id'])) {
            $path = public_path() . "/" . $photo->image_id;
            unlink($path);
        }
        $file = $request->file('image_id');
        $filename =  time() . $file->getClientOriginalName();
        $file->move(public_path('EpPhotos'), $filename);
        $photo->image_id = 'EpPhotos/' . $filename;
        $photo->save();
        // return response()->json([
        //     "message" => "Updated Successfully"
        // ], 201);
        return response()->json([
            "message" => "success"
        ], 201);
    }

    public function deleteEpphoto($eptoolId, $EpphotoId)
    {
        $photo = Epphoto::find($EpphotoId);

        if (is_null($photo)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }

        if (isset($photo['image_id'])) {
            $path = public_path() . "/" . $photo->image_id;
            unlink($path);
        }
        $photo->delete();
        return response()->json(["messege" => "Deleted successfully"], 204);
    }
}
