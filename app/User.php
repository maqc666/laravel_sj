<?php

namespace App;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;


    const ACTIVE_YES=1;
    const ACTIVE_NO=-1;

    const LOCK_YES=1;
    const LOCK_NO=-1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'avatar','nick_name','mobile','password',
        'is_lock','is_active','role_id','role_expired_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function findForPassport($name){
        return self::whereMobile($name)->first();
    }

    public function role(){
        return $this->belongsTo(Role::class,'role_id');
    }

    public static function randomNickName(){
        return 'random.'.str_random(10);
    }

    public function courses(){
        return $this->hasMany(Course::class,'user_id','id');

    }
    public function joinCourses(){
        return $this->belongsTo(Course::class,'user_course','user_id','course_id')
            ->withPivot('created_at','charge');
    }
    public function buyVideos(){
        return $this->belongsToMany(Video::class,'user_video','user_id','video_id')
            ->withPivot('created_at','charge');
    }
    public function courseComments(){
        return $this->hasMany(CurseComment::class,'user_id');
    }
    public function videoComments(){
        return $this->hasMany(VideoComment::class,'user_id');
    }

    public function joinACourse(Course $course){
        if(!$this->joinCourses()->whereId($course->id)->exists()){
            $this->joinCourses()->attach($course->id,[
                'created_at'=>Carbon::now()->format('Y-m-d H:i:s'),
                'charge'=>$course->charge,
            ]);
        }
    }

    public function buyAVideo(Video $video){
        if(!$this->buyVideos()->whereId($video->id)->exists()){
            $this->buyVideos()->attach($video->id,[
                'created_at'=>Carbon::now()->format('Y-m-d H:i:s'),
                'charge'=>$video->charge,
            ]);
        }
    }

    /**
     * 头像修饰器.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getAvatarAttribute($avatar){
        return $avatar?:url(config('meedu.member.default_avatar'));
    }
    /**
     * 关联订单.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders(){
        return $this->hasMany(Order::class,'user_id');
    }


    /**
     * 余额扣除.
     *
     * @param $money
     */
    public function credit1Dec($money){
        $this->credit1-=$money;
        $this->save();
    }

    /**
     * 判断用户是否可以观看指定的视频.
     *
     * @param Video $video
     *
     * @return bool
     */
    public function canSeeThisVideo(Video $video){
        $course=$video->course;
        if($course->charge==0||$video->charge==0){
            return true;
        }
        if($this->activeRole()){
            return true;
        }
        //是否加入课程
        $hasJoinCourse=$this->joinCourses()->whereId($video->course->id)->exists();
        if($hasJoinCourse){
            return true;
        }

        //是否购买视频
        return $this->buyVideos()->whereId($video->id)->exists();
    }

    /**
     * 是否为有效会员.
     *
     * @return bool
     */
    public function activeRole(){
        return $this->role_id&&time()<strtotime($this->role_expired_at);
    }
    public function joinRoles(){
        return $this->hasMany(UserJoinRoleRecord::class,'user_id');
    }


    public function buyRole(Role $role){

        throw_if($this->role&&$this->role->weight!=$role->weight,new Exception('该账户已经存在会员记录'));

        if($this->role){
            $startDate=$this->role_expired_at;
            $endDate=Carbon::createFromFormat('Y-m-d H:i:s',$this->role_expired_at)->addDays($role->expire_days);
        }else{
            $startDate=Carbon::now();
            $endDate=Carbon::now()->addDays($role->expire_days);
        }
        $this->role_id=$role->id;
        $this->role_expired_at=$endDate;
        $this->save();

        $this->joinRoles()->save(new UserJoinRoleRecord([
            'role_id'=>$this->role_id,
            'charge'=>$role->charge,
            'started_at'=>$startDate,
            'expired_at'=>$endDate,
        ]));

    }
    /**
     * 今日注册用户数量.
     *
     * @return mixed
     */

    public static function todayRegisterCount(){
        return self::createdAtBetween(
            Carbon::now()->format('Y-m-d'),
            Carbon::now()->addDats(1)->format('Y-m-d')
        )->count();
    }
    /**
     * 订单成功的处理.
     *
     * @param Order $order
     *
     * @return bool
     *
     * @throws \Throwable
     */

    public function handlerOrderSuccess(Order $order){
        $goods=$order->goods;
        DB::beginTransaction();
        try{
            foreach($goods as $goodsItem){
                switch($goodsItem->goods_type){
                    case OrderGoods::GOODS_TYPE_COURSE:
                        $course=Course::find($goodsItem->goods_id);
                        $this->joinACourse($course);
                        break;
                    case OrderGoods::GOODS_TYPE_VIDEO:
                        $video=Video::find($goodsItem->goods_id);
                        $this->buyAVideo($video);
                        break;
                    case OrderGoods::GOODS_TYPE_ROLE:
                        $role=Role::find($goodsItem->goods_id);
                        $this->buyRole($role);
                        break;
                }
            }
            DB::commit();
            return true;
        }catch (Exception $exception){
            DB::rollBack();
            exception_record($exception);

            return false;

        }
    }

    public function socialite(){
        return $this->hasMany(Socialite::class,'user_id');
    }

    public static function createUser($name,$avatar){
        return User::creae([
            'avatar'=>$avatar?:config('meedu.member.default_avatar'),
            'nick_name' => $name ?? '',
            'mobile' => mt_rand(2, 9).mt_rand(1000, 9999).mt_rand(1000, 9999),
            'password' => Hash::make(Str::random(6)),
            'is_lock' => config('meedu.member.is_lock_default'),
            'is_active' => config('meedu.member.is_active_default'),
            'role_id' => 0,
            'role_expired_at' => Carbon::now(),
        ]);
    }

    public function bindSocialite($app, $socialite){
        return $this->socialite()->save(new Socialite([
            'app' => $app,
            'app_user_id' => $socialite->getId(),
            'data' => serialize($socialite),
        ]));
    }
    /**
     * 判断是否绑定手机.
     *
     * @return bool
     */
    public function isBindMobile(){
        return substr($this->mobile,0,1)==1;
    }
}
