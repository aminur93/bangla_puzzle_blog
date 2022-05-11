<?php

namespace App\Http\Controllers\api;

use App\BlogPost;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Image;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = BlogPost::latest()->get();

        return response()->json([
            'blogs' => $blogs
        ],Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        if($request->isMethod('post'))
        {
            DB::beginTransaction();

            try{

                $blog = new BlogPost();

                $blog->user_id = Auth::id();
                $blog->title = $request->title;
                $blog->description = $request->description;
                $blog->date = $request->date;

                if($request->hasFile('image')){

                    $image_tmp = $request->file('image');

                    if($image_tmp->isValid()){

                        $image_name=time().'.'.$image_tmp->getClientOriginalExtension();

                        $original_image_path = public_path().'/admin/uploads/blog/'.$image_name;

                        Image::make($image_tmp)->save($original_image_path);

                        $blog->image = $image_name;
                    }
                }

                $blog->save();

                $blog_id = DB::getPdo()->lastInsertId();

                // dd($request->gallery_image);

                if ($request->input('gallery_image') !== null){

                    foreach ($request->input('gallery_image') as $gi){

                        $extenson =$gi->getClientOriginalExtension();
                        $filename = rand(111,99999).'.'.$extenson;

                        $original_image_path = public_path().'/admin/uploads/blog_gallery/'.$filename;

                        Image::make($gi)->save($original_image_path);

                        DB::table('blog_galleries')->insert([
                            'blog_post_id' => $blog_id,
                            'image' => $filename,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                    }
                }

                DB::commit();

                return response()->json([
                    'message' => 'Blog store successful'
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

    public function edit($id)
    {
        $blog = BlogPost::findOrFail($id);

        return response()->json([
            'blog' => $blog
        ],Response::HTTP_OK);
    }

    public function update(Request $request, $id)
    {
        if($request->isMethod('post'))
        {
            DB::beginTransaction();

            try{

                $blog = BlogPost::findOrFail($id);

                $blog->user_id = Auth::id();
                $blog->title = $request->title;
                $blog->description = $request->description;
                $blog->date = $request->date;

                if($request->hasFile('image')){

                    $image_tmp = $request->file('image');

                    if($blog->image == null){

                        $image_name=time().'.'.$image_tmp->getClientOriginalExtension();

                        $original_image_path = public_path().'/admin/uploads/blog/'.$image_name;

                        Image::make($image_tmp)->save($original_image_path);

                        $blog->image = $image_name;

                    }else{
                        if (file_exists(public_path().'/admin/uploads/blog/'.$blog->image)) {
                            unlink(public_path().'/admin/uploads/blog/'.$blog->image);
                        }

                        $image_name=time().'.'.$image_tmp->getClientOriginalExtension();

                        $original_image_path = public_path().'/admin/uploads/blog/'.$image_name;

                        //Resize Image
                        Image::make($image_tmp)->save($original_image_path);

                        $blog->image = $image_name;
                    }
                }

                $blog->save();

                if ($request->gallery_image)
                {
                    $blog_gallery = DB::table('blog_galleries')->where('blog_post_id', $blog->id)->get();

                    foreach ($blog_gallery as $bg){
                        if (file_exists(public_path().'/admin/uploads/blog_gallery/'.$bg->image)) {
                            unlink(public_path().'/admin/uploads/blog_gallery/'.$bg->image);
                        }
                    }

                    DB::table('blog_galleries')->where('blog_post_id', $id)->delete();

                    foreach ($request->gallery_image as $gi){

                        $images = $request->file($gi);

                        $extenson =$gi->getClientOriginalExtension();
                        $filename = rand(111,99999).'.'.$extenson;

                        $original_image_path = public_path().'/admin/uploads/blog_gallery/'.$filename;

                        //Resize Image
                        Image::make($gi)->save($original_image_path);

                        DB::table('blog_galleries')->insert([
                            'blog_post_id' => $blog->id,
                            'image' => $filename,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);

                    }
                }

                DB::commit();

                return response()->json([
                    'message' => 'Blog updated successful'
                ],Response::HTTP_OK);

            }catch(Exception $e){
                DB::rollBack();

                $error = $e->getMessage();

                return response()->json([
                    'error' => $error
                ],Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function destroy($id)
    {
        $blog = BlogPost::findOrFail($id);

        // $blog_gallery = DB::table('blog_galleries')->where('blog_post_id', $id)->get();

        
        // foreach($blog_gallery as $bg)
        // {
        //     if($bg->blog_post_id == $id)
        //     {
        //         $original_image_path = public_path().'/admin/uploads/blog_gallery/'.$bg->image;

        //         unlink($original_image_path);
        //     }

        // }

        // DB::table('blog_galleries')->where('blog_post_id', $id)->delete();
        

        if($blog->image != null)
        {
            $original_image_path = public_path().'/admin/uploads/blog/'.$blog->image;

            unlink($original_image_path);

            $blog->delete();
        }else{
            $blog->delete();
        }

        //$blog->delete();

        return response()->json([
            'message' => 'Blog deleted successful'
        ],Response::HTTP_OK);
    }
}
