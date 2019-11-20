<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CourseChapter extends Model
{
    protected $table='course_chapter';
    protected  $fillable=[
        'course_id','title','sort',
    ];

    public function course(){
        return $this->belongsTo(Course::class,'course_id');
    }

    public function videos(){
        return $this->hasMany(Video::class,'chapter_id');
    }
    /**
     * 获取视频缓存.
     *
     * @return mixed
     */
    public function getVideoCache(){
        if(config('meedu.system.cache.status',-1)!=1){
            return $this->getVideos();
        }
        $that=$this;
        return Cache::remember(
            "chapter_{$this->id}_videos",
            config('meedu.system.cache.expire',60),
            function()use($that){
                return $that->getVideos();
            }
        );
    }

    /**
     * 获取已出版且显示的视频.
     *
     * @return mixed
     */

    public function getVideos(){
        return $this->videos()
            ->published()
            ->show()
            ->orderBy('published_at')
            ->get();
    }
}
