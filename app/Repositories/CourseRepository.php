<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-11-13
 * Time: 2:04
 */

namespace App\Repositories;


use App\Models\Order;
use App\Models\OrderGoods;
use Illuminate\Support\Facades\DB;

class CourseRepository
{


    public function createOrder($user,$course){
        if($user->joinCourses()->whereId($course->id)->frist()){
            return '该视频已购买';
        }
        DB::beginTransaction();
        try{
            // 创建订单
            $order=$user->orders()->save(new Order([
                'charge'=>$course->charge,
            'status'=>Order::STATUS_UNPAY,
                'order_id'=>gen_order_no($user),

            ]));
            // 关联商品
            $order->goods()->save(new OrderGoods([
                'user_id'=>$user->id,
                'num'=>1,
                'charge'=>$course->charge,
                'goods_id'=>$course->id,
                'goods_type'=>OrderGoods::GOODS_TYPE_COURSE,
            ]));
            DB::commit();

        }
    }
}