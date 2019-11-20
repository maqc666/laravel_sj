<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nav extends Model
{
    protected $tabel='navs';

    protected $fillable=[
        'sort','name','url',
    ];
    public function allCache(){
        if(config('meedu.system.cache.status',-1)!=1){
            return $this->orderBy('sort')->get();
        }
        $that=$this;

        return Cache::remember(
            'navs',config('meedu.system.cache.expire',60),
            function ()use ($that){
                return $this->orderBy('sort')->get();
            }
        );
    }
}
