<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use File;
$baseurl = env('APP_URL');

class PhotosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $photo  = Photo::where('gallery_id',$id);
        $photos = $photo->get();
        if($photos ->isEmpty()){
            return response()->json(["message" => "No photo available"],404);
        }


        return response()->json( $photos,
            200);

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




    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'gallery_id' => 'required|exists:galleries,id',
                'name' => 'image|max:2048',
            ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 422);
        }
        $photo = new Photo();
        if($request->file('name'))
        {
            $file = $request->file('name');
            $filename = time().'.'.$file->extension();
            $file->move(public_path('Gallery'),$filename);
            $photo->name= $filename;
        }
        $photo->gallery_id = $request->gallery_id;
        $photo->save();
        return response()->json([
            "message" => "Success",
            "Photo"=> $photo
        ],201);


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $photo = Photo::find($id);
        if(is_null($photo)){
            return response()->json(["message" => "Record Not Found!"],404);
        }
        $rules = [
            'name' => 'image|max:2048',
        ];

        $validator = Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }
        if(isset($photo['name']))
        {
            if(file_exists(public_path()."/Gallery/".$photo->name)){
                //delete file
                unlink(public_path()."/Gallery/".$photo->name);
            }
        }
        $file = $request->file('name');
        $filename = time().'.'.$file->extension();
        $file->move(public_path('Gallery'),$filename);
        $photo->name = $filename;
        $photo->save();
        return response()->json([
            "message" => "Updated Successfully"
        ],201);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $photo= Photo::find($id);
        if(is_null($photo)){
            return response()->json(["message" => "Record Not Found!"],404);
        }
        if(isset($photos['name']))
        {
            //check if file exists
            if(file_exists(public_path()."/Gallery/".$photo->name)){
                //delete file
                unlink(public_path()."/Gallery/".$photo->name);
            }
        }
        $photo->delete();
        return response()->json(["messege" =>"Deleted"],200);
    }
}
