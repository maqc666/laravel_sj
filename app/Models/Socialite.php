<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socialite extends Model
{
    protected $table='socialite';
    protected $fillable=[
        'user_id','app','app_user_id','data',
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }
}
