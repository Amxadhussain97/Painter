<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Epphoto extends Model
{
    use HasFactory;
    public function eptools()
    {
        return $this->belongsTo(Eptool::class);
    }
}
