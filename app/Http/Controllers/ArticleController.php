<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Article;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = [
            'title'=>'Article list',
            'articles'=>Article::with('category')->get()
        ];
        return view('admin.articles.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'title'=>'Article create',
            'categories'=>Category::get()
        ];
        return view('admin.articles.create',$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'title'=>'required',
            'isi'=>'required',
            'gambar'=>'image|mimes:jpg,png,bmp,jpeg,webp',
            'category_id'=>'required'
        ]);
        $file = $request->file('gambar');
        $slug = SlugService::createSlug(Article::class, 'slug', $request->title);
        if (!$file) {
            $namafile = "default";
        } else {
            if (!Storage::exists('/public/articles')) {
                Storage::makeDirectory('public/articles', 0775, true);
            }
            $namafile =  $file->hashName();
            $img = Image::make($file->path());
            $img->resize(1080, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save(Storage::path('public/articles/' . $namafile));
        }
        $save=Article::create([
            'title'=>$request->title,
            'slug'=>$slug,
            'category_id'=>$request->category_id,
            'penulis'=>$request->penulis,
            'body'=>$request->isi,
            'gambar'=>$namafile
        ]);
        if($save){
            return redirect()->route('articles.index')->with('success','Data berhasil ditambahkan');
        }else{
            return redirect()->route('articles.index')->with('failed','Data gagal ditambahkan');

        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        $data = [
            'title' => 'Detail Article',
            'posts' => $article->load('category'),
            'categories'=>Category::get()
        ];

        return view('admin.articles.details', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title'=>'required',
            'isi'=>'required',
            'penulis'=>'required',
            'gambar'=>'image|mimes:jpg,png,bmp,jpeg,webp'
        ]);
        $file = $request->file('gambar');
        $slug = SlugService::createSlug(Article::class, 'slug', $request->title);
        if (!$file) {
            $namafile = $article->gambar;
        } else {
            if (!Storage::exists('/public/articles')) {
                Storage::makeDirectory('public/articles', 0775, true);
            }
            $namafile =  $file->hashName();
            $img = Image::make($file->path());
            $img->resize(1080, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            Storage::delete('public/articles/' . $articles->image);
            $img->save(Storage::path('public/articles/' . $namafile));
        }
        $update=Article::where('id',$article->id)->update([
            'title'=>$request->title,
            'slug'=>$slug,
            'penulis'=>$request->penulis,
            'category_id'=>$request->category_id,
            'body'=>$request->isi,
            'gambar'=>$namafile
        ]);
        if($update){
            return redirect()->route('articles.index')->with('success','Data berhasil diupdate');
        }else{
            return redirect()->route('articles.index')->with('failed','Data gagal diupdate');

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $delete = $article->delete();
        if ($delete) {
            Storage::delete('public/articles/' . $article->image);
            return redirect()->route('articles.index')->with('success', 'Data Berhasil dihapus');
        } else {
            return redirect()->route('articles.index')->with('failed', 'Data Gagal dihapus');
        }
    }

    public function categories()
    {
        $data = [
            'title' => 'List Categories',
            'categories' => Category::get()
        ];
        return view('admin.articles.categories', $data);
    }

    public function save_category(Request $request)
    {
        $request->validate([
            'category' => 'required'
        ]);
        $save = Category::create([
            'category' => $request->category
        ]);
        if ($save) {
            return redirect()->route('categories.index')->with('success', 'Data was added');
        } else {
            return redirect()->route('categories.index')->with('failed', 'Data was failed to add');
        }
    }
    public function delete_category(Category $category)
    {
        $delete = $category->delete();
        if ($delete) {
            return redirect()->route('categories.index')->with('success', 'Data was deleted');
        } else {
            return redirect()->route('categories.index')->with('failed', 'Data was failed to delete');
        }
    }

}
