<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use File;

class GalleryController extends Controller
{

    public function getUserPhotos(Request $request, $galleryId)
    {

        $photos  = Photo::where('gallery_id', $galleryId)->get();
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

    public function getGalleries(Request $request)
    {
        $userId = $request->user_id;
        $galleries  =  Gallery::where('user_id', $userId)->get();
        if ($galleries->isEmpty()) {
            return response()->json(["message" => "No gallery found"], 404);
        }
        return response()->json([
            "message" => "Success",
            "Galleries" => $galleries,
        ], 200);
    }

    public function postUserPhoto(Request $request, $galleryId)
    {
        $gallery = Gallery::find($galleryId);

        $r = [
            'image_id' => $request->image_id,
            'gallery_id' => $galleryId,
        ];
        $validator = Validator::make(
            $r,
            [
                'image_id' => 'image|mimes:jpeg,png,jpg,gif,svg',
                // 'gallery_id' => 'required|exists:galleries,id',

            ]
        );
        if ($validator->fails() || is_null($gallery)) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }

        $photo = new Photo();
        if ($request->file('image_id')) {
            $file = $request->file('image_id');
            $filename = $file->getClientOriginalName();
            $file->move(public_path('Photos'), $filename);
            $photo->image_id = 'Photos/' . $filename;
        }
        $photo->gallery_id = $galleryId;
        $photo->save();
        return response()->json([
            "message" => "Success",
            "photo" => $photo,

        ], 201);
    }

    public function postGallery(Request $request)
    {
        $userId = $request->user_id;
        $r = [
            'name' => $request->name,
            'user_id' => $userId,
        ];
        $validator = Validator::make(
            $r,
            [
                'name' => 'required|max:255|min:3',
                'user_id' => 'required|exists:users,id',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        $gallery = new Gallery();
        $gallery->name = $request->name;
        $gallery->user_id = $userId;
        $gallery->save();

        return response()->json([
            "message" => "Success",
            "Gallery" => $gallery->makeHidden(['user_id'])
        ], 201);
    }



    public function index(Request $request, $id)
    {
        $galleries = Gallery::where('user_id', $id)->get();
        if ($galleries->isEmpty()) {
            return response()->json(["message" => "No content found"], 404);
        }
        $array = [];
        foreach ($galleries as $gallery) {
            array_push($array, $gallery->id);
        }
        $photos_query = Photo::whereIn('gallery_id', $array)->get();
        if ($photos_query->isEmpty()) {
            return response()->json(["message" => "This User doesn't have any photos"], 404);
        }
        return response()->json([
            "message" => "Success",
            "Photo" => $photos_query
        ], 200);
    }





    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:255|min:3',
                'user_id' => 'required|exists:users,id',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $gallery = new Gallery();
        $gallery->name = $request->name;
        $gallery->user_id = $request->user_id;
        $gallery->save();

        return response()->json([
            "message" => "Success",
            "Gallery" => $gallery
        ], 201);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function updateGallery(Request $request, $galleryId)
    {

        $gallery = Gallery::find($galleryId);

        if (is_null($gallery)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'required|max:255'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 401);
        }
        $gallery->name = $request->name;
        $gallery->save();
        return response()->json([
            "message" => "Updated Successfully",
        ], 204);
    }


    public function updateUserPhoto(Request $request, $galleryId, $photoId)
    {
        $gallery = Gallery::find($galleryId);
        $photo = Photo::find($photoId);

        if (is_null($photo) || $photo->gallery_id != $galleryId || is_null($gallery)) {
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
        $filename = $file->getClientOriginalName();
        $file->move(public_path('Photos'), $filename);
        $photo->image_id = 'Photos/' . $filename;
        $photo->save();
        return response()->json([
            "message" => "Updated Successfully"
        ], 204);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteGallery(Request $request, $galleryId)
    {
        $gallery = Gallery::find($galleryId);

        if (is_null($gallery) ) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }


        $photo_query = Photo::where('gallery_id', $galleryId);
        $photos = $photo_query->get();
        foreach ($photos as $photo) {
            if (isset($photo['image_id'])) {
                $path = public_path() . "/" . $photo->image_id;
                unlink($path);
            }
        }

        $photo_query->delete();
        $gallery->delete();
        return response()->json(["message" => "Deleted"], 204);
    }



    public function deleteUserPhoto($galleryId, $photoId)
    {
        $gallery = Gallery::find($galleryId);
        $photo = Photo::find($photoId);

        if (is_null($photo) || $photo->gallery_id != $galleryId || is_null($gallery)) {
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
