<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table='templates';
    protected $fillable=[
        'name','current_version','path','real_path','author','thumb',
    ];

}
