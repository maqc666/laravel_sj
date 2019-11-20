<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Video extends Model
{
    const IS_SHOW_YES=1;
    const IS_SHOW_NO=-1;

    protected $table='video';
    protected $fillable=[
        'user_id', 'course_id', 'title', 'slug',
        'url', 'view_num', 'short_description', 'description',
        'seo_keywords', 'seo_description', 'published_at',
        'is_show', 'charge', 'aliyun_video_id',
        'chapter_id', 'duration', 'tencent_video_id',
    ];

    public function getPublishedAtAttribute($publishedAt){
        return Carbon::parse($publishedAt);
    }
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
    public function course(){
        return $this->belongsTo(Course::class,'course_id','id');
    }
    public function scopeShow($query){
        return $query->where('is_show',self::IS_SHOW_YES);
    }

    public function scopePublished($query){
        return $query->where('published_at','<=',date('Y-m-d H:i:s'));
    }
    public function comments(){
        return $this->hasMany(VideoComment::class,'video_id');
    }
    public function chapter(){
        return $this->belongsTo(CourseChapter::class,'chapter_id');
    }
    public function buyUsers(){
        return $this->belongsTo(User::class,'user_video','video_id','user_id')
            ->withPivot('charge','created_at');
    }

    public function commentHandler(string $content){
        $comment=$this->comments()->save(new VideoComment([
            'user_id'=>Auth::id(),
            'content'=>$content,
        ]));
        return $comment;
    }
    public function getPlayInfo(){
        if($this->aliyun_video_id!=''){
            $playInfo=aliyun_play_url($this);
            Log::info(json_encode($playInfo));

            return $playInfo;
        }
        return [
            [
                'format' => pathinfo($this->url, PATHINFO_EXTENSION),
                'url' => $this->url,
                'duration' => 0,
            ],
        ];
    }
    /**
     * 获取视频播放地址
     *
     * @return mixed
     */
    public function getPlayUrl(){
        if($this->url){
            return $this->url;
        }
        $playInfo=aliyun_play_url($this);

        return isset($playInfo[0])?$playInfo[0]['url']:'';
    }

}
