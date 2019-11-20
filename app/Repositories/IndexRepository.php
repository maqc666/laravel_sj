<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-11-06
 * Time: 17:17
 */

namespace App\Repositories;


use App\Models\Course;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class IndexRepository
{

    /**
     * 最新课程.
     *
     * @return mixed
     */

    public function recentPublishedAndShowCourses(){
        if(config('meedu.system.cache.status',-1)==1){
            return Cache::remember('index_recent_course',config('meedu.system.cache.expire',60),function (){
                return Course::published()->show()->orderByDesc('created_at')->limit(3)->get();
            });
        }
        return Course::published()->show()->orderByDesc('created_at')->limit(3)->get();
    }

    /**
     * 订阅.
     *
     * @return mixed
     */
    public function roles(){
        if(config('meedu.system.cache.status',-1)==1){
            return Cache::remember('index_roles',config('meedu.system.cache.expire',60),function (){
                return Role::show()->orderByDesc('weight')->get();
            });
        }
        return Role::show()->orderByDesc('weight')->limit(3)->get();
    }
}