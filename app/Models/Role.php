<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const IS_SHOW_YES=1;
    const IS_SHOW_NO=0;


    protected $table='role';

    protected $fillable=[
        'name','charge','expire_days','weight','description','is_show',
];

    public function users(){
        return $this->hasMany(User::class,'user_id');
    }

    public function descriptionRows(){
        return $this->hasMany(User::class,'user_id');
    }

    /**
     * 作用域：显示.
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeShow($query){
        return $query->where('is_show',self::IS_SHOW_YES);
    }

    public function statusText(){
        return $this->is_show==self::IS_SHOW_YES?'显示':'不显示';
    }
}

