<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministratorRole extends Model
{

    protected $table='administrator_role';

    protected $fillable=[
        'display_name','slug','description',

    ];
    protected $appends=[
        'edit_url','destroy_url','permission_url',
    ];

    public function administrators(){

        return $this->belongsToMany(
            Administrator::class,
            'administrator_role_relation',
            'role_id',
            'administrator_id'
        );
    }

    public function permissions(){
        return $this->belongsToMany(
            AdministratorPermission::class,
            'administrator_role_permission_relation',
            'role_id',
            'permission_id'
        );
    }
    public function getEditUrlAttribute(){
        return route('backend.administrator_role.edit',$this);
    }

    public function getDestoryUrlAttribute(){
        return route('backend.administrator_role.destroy',$this);
    }
    public function getPermissionUrlAttribute(){
        return route('backend.administrator_role.permission',$this);

    }
    public function hasPermission(AdministratorPermission $permission){
        return $this->permissions()->where('id',$permission->id)->exists();
    }
    /**
     * 当前角色是否属于某个用户.
     *
     * @param Administrator $administrator
     *
     * @return mixed
     */
    public function hasAdministrator(Administrator $administrator){
        return $this->administrators()->whereId($administrator->id)->exists();
    }
}
