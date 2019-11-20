<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Course;
use App\Models\CourseComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    public function index(Request $request){
        $keywords=$request->input('keywords','');
        $courses=Course::show()
            ->when($keywords,function($query) use($keywords){
                return $query->where('title','like','%{$keywords%}');
            })->published()->orderbyDesc('created_at')->paginate($request->input('page_size',10));
    return CourseListResource::collection($courses);
    }

    public function show($id){
        $course=Course::show()->published()->whereIn($id)->firstOrFail();
        return new CourseResourse($course);
    }

    public function video($id){
        $course=Course::show()->published()->whereIn($id)->firstOrFail();
        $videos=$course->getAllPublishedAndShowVideosCache();

        return CourseVideoListResource::collection($videos);
    }

    /**
     * 课程下的评论.
     *
     * @param Request $request
     * @param $id
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function comments(Request $request,$id){
        $course=Course::show()->published()->whereId($id)->firstOrFail();
        $comments=$course->comments()->orderByDesc('created_at')->paginate($request->input('page_size',10));
        return CourseCommentResource::collection($comments);
    }
}
