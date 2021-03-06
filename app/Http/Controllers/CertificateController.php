<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Validator;
use File;


class CertificateController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCertificates(Request $request)
    {
        $userId = $request->user_id;


        $certificates = Certificate::where('user_id', $userId)->get();
        if ($certificates->isEmpty()) {
            return response()->json(["message" => "This User Doesn't have any Certificates"], 404);
        }

        return response()->json(
            [
                "message" => 'success',
                "list" => $certificates
            ],
            200
        );
    }






    public function postCertificate(Request $request)
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
                'file_id' => 'required|mimes:pdf|max:3048',
                'user_id' => 'required|exists:users,id',
            ]
        );
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 422);
        }
        $certificate = new Certificate();
        if ($request->file('file_id')) {
            $file = $request->file('file_id');
            $filename =time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('Certificates'), $filename);
            $certificate->file_id = env('APP_URL') . "/Certificates/" . $filename;
        }

        $certificate->name = $request->name;
        $certificate->user_id = $userId;
        $certificate->save();
        return response()->json(
            [
                "message" => "Success",
                "certificate" => $certificate->makeHidden(['user_id'])
            ],
            201
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCertificate(Request $request, $certificateId)
    {
        $certificate = Certificate::find($certificateId);
        if (is_null($certificate)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        $rules = [
            'name' => 'max:255|',
            // 'file_id' => 'mimes:doc,docx,pdf,txt|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->all()[0];
            return response()->json(["message" => $error], 422);
        }


        if ($request->file('file_id')) {
            if (isset($certificate['file_id'])) {
                $suffix = substr($certificate->file_id, strpos($certificate->file_id, env('APP_URL')) + strlen(env('APP_URL'))+1);
                $path = public_path() . "/" . $suffix;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
            
            $file = $request->file('file_id');
            $filename =time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('Certificates'), $filename);
            $certificate->file_id = env('APP_URL') . "/Certificates/" . $filename;

        }
        $certificate->name = is_null($request->name) ? $certificate->name : $request->name;
        $certificate->save();
        return response()->json([
            "message" => 'Updated Successfully',
            "certificate" => $certificate,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteCertificate(Request $request, $certificateId)
    {
        $certificate = Certificate::find($certificateId);

        if (is_null($certificate)) {
            return response()->json(["message" => "Record Not Found!"], 404);
        }
        if (isset($certificate['file_id'])) {
            $suffix = substr($certificate->file_id, strpos($certificate->file_id, env('APP_URL')) + strlen(env('APP_URL'))+1);
            $path = public_path() . "/" . $suffix;
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $certificate->delete();
        return response()->json([
            "messsage" => "Deleted successfully"
        ], 204);
    }
}
