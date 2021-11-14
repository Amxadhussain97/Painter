<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eptool extends Model
{
    use HasFactory;
    protected $fillable = ['name' ,'image_id','model','amount'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function epphotos()
    {
        return $this->hasMany(Epphoto::class);
    }
}
