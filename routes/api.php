<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\CertificateController;
use \App\Http\Controllers\InsuranceController;
use \App\Http\Controllers\GalleryController;
use \App\Http\Controllers\PhotosController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\EptoolController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::get('/test/otp-request', [UserController::class,'requestForOtp']);

Route::post("register",[UserController::class,'register']);
Route::post("login",[UserController::class,'login']);

Route::group(["middleware" => ["auth:api"]],function (){

    Route::get("profile",[UserController::class,'profile']);
    Route::get("logout",[UserController::class,'logout']);
    Route::post("profile",[UserController::class,'updateProfile']);

    Route::post('eptools',[EptoolController::class,'postEptool']); // TO POST AN EPTOOL
    Route::get('eptools',[EptoolController::class,'getEptool']); // TO GET EPTOOL OF AN USER
    Route::post('eptools/{Eptool::id}',[EptoolController::class,'updateEptool']); // TO UPDATE AN USER'S EPTOOL
    Route::delete('eptools/{Eptool::id}',[EptoolController::class,'deleteEptool']); // TO DELETE AN USER'S EPTOOL


    Route::post('certificates',[CertificateController::class,'postCertificate']); // TO POST A CERTIFICATE
    Route::get('certificates',[CertificateController::class,'getCertificate']); // TO GET CERTIFICATES OF AN USER
    Route::post('certificates/{Certificate::id}',[CertificateController::class,'updateCertificate']); // TO UPDATE AN USER'S CERTIFICATE
    Route::delete('certificates/{Certificate::id}',[CertificateController::class,'deleteCertificate']); // TO DELETE AN USER'S CERTIFICATE


    Route::post('insurances',[InsuranceController::class,'postInsurance']); // TO POST AN INSURANCE
    Route::get('insurances',[InsuranceController::class,'getInsurances']); // TO GET INSURANCES OF AN USER
    Route::post('insurances/{Insurance::id}',[InsuranceController::class,'updateInsurance']); // TO UPDATE AN USER'S INSURANCE
    Route::delete('insurances/{Insurance::id}',[InsuranceController::class,'deleteInsurance']);  // TO DELETE AN USER'S INSURANCE


    Route::post('galleries',[GalleryController::class,'postGallery']); // TO POST A GALLERY
    Route::get('galleries',[GalleryController::class,'getGalleries']); // TO GET GALLERIES OF AN USER
    Route::post('galleries/{Gallery::id}',[GalleryController::class,'updateGallery']); // TO UPDATE AN USER'S GALLERY
    Route::delete('galleries/{Gallery::id}',[GalleryController::class,'deleteGallery']); // TO DELETE AN USER'S GALLERY


    Route::post('galleries/{Gallery::id}/photos',[GalleryController::class,'postUserPhoto']); // TO POST A PHOTO
    Route::get('galleries/{Gallery::id}/photos',[GalleryController::class,'getUserPhotos']); // TO GET PHOTOS OF A GALLERY
    Route::post('galleries/{Gallery::id}/photos/{Photo::id}',[GalleryController::class,'updateUserPhoto']); // TO UPDATE A GALLERY PHOTO
    Route::delete('galleries/{Gallery::id}/photos/{Photo::id}',[GalleryController::class,'deleteUserPhoto']); // TO DELETE A GALLERY PHOTO




});



//Route::post('users',[UserController::class,'store']);
//Route::get('users',[UserController::class,'show']);
//Route::get('users/{User::id}',[UserController::class,'index']);
//Route::post('users/{User::id}',[UserController::class,'update']);
//Route::delete('users/{User::id}',[UserController::class,'destroy']);









//Route::post('photos',[PhotosController::class,'store']);
//Route::get('photos/{Gallery::id}',[PhotosController::class,'index']);
//Route::post('photos/{Photo::id}',[PhotosController::class,'update']);
//Route::delete('photos/{Photo::id}',[PhotosController::class,'destroy']);

//
//Route::post('galleries',[GalleryController::class,'store']);
//Route::get('galleries/{User::id}',[GalleryController::class,'index']);
//Route::post('galleries/{Gallery::id}',[GalleryController::class,'update']);
//Route::delete('galleries/{Gallery::id}',[GalleryController::class,'destroy']);
