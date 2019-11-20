<?php

namespace App\Http\Controllers\Frontend;



use App\Models\Course;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class CourseController extends FrontendController
{
    public function index(){
        $course=Course::show()
            ->published()
            ->orderByDesc('created_at')
            ->paginate(config('meedu.other.course_list_page_size', 6));
        ['title'=>$title,'keywords'=>$keywords,'description'=>$description]=config('meedu.seo.course_list');
        return view('frontend.course.index',compact('course','title','keywords','description'));
    }
    public function show($id,$slug){
        $course=Course::with(['comments','user','comments.user'])
            ->show()
            ->published()
            ->whereId($id)
            ->fristOrFail();
        $title=sprintf('课程《%s》',$course->title);
        $keywords=$course->keywords;
        $description=$course->comments()->orderByDesc('created_at')->get();
        return view('frontend.course.show',compact(
            'course',
            'title',
            'keywords',
            'description',
            'comments'
        ));
    }

    public function showBuyPage($id){
        $course=Course::findOrFail($id);
        $title=sprintf('购买课程《%s》',$course->title);

        return view('frontend.course.buy',compact('course','title'));
    }

    public function buyHandler(CourseRepository $repository,$id){
        $course=Course::findOrFail($id);
        $user=Auth::user();

        $order=$repository->createOrder($user,$course);
        if(!($order instanceof Order)){
            flash($order,'warning');
            return back();

        }
        flash('下单成功，请尽快支付');
        return redirect(route('order.show',$order->order_id));

    }

}
