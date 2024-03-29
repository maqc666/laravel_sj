<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class Course extends Model
{
    use SoftDeletes;

    const SHOW_YES=1;
    const  SHOW_NO=-1;

    protected $table='course';

    protected $fillable=[
        'user_id','title','slug','thumb','charge',
        'short_description','description','seo_keywords',
        'seo_description','published_at','is_show',
    ];

    public function user(){
      return $this->belongsTo(User::class,'user_id','id');
    }

    public function chapters(){
        return $this->hasMany(CourseChapter::class,'course_id');
    }
    public function buyUsers(){
        return $this->belongsToMany(User::class,'user_course','course_id','user_id')
            ->withPivot('charge','created_at');
    }

    /**
     * 该课程下面的视频.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function videos(){
        return $this->hasMany(Video::class,'course_id','id');
    }

    /**
     * 作用域：显示.
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeShow($query){
        return $query->where('is_show',self::SHOW_YES);
    }
    /**
     * 作用域：不显示.
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeNotShow($query){
        return  $query->where('is_show',self::SHOW_NO);
    }
    /**
     * 作用域：上线的视频.
     *
     * @param $query
     *
     * @return mixed
     */
    public function ScopePublished($query){
        return $query->where('published_at','<=',date('Y-m-d H:i:s'));
    }

    /**
     * 作用域：关键词搜索.
     *
     * @param $query
     * @param string $keywords
     *
     * @return mixed
     */
    public function scopeKeyword($query, string $keywords){
        $keywords && $query->where('title','like',"%{$keywords}%");
        return $query;
    }
    public function getDescription(){
        return $this->description();
    }

    public function getAllPublishedAndShowVideosCache(){
        if(config('meedu.system.cache.status',-1)!=1){
            return $this->getAllPublishedAndShowVideos();
        }
        return Cache::remember("course_{$this->id}_videos",
            config('meedu.system.cache.expire',60),
            function(){
            return $this->getAllPublishedAndShowVideos();
            });
    }
    /**
     * 获取所有已出版且显示的视频.
     *
     * @return mixed
     */
    public function getAllPublishedAndShowVideos(){
        return $this->videos()
            ->published()
            ->show()
            ->orderBy('published_at')
            -get();
        }
    /**
     * 章节缓存.
     *
     * @return \Illuminate\Database\Eloquent\Collection|mixed
     */
    public function getChaptersCache(){
        if(config('meedu.system.cache.status',-1)!=1){
            return $this->getChapters();
        }
        return Cache::remember(
            "course_{$this->id}_chapter_videos",
            config('meedu.system.cache.expire', 60),
            function () {
                return $this->getChapters();
            }
        );
    }
    /**
     * 获取章节
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */

    public function getChapters(){
        return $this->chapters()->orderBy('sort')->get();
    }

    /**
     * 是否存在缓存.
     *
     * @return bool|mixed
     */
    public function hasChapterCache(){
        if(config('meedu.system.cache.status',-1)!=1){
            return $this->hasChapters();
        }
        return Cache::remember(
            "course_{$this->id}_has_chapters",
            config('meedu.system.cache.expire',60),
            function(){
                return $this->hasChapters();
            }
        );
    }
    /**
     * 是否存在章节
     *
     * @return bool
     */
    public function hasChapters(){
        return $this->chapters()->exists();
    }

    /**
     * 评论.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments(){
        return $this->hasMany(CourseComment::class,'course_id');
    }

    /**
     * 课程的观看URL.
     *
     * @return string
     */
    public function seeUrl(){
        $firstVideo=$this->videos()
            ->published()
            ->show()
            ->orderByDesc('published_at')
            ->first();

        return $firstVideo ? route('video.show',[$this->id,$firstVideo->id,$firstVideo->slug]):'javascripter:void(0)';
    }
    /**
     * 获取当前课程最近加入的用户[缓存].
     *
     * @return mixed
     */
    public function getNewJoinMembersCache(){
        $course=$this;
        if(!config('meedu.system.cache.status')){
            return $this->getNewJoinMembers();
        }

        return Cache::remember(
            "course:{$course->id}:new_join_member",
            config('member.system.cache.expire',60),
            function() use($course){
                return $course->getNewJoinMembers();
            }
        );
    }
    /**
     * 获取当前课程最近加入的用户.
     *
     * @return mixed
     */
    public function getNewJoinMembers(){
        return $this->buyUsers()->orderByDesc('pivot_created_at')->limit(10)->get();
    }

    /**
     * 评论处理.
     *
     * @param string $content
     *
     * @return false|Model
     */
    public function commentHandler(string $content){
        $comment=$this->comments()->save(new CourseComment([
            'user_id'=>Auth::id(),
            'content'=>$content,
        ]));
        return $comment;
    }

}
