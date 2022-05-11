<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Like;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LikeController extends Controller
{
    public function index(Request $request)
    {
        $blog_id = $request->blog_post_id;

        $like = DB::table('likes')
                ->select()
                ->leftJoin('blog_posts', function($join){
                    $join->on('likes.blog_post_id','=','blog_posts.id');
                })
                ->where('likes.blog_post_id', $blog_id)
                ->count();

        return response()->json([
            'like' => $like
        ],Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        if($request->isMethod('post'))
        {
            DB::beginTransaction();

            try{

                $like = new Like();

                $like->user_id = Auth::id();
                $like->blog_post_id = $request->blog_post_id;

                if($request->like == 1)
                {
                    $like->like = 1;
                }else{
                    $like->like = 0;
                }

                $like->save();

                DB::commit();

                return response()->json([
                    'messsage' => 'like successful'
                ],Response::HTTP_CREATED);
               

            }catch(Exception $e){
                DB::rollBack();

                $error = $e->getMessage();

                return response()->json([
                    'error' => $error
                ],Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
