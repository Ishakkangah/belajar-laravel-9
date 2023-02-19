<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class PostController extends Controller
{
    public function index(){
        $posts = Post::latest()->when(request()->search, function($posts) {
            $posts = $posts->where('title', 'like', '%' . request()->search . '%');
        })->paginate(5);

        return view('post.index', compact('posts'));
    }



    public function create(){
        return view('post.create');
    }

    public function store(Request $request){
        $this->validate($request, [
            'image' => 'required|image|mimes:png,jpg, jpeg',
            'title' => 'required',
            'content' => 'required',
        ]);


        // upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashNAme());

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'image' => $image->hashName(),
        ]);

       
        if($post){
            //redirect dengan pesan sukses
            return redirect()->route('post.index')->with(['success' => 'Data Berhasil Disimpan!']);
          }else{
            //redirect dengan pesan error
            return redirect()->route('post.index')->with(['error' => 'Data Gagal Disimpan!']);
          }

    }


    public function edit(Post $post){
        return view('post.edit', compact('post'));
    }

    public function update(Request $request, Post $post){
        $this->validate($request, [
            'title' => 'required',
            'content' => 'required',
        ]);

        $post  = Post::findOrFail($post->id);

        if($request->file('image') == ""){
            $post->update([
                'title' => $request->title,
                'content' => $request->content
            ]);
        } else {
            // hapus gambar 
            Storage::disk('local')->delete('public/posts/'.$post->image);
        
            // upload gambar
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());


            $post = $post->update([
                'title' => $request->title,
                'content' => $request->content,
                'image' => $image->hashName()
            ]);
        }


        if($post){
            //redirect dengan pesan sukses
            return redirect()->route('post.index')->with(['success' => 'Data Berhasil Disimpan!']);
          }else{
            //redirect dengan pesan error
            return redirect()->route('post.index')->with(['error' => 'Data Gagal Disimpan!']);
          }
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        Storage::disk('local')->delete('public/posts/'.$post->image);
        $post->delete();

        if($post){
            //redirect dengan pesan sukses
            return redirect()->route('post.index')->with(['success' => 'Data Berhasil Dihapus!']);
        }else{
            //redirect dengan pesan error
            return redirect()->route('post.index')->with(['error' => 'Data Gagal Dihapus!']);
        }
    }
}
