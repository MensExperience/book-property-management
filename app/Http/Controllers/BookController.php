<?php

namespace App\Http\Controllers;

use App\Bookdata;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $books = Bookdata::all();
        return view('book.index', ['books' => $books]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return view('book.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      // 新規投稿レコード
      $book = new Bookdata;
      // リクエストデータ受取
      $form = $request->all();
      // フォームトークン削除
      unset($form['_token']);
      // 画像データ情報取得
      $file = $request->file('picture');
      if (isset($form['picture'])) {
        // 拡張子取得
        $ext = $file->getClientOriginalExtension();
        // ファイル保存用トークン発行
        $file_token = str_random(32);
        // 画像ファイル名作成
        $pictureFile = $file_token . "." . $ext;
        // 画像ファイル名指定
        $form['picture'] = $pictureFile;
        // 画像ファイルをstorage保存
        $request->picture->storeAs('public/book_images', $pictureFile);
      }
      // DB保存
      $book->fill($form)->save();
      return redirect('/book');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $book = Bookdata::find($id);
      return view('book.show', ['book' => $book]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $book = Bookdata::find($id);
      return view('book.edit', ['form' => $book]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $book = Bookdata::find($id);
      $form = $request->all();
      unset($form['_token']);
      $book->fill($form)->save();
      return redirect('/book');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      Bookdata::find($id)->delete();
      return redirect('/book');
    }

    public function find(Request $request)
    {
      return view('book.find', ['input' => '']);
    }

    public function search(Request $request)
    {
      $title = $request->input;
      $books = Bookdata::where('title', 'like', "%{$title}%")->get();
      $param = ['input' => $title, 'books' => $books];
      return view('book.find', $param);
    }
}
