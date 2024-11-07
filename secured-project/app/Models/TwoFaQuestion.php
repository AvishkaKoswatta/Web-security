<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwoFaQuestion extends Model
{
    protected $fillable = [
        'user_id' ,
'question'  ,
'answer' ,
    ];
}
