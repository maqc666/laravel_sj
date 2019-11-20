<?php

namespace App\Http\Controllers\Frontend;

use App\Events\AdFromEvent;
use App\Models\Link;
use App\Repositories\IndexRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class IndexController extends FrontendController
{
    public function index(Request $request, IndexRepository $repository){
        $courses=$repository->recentPublishedAndShowCourses();
        $roles=$repository->roles();

        if($request->input('from')){
            event(new AdFromEvent($request->input('from')));
        }

        // 友情链接
        $links=Link::linksCache();

        ['title'=>$title,'keywords'=>$keywords,'description'=>$description]=config('meedu.seo.index');

        return view(
            config('meedu.advance.template_index','frontend.index.index'),
            compact('courses','roles','title','keywords','description','links')
        );

    }
}
