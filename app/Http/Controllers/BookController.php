<?php

namespace App\Http\Controllers;

use App\Bookdata;
use Illuminate\Http\Request;
use Aws\S3\S3Client;

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
      $this->validate($request, Bookdata::$rules);
      // 新規投稿レコード
      $book = new Bookdata;
      // リクエストデータ受取
      $form = $request->all();
      // フォームトークン削除
      unset($form['_token']);
      // 画像データ有無判定
      if (isset($form['picture'])) {
        // 画像保存ディレクトリ設定
        $save_directory = "book_images";

        // 画像情報
        $picture_name = $_FILES['picture']['name']; //ファイル名
        $picture_ext = substr($picture_name, strrpos($picture_name, '.') + 1); //拡張子
        $picture_tmp_name = $_FILES['picture']['tmp_name']; //tmp_name
        $picture_error = $_FILES['picture']['error']; //errorコード

        // 画像ファイルの判定
        picture_check($picture_ext,$picture_error);

        // 開発環境で画像保存先を変更
        if ( app()->isLocal() || app()->runningUnitTests() ) {
          // ローカル保存処理
          $request->picture->storeAs('public/'.$save_directory, $picture_name); // 画像ファイルをstorage保存
          $picture_upload = "/storage/".$save_directory."/".$picture_name; //画像保存パス

        } else {
          // 本番環境保存処理
          // S3インスタンス生成
          $s3settings = s3settings();

          // S3アップロード処理
          $picture_upload = picture_upload($save_directory,$picture_name,$picture_tmp_name,$picture_ext,$s3settings);
        }
        $form['picture'] = $picture_upload; //画像パスをDB保存値に設定
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
      $this->validate($request, Bookdata::$rules);
      // 画像保存ディレクトリ設定
      $save_directory = "book_images";
      // 対象レコード取得
      $book = Bookdata::find($id);
      // リクエストデータ受取
      $form = $request->all();
      // フォームトークン削除
      unset($form['_token']);

      // 写真変更がある場合
      if (isset($form['picture'])) {
        // 削除処理実施
        // 既存画像がない時は削除処理不要
        if ($book['picture'] != null) {
          // 開発環境で画像保存先を変更
          if ( app()->isLocal() || app()->runningUnitTests() ) {
            // 写真削除情報取得
            $deletename = str_replace('/storage/'.$save_directory.'/','',$book->picture);
            $pathdel = storage_path() . '/app/public/book_images/' . $deletename;
            // 写真削除
            \File::delete($pathdel);
          } else {
            // 本番環境削除処理
            // S3インスタンス生成
            $s3settings = s3settings();
            // S3削除処理
            $picture_delete = picture_delete($save_directory,$book,$s3settings);
          }
        }
        // 削除のみか判定
        if ($form['picture'] == "no-picture") {
          // レコードをnull化
          $picture_upload = null;
        // 画像変更時
        } else {
          // 写真追加処理
          // 変更画像情報取得
          $picture_name = $_FILES['picture']['name']; //ファイル名
          $picture_ext = substr($picture_name, strrpos($picture_name, '.') + 1); //拡張子
          $picture_tmp_name = $_FILES['picture']['tmp_name']; //tmp_name
          $picture_error = $_FILES['picture']['error']; //errorコード

          // 画像ファイルの判定
          picture_check($picture_ext,$picture_error);
          // 開発環境で画像保存先を変更
          if ( app()->isLocal() || app()->runningUnitTests() ) {
            // ローカル保存処理
            $request->picture->storeAs('public/'.$save_directory, $picture_name); // 画像ファイルをstorage保存
            $picture_upload = "/storage/".$save_directory."/".$picture_name; //画像保存パス
          } else {
            // 本番環境画像追加処理
            // S3インスタンス生成
            $s3settings = s3settings();
            // S3アップロード処理
            $picture_upload = picture_upload($save_directory,$picture_name,$picture_tmp_name,$picture_ext,$s3settings);
          }
        }
        $form['picture'] = $picture_upload; //画像パスをDB保存値に設定
      }

      // レコードアップデート
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
      // 画像保存ディレクトリ設定
      $save_directory = "book_images";
      // 削除レコード取得
      $delete_book = Bookdata::find($id);

      // 写真削除がある場合
      if (isset($delete_book['picture'])) {
        // 開発環境で画像保存先を変更
        if ( app()->isLocal() || app()->runningUnitTests() ) {
          // 写真削除情報取得
          $deletename = str_replace('/storage/'.$save_directory.'/','',$delete_book->picture);

          $pathdel = storage_path() . '/app/public/book_images/' . $deletename;
          // 写真削除
          \File::delete($pathdel);
        } else {
          // 本番環境削除処理
          // S3インスタンス生成
          $s3settings = s3settings();
          // S3削除処理
          $picture_delete = picture_delete($save_directory,$delete_book,$s3settings);
        }
      }
      // レコード削除
      $delete_book->delete();
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

    public function getIsbn(){
        $msg = 'ISBNコードを入力して下さい。';
        return view('book.isbn',['msg'=>$msg]);
    }
    public function postIsbn(Request $request){
        unset($request['_token']);
        $value = $request['isbn'];

        // ISBNコード桁数を確認
        if (strlen($value) != 13) {
          // レコード数不一致時はエラーを返して終了
          $msg = 'ISBNコードは13桁で入力してください';
          return view('book.isbn',['msg'=>$msg]);
        }

        // ISBNコードが既に登録されているか確認
        $isbn = \App\Bookdata::where('isbn', $value)->first();
        if ($isbn != null){
          $msg = '作成済みのデータがあります';
          return view('book.isbn',['msg'=>$msg]);
        }

        // ISBNコードから本情報を取得
        $isbn_url = 'https://api.openbd.jp/v1/get?isbn=';
        $response = file_get_contents(
                          $isbn_url.$value
                    );
        $result = json_decode($response, true);

        // ISBNレコード結果を確認
        if($result[0]==null){
          // 情報がない場合はエラーを返して終了
          $msg = '該当するISBNコードは見つかりませんでした。';
          return view('book.isbn',['msg'=>$msg]);
        }

        // ISBNレコードがあれば追加処理
        $savedata = new Bookdata;

        // summaryData取得
        $getdata = $result[0]["summary"];

        // 要素毎にレコードに追加
        foreach($getdata as $key => $value){
          if(strlen($value) == 0){
            $savedata->$key = null;
          } else {
            $savedata->$key = $value;
          }
        }

        // detail取得
        $detail_datacheck = empty($result[0]["onix"]["DescriptiveDetail"]["Contributor"][0]["BiographicalNote"]);
        if(strlen($detail_datacheck) == true){
          $savedata->detail = null;
        } else {
          $savedata->detail = $result[0]["onix"]["DescriptiveDetail"]["Contributor"][0]["BiographicalNote"];
        }
        // 保存
        $savedata->save();
        // 保存完了メッセージ
        $msg = 'データを新規作成しました';
        // ビューに出力
        return view('book.isbn',['msg'=>$msg,'book'=>$savedata]);
    }
}
