<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\User;
use App\Bookdata;
use App\Property;
use Illuminate\Support\Facades\Auth;

class BookdataTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */

     // トップページアクセス確認
    public function test_indexAccess_ok()
    {
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        $response = $this->get('/book'); // bookへアクセス
        $response->assertStatus(200); // 200ステータスであること
    }

     // 手動登録確認
     // 写真なしパターン
     public function test_bookControll_ok()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// createページアクセス
        $response = $this->get('/book/create'); // createへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.create'); // book.createビューであること

        //// 登録
        $bookdata = [
            'title' => 'テストブック',
            'detail' => '詳細はこちら',
        ];
        $response = $this->from('book/create')->post('book', $bookdata); // 本情報保存
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        // 登録されていることの確認(indexページ)
        $response = $this->get('book'); // bookへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.index'); // book.indexビューであること
        $response->assertSeeText($bookdata['title']); // 登録タイトルが表示されていること

        // 詳細ページで表示されること
        $savebook = Bookdata::all()->first(); // 保存情報確認
        $response = $this->get('book/'.$savebook['id']); // 指定bookへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.show'); // book.showビューであること
        foreach ($savebook as $value)
        {
            $response->assertSeeText($value);
        }; // savebookデータが表示されていること

        //// 編集
        // 写真なしパターン
        $edit_post = 'book/'.$savebook['id']; // 編集パス
        $response = $this->get($edit_post.'/edit'); // 編集ページへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.edit'); // book.editビューであること

        // 編集内容
        $editbookdata = [
            'title' => 'テストブック編集',
            'detail' => 'テストとして変更した詳細',
            '_method' => 'PUT',
        ];
        $response = $this->from($edit_post.'/edit')->post($edit_post, $editbookdata); // 編集実施
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('/book');  // トップページ表示

        // 編集されていることの確認(indexページ)
        $response = $this->get('book'); // bookへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.index'); // book.indexビューであること
        $response->assertSeeText($editbookdata['title']); // 編集タイトルが表示されていること

        //// 削除
        // 写真なしパターン
        $response = $this->from('book/'.$savebook['id'])->post('book/'.$savebook['id'], [
            '_method' => 'DELETE',
            ]); // 削除実施
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('/book');  // トップページ表示

        // 削除されていることの確認(indexページ)
        $response = $this->get('book'); // bookへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.index'); // book.indexビューであること
        $response->assertDontSeeText($editbookdata['title']); // 削除タイトルが表示されていないこと
    }
    // isbn登録と表示確認
    public function test_isbnCreate_ok()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // isbnページアクセス
        $response = $this->get('/book/isbn'); // isbnへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.isbn'); // book.isbnビューであること

        // 登録
        $isbn = ['isbn' => 9784798052588]; // 新規登録コード
        $response = $this->from('book/isbn')->post('book/isbn', $isbn); // isbn情報保存
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること
        $response->assertSeeText('データを新規作成しました'); // メッセージが出力されていること
        
        // 登録されていることの確認(indexページ)
        $savebook = Bookdata::all()->first(); // 保存されたデータを取得
        $response = $this->get('book'); // bookへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.index'); // book.indexビューであること
        $response->assertSeeText($savebook['title']); // bookdataタイトルが表示されていること

        // 詳細ページで表示されること
        $response = $this->get('book/'.$savebook['id']); // 指定bookへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.show'); // book.showビューであること
        foreach ($savebook as $value)
        {
            $response->assertSeeText($value);
        }; // savebookデータが表示されていること
    }
    // 複数isbn登録と表示確認
    public function test_someIsbnCreate_ok()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // isbnページアクセス
        $response = $this->get('/book/isbn_some'); // isbnへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.isbn_some'); // book.isbnビューであること

        // 登録
        $isbn0 = 9784798052588;
        $isbn1 = 9784756918765;
        $isbns = [
            'isbn0' => $isbn0,
            'isbn1' => $isbn1,
        ]; // 新規登録コード
        $response = $this->from('book/isbn_some')->post('book/isbn_some', $isbns); // isbn情報保存
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        $response->assertSeeText($isbn0); // 入力番号が反映されていること
        $response->assertSeeText($isbn1); // 入力番号が反映されていること
        $savebook = Bookdata::first();
        $response->assertSeeText($savebook->title); // 取得した本のタイトルがが反映されていること
        $bookcount = count(Bookdata::all());
        $savebook = Bookdata::find($bookcount);
        $response->assertSeeText($savebook->title); // 取得した本のタイトルがが反映されていること
    }
    // 一括isbn登録と表示確認
    public function test_someIsbnCommaCreate_ok()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // isbnページアクセス
        $response = $this->get('/book/isbn_some_input'); // isbnへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.isbn_some_input'); // book.isbnビューであること

        // 登録
        $datas = [ 
            9784756918765,9784844366454,9784798052588,9784863542174,9784822282516,
            9784054066892,9784046023919,9784046023919,9784797397390,9784023332591,
        ]; // 登録用データ
        $isbns = implode(",", $datas); // フォーム入力用一括登録テキスト（カンマ区切り）
        $inputform = ['isbns' => $isbns]; // フォーム送信用データ生成
        $response = $this->from('book/isbn_some')->post('book/isbn_some', $inputform); // フォームよりpost送信
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        foreach($datas as $data){
            $response->assertSeeText($data); // 入力番号が反映されていること
        }
        $savebooks = Bookdata::all();
        foreach($savebooks as $savebook){
            $response->assertSeeText($savebook->title); // 取得した本のタイトルがが反映されていること
        }
    }
    // 一括isbn登録、ハイフン解除確認
    public function test_someIsbnCommaHyphenCreate_ok()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録
        $testisbn = '978-4756918765'; // テスト用ISBNコード
        $inputform = ['isbns' => $testisbn]; // フォーム送信用データ生成
        $response = $this->from('book/isbn_some')->post('book/isbn_some', $inputform); // フォームよりpost送信
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        $data = str_replace('-', '', $testisbn); // 表示確認コード生成、ハイフンを取り除く
        $response->assertSeeText($data); // 入力番号が反映されていること
        $savebook = Bookdata::first();
        // eval(\Psy\sh());
        $response->assertSeeText($savebook->title); // 取得した本のタイトルがが反映されていること
    }
    // 一括isbn登録、空文字削除対応確認
    public function test_someIsbnCommaSpaceCreate_ok()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録
        $testisbn = 
            "9784756918765\n9784844366454"; // テスト用ISBNコード
        $inputform = ['isbns' => $testisbn]; // フォーム送信用データ生成
        $response = $this->from('book/isbn_some')->post('book/isbn_some', $inputform); // フォームよりpost送信
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        $datas = preg_split("/[\s,]+/", $testisbn); // 確認用データを抽出
        foreach ($datas as $data){
            $response->assertSeeText($data); // 入力番号が反映されていること
        }
        $savebooks = Bookdata::all();
        foreach($savebooks as $savebook){
            $response->assertSeeText($savebook->title); // 取得した本のタイトルがが反映されていること
        }
    }
    // 検索書籍有り
    public function test_findTitle_ok_yesMatchFindTitle()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // faker book自動生成
        $bookdata = factory(Bookdata::class)->create();
        // 検索
        $find_post = 'book/find'; // 検索パス
        $response = $this->get($find_post); // 検索ページへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.find'); // book.findビューであること
        $response = $this->call('GET', 'book/search', ['find' => $bookdata->title]); // 検索実施
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.find'); // book.findビューであること
        $response->assertSeeText($bookdata->title); // bookdataタイトルが表示されていること
    }
    // 検索書籍なし
    public function test_findTitle_ok_noMatchFindTitle()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // faker book自動生成
        $bookdata = factory(Bookdata::class)->create([
            'title' => 'a'
        ]); // タイトル名aで作成

        //// 検索
        // 検索の実施(findページ)
        $find_post = 'book/find'; // 検索パス
        $savebook = Bookdata::all()->first(); // 保存されたデータを取得
        $response = $this->get($find_post); // 検索ページへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.find'); // book.findビューであること
        $response = $this->call('GET', 'book/search', ['find' => 'b']); // 検索実施
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200 ステータスであること
        $response->assertViewIs('book.find'); // book.findビューであること
        $response->assertSeeText('書籍がみつかりませんでした。'); // タイトルなしメッセージが表示されていること
    }
     // 複数削除
     public function test_bookControll_ok_someDelete()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // faker 自動生成
        $delete_bookdatas = factory(Bookdata::class, 20)->create();
        // 送信フォームパス
        $formpath = 'book/somedelete';
        // 削除データ
        $deletedata = ['select_books' => range(1, 20)];

        //// 削除
        $response = $this->from($formpath)->post($formpath, $deletedata); // 削除実施
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.delete_result'); // book.delete_resultビューであること

        // 削除されていることの確認(resultページ)
        $response->assertSeeText('書籍削除結果'); // 削除結果ページが出力されていること
        $response->assertStatus(200); // 200ステータスであること
        foreach($delete_bookdatas as $deletebook){
            $response->assertSeeText($deletebook->title); // 削除タイトルが結果に反映されていること
        }
    }
    // ページネーション表示テスト
    public function test_bookControll_ok_paginationView()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 件数設定
        $datarecords = 21; // テストレコード数
        $paginate = 20; // 1ページ表示数
        $totalpage = ceil($datarecords/$paginate); // 表示ページ総数

        // faker 自動生成
        $books = factory(Bookdata::class, $datarecords)->create();

        // index表示パス
        $viewpath = 'book?page=';

        for ($i = 1; $i <= $totalpage; $i++){ // ページ数分繰り返し
            $response = $this->get($viewpath.$i); // 各ページへアクセス
            $response->assertStatus(200); // 200ステータスであること

            // 表示ページに対してデータが確認できること
            // レコード件数/総数で対象ページを算出
            $j = 0;
            foreach($books as $book){
                $j++;
                if ($i == ceil($j/$paginate)) { // 表示ページデータの場合
                    $response->assertSeeText($book->title); // 対象ページに表示されていること
                } else { // 表示ページデータでない場合
                    $response->assertDontSeeText($book->title); // 対象ページに表示されていないこと
                }
            }
        }
    }

    //// NGパターン調査
    // 手動登録タイトル未入力
    public function test_bookControll_ng_notNameEntry()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 登録
        $bookdata = [
            'title' => '',
            'detail' => '詳細はこちら',
        ];
        $response = $this->from('book/create')->post('book', $bookdata); // 本情報保存
        $response->assertSessionHasErrors(['title']); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('book/create');  // 同ページへリダイレクト
        $this->assertEquals('titleは必須です。',
        session('errors')->first('title')); // エラメッセージを確認
    }
    // ISBN登録未入力
    public function test_bookControll_ng_notIsbnEntry()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 登録
        $bookdata = [
            'isbn' => ''
        ]; // コード未入力
        $response = $this->from('book/isbn')->post('book/isbn', $bookdata); // isbn登録
        $response->assertSessionHasErrors(['isbn']); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('book/isbn');  // 同ページ表示
        $this->assertEquals('isbnは必須です。',
        session('errors')->first('isbn')); // エラメッセージを確認
    }
    // ISBN登録桁数誤り小
    public function test_bookControll_ng_IsbnSmallEntry()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 登録
        $bookdata = [
            'isbn' => '123456789012'
        ]; // 12桁コード
        $response = $this->from('book/isbn')->post('book/isbn', $bookdata); // isbn登録
        $response->assertSessionHasErrors(['isbn']); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('book/isbn');  // 同ページ表示
        $this->assertEquals('isbnは13桁にしてください',
        session('errors')->first('isbn')); // エラメッセージを確認
    }
    // ISBN登録桁数誤り大
    public function test_bookControll_ng_IsbnBigEntry()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 登録
        $bookdata = [
            'isbn' => '12345678901234'
        ]; // 14桁コード
        $response = $this->from('book/isbn')->post('book/isbn', $bookdata); // isbn登録
        $response->assertSessionHasErrors(['isbn']); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('book/isbn');  // 同ページ表示
        $this->assertEquals('isbnは13桁にしてください',
        session('errors')->first('isbn')); // エラメッセージを確認
    }
    public function test_bookControll_ng_notIsbnData()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 登録
        $newbookdata = [
            'isbn' => '1234567890123'
        ]; // 13桁コード
        $response = $this->from('book/isbn')->post('book/isbn', $newbookdata); // isbn登録
        $response->assertSessionHasErrors(['isbn']); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('book/isbn');  // 同ページ表示
        $this->assertEquals('該当するISBNコードは見つかりませんでした。',
        session('errors')->first('isbn')); // エラメッセージを確認
    }

    // 書籍情報編集NG、タイトルなし
    public function test_bookControll_ng_notTitleEdit()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 仮本新規登録
        $bookdata = factory(Bookdata::class)->create(); // 書籍を作成
        $bookpath = 'book/'.$bookdata->id.'/edit'; // 書籍編集パス
        //// 登録
        $editbookdata = [
            'title' => '',
        ]; // タイトルなしに編集
        $response = $this->from($bookpath)->post('book', $editbookdata); // 本情報保存
        $response->assertSessionHasErrors(['title']); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect($bookpath);  // トップページ表示
        $this->assertEquals('titleは必須です。',
        session('errors')->first('title')); // エラメッセージを確認
    }
    // 書籍情報編集NG、未登録id
    public function test_bookControll_ng_notIdEdit()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 仮本新規登録
        $bookdata = factory(Bookdata::class)->create(); // 書籍を作成
        $bookpath = 'book/2/edit'; // 書籍編集パス(存在しないID)

        $response = $this->get($bookpath); // ページにアクセス
        $response->assertStatus(500);  // 500ステータスであること
    }
    public function test_bookControll_ng_notIdDelete()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 仮本新規登録
        $bookdata = factory(Bookdata::class)->create(); // 書籍を作成
        $bookpath = 'book/2'; // 書籍編集パス(存在しないID)

        // アクセス不可
        $response = $this->get($bookpath); // ページにアクセス
        $response->assertStatus(500);  // 500ステータスであること

        //// 削除
        $response = $this->from($bookpath)->post($bookpath, [
            '_method' => 'DELETE',
            ]); // 削除実施
        $response->assertStatus(500);  // 500ステータスであること
    }
    public function test_bookControll_ng_haveProrpertyDelete()
    {
        // property 自動生成 // 関連 user,bookdataも作成
        $bookdata = factory(Property::class)->create();
        $bookpath = 'book/'.$bookdata->id; // 書籍削除パス指定
        $user = User::find(1); // ユーザー指定
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 所持ユーザーありで削除実施
        $response = $this->from($bookpath)->post($bookpath, [
            '_method' => 'DELETE',
            ]); // 削除実施
        $response->assertSessionHasErrors(['bookdata_id']); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect($bookpath);  // 編集ページ表示
        $this->assertEquals('所有者がいるため削除できません',
        session('errors')->first('bookdata_id')); // エラメッセージを確認
    }
    public function test_findTitle_ng_noTitle()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        //// 検索
        // 検索の実施(findページ)
        $find_post = 'book/find'; // 検索パス
        $savebook = Bookdata::all()->first(); // 保存されたデータを取得
        $response = $this->get($find_post); // 検索ページへアクセス
        $response->assertStatus(200); // 200ステータスであること
        $response->assertViewIs('book.find'); // book.findビューであること
        $response = $this->call('GET', 'book/search', ['find' => '']); // 検索実施
        $response->assertSessionHasErrors('find'); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('book/find');  // 同ページへリダイレクト
        $this->assertEquals('検索ワードは必須です。',
        session('errors')->first('find')); // エラメッセージを確認
    }
    // 複数isbn登録エラー、登録なし
    public function test_someIsbnCreate_ng_notEntry()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録
        $bookpath = 'book/isbn_some';
        $response = $this->from($bookpath)->post($bookpath, []); // isbn情報保存
        $response->assertSessionHasErrors(['isbnrecords']); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect($bookpath);  // トップページ表示
        $this->assertEquals('ISBNコードが入力されていません',
        session('errors')->first('isbnrecords')); // エラメッセージを確認
    }
    // 複数isbn登録エラー、コード12桁
    public function test_someIsbnCreate_ng_isbn12()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録
        $isbn0 = 123456789012;
        $isbns = [
            'isbn0' => $isbn0,
        ]; // 新規登録コード
        $bookpath = 'book/isbn_some';
        $response = $this->from($bookpath)->post($bookpath, $isbns); // isbn情報保存
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        $response->assertSeeText($isbn0); // 入力番号が反映されていること
        $response->assertSeeText('桁数が13桁ではありません。'); // 取得できていない旨のメッセージが表示されること
    }
    // 複数isbn登録エラー、コード14桁
    public function test_someIsbnCreate_ng_isbn14()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録
        $isbn0 = 12345678901234;
        $isbns = [
            'isbn0' => $isbn0,
        ]; // 新規登録コード
        $bookpath = 'book/isbn_some';
        $response = $this->from($bookpath)->post($bookpath, $isbns); // isbn情報保存
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        $response->assertSeeText($isbn0); // 入力番号が反映されていること
        $response->assertSeeText('桁数が13桁ではありません。'); // 取得できていない旨のメッセージが表示されること
    }
    // 複数isbn登録エラー、フォーム重複
    public function test_someIsbnCreate_ng_formEntryDouble()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録
        $isbn0 = 9784798052588;
        $isbn1 = 9784798052588;
        $isbns = [
            'isbn0' => $isbn0,
            'isbn1' => $isbn1,
        ]; // 新規登録コード
        $bookpath = 'book/isbn_some';
        $response = $this->from($bookpath)->post($bookpath, $isbns); // isbn情報保存
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        $response->assertSeeText($isbn0); // 入力番号が反映されていること
        $response->assertSeeText("1件目のデータと同じです。"); // 重複メッセージが表示されること
    }
    // 複数isbn登録エラー、DB重複
    public function test_someIsbnCreate_ng_InDbEntry()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認
        // faker book自動生成
        $bookdata = factory(Bookdata::class)->create([
            'title' => 'a',
            'isbn' => '9784798052588'
        ]); // DBにISBNを登録しておく

        // 登録
        $isbn0 = 9784798052588;
        $isbns = [
            'isbn0' => $isbn0,
        ]; // 新規登録コード
        $bookpath = 'book/isbn_some';
        $response = $this->from($bookpath)->post($bookpath, $isbns); // isbn情報保存
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        $response->assertSeeText($isbn0); // 入力番号が反映されていること
        $response->assertSeeText("既に登録されています。"); // 重複メッセージが表示されること
    }
    // 複数isbn登録エラー、API検索結果なし
    public function test_someIsbnCreate_ng_findNotData()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録
        $isbn0 = 1234567890123;
        $isbns = [
            'isbn0' => $isbn0,
        ]; // 新規登録コード
        $bookpath = 'book/isbn_some';
        $response = $this->from($bookpath)->post($bookpath, $isbns); // isbn情報保存
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        $response->assertSeeText($isbn0); // 入力番号が反映されていること
        $response->assertSeeText('該当するISBNコードは見つかりませんでした。'); // 取得できていない旨のメッセージが表示されること
    }
    // 一括isbn登録エラー、タイトル0件
    public function test_someIsbnCommaCreate_ng_notEntry()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        $bookpath ='book/isbn_some';
        $inputform = ['isbns' => null]; // フォーム送信用データ生成
        $response = $this->from($bookpath)->post($bookpath, $inputform); // フォームよりpost送信
        $response->assertSessionHasErrors(); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect($bookpath);  // トップページ表示
        $this->assertEquals('ISBNコードが入力されていません',
        session('errors')->first('isbnrecords')); // エラメッセージを確認
    }
    // 一括isbn登録エラー、上限登録超過
    public function test_someIsbnCommaCreate_ng_limitNumber()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録用配列生成（上限＋1生成）
        $count = 20; // 上限数
        for ($i = 0; $i < $count+1; $i++){ // 上限数+1個生成
            $datas[] = 1234567890123 + $i; // 13桁コード作成(重複なし)
        }
        $bookpath ='book/isbn_some';
        $isbns = implode(",", $datas); // フォーム入力用一括登録テキスト（カンマ区切り）
        $inputform = ['isbns' => $isbns]; // フォーム送信用データ生成
        $response = $this->from($bookpath)->post($bookpath, $inputform); // フォームよりpost送信
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
        // 編集ISBN番号結果確認
        for ($i = 0; $i < $count+1; $i++){
            if ($i < $count){ // 上限数まで結果表示されること
                $response->assertSeeText($datas[$i]); // 入力番号が反映されていること
            } else { // 上限超過データは扱われていないこと
                $response->assertDontSeeText($datas[$i]); // 入力番号が反映されていないこと
            }
        }
    }
    // 一括isbnエラー、数値以外入力
    public function test_someIsbnCommaCreate_ng_notNumber()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 登録
        $datas = [
            '978ー4756918765', // 全角文字あり
            '97847@56918765', // 記号あり
            '９７８４７５６９１８７６５', // 全角数字
            '9784756i18765', // 半角英字あり
        ]; 
        foreach ($datas as $data){
            $inputform = ['isbns' => $data]; // フォーム送信用データ生成
            $bookpath ='book/isbn_some';
            $response = $this->from($bookpath)->post($bookpath, $inputform); // フォームよりpost送信
            $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
            $response->assertStatus(200); // 200ステータスであること
    
            $response->assertSeeText('ISBNコード登録結果'); // 登録結果ページが出力されていること
            $response->assertSeeText($data); // 入力番号が反映されていること
            $response->assertSeeText('数値ではありません'); // エラー名が表示されていること
        }
    }
     // 複数削除エラー、postデータなし
     public function test_bookControll_ng_someDeleteNotEntry()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 送信フォームパス
        $formpath = 'book/somedelete';
        // 削除データ
        $deletedata = [];

        //// 削除
        $response = $this->from($formpath)->post($formpath, $deletedata); // 削除実施
        $response->assertSessionHasErrors(); // エラーメッセージがあること
        $response->assertStatus(302); // リダイレクト
        $response->assertRedirect('book'); // bookビューであること
        $this->assertEquals('削除する本が選択されていません',
        session('errors')->first('deletebook')); // エラメッセージを確認
    }
     // 複数削除エラー、上限超過処理
     public function test_bookControll_ng_someDeleteLimitNumber()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        $count = 20;// 上限設定
        // faker 自動生成
        $delete_bookdatas = factory(Bookdata::class, $count+1)->create();
        // 送信フォームパス
        $formpath = 'book/somedelete';
        // 削除データ
        $deletedata = ['select_books' => range(1, 20)];

        //// 削除
        $response = $this->from($formpath)->post($formpath, $deletedata); // 削除実施
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('書籍削除結果'); // 登録結果ページが出力されていること
        // 編集ISBN番号結果確認
        for ($i = 0; $i < $count+1; $i++){
            if ($count > $i){ // 上限数まで結果表示されること
                $response->assertSeeText($delete_bookdatas[$i]->title); // タイトルが反映されていること
            } else { // 上限超過データは扱われていないこと
                $response->assertDontSeeText($delete_bookdatas[$i]->title); // タイトルが反映されていないこと
            }
        }
    }
     // 複数削除エラー、所有者有り
     public function test_bookControll_ng_someDeleteHaveProperty()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // faker 自動生成
        $delete_bookdata = factory(Property::class)->create();
        // 送信フォームパス
        $formpath = 'book/somedelete';
        // 削除データ
        $deletedata = ['select_books' => [$delete_bookdata->bookdata_id]];

        //// 削除
        $response = $this->from($formpath)->post($formpath, $deletedata); // 削除実施
        $response->assertSessionHasNoErrors(); // エラーメッセージがないこと
        $response->assertStatus(200); // 200ステータスであること

        $response->assertSeeText('書籍削除結果'); // 登録結果ページが出力されていること
        $response->assertSeeText($delete_bookdata->bookdata->title); // タイトルが反映されていること
        $response->assertSeeText('所有者がいるため削除できません'); // 処理結果が反映されていること
    }
    // ページネーション表示、ページ超過
    public function test_bookControll_ng_paginationViewOverPage()
    {
        //// ユーザー生成
        $user = factory(User::class)->create(); // ユーザーを作成
        $this->actingAs($user); // ログイン済み
        $this->assertTrue(Auth::check()); // Auth認証済であることを確認

        // 件数設定
        $datarecords = 21; // テストレコード数

        // faker 自動生成
        $books = factory(Bookdata::class, $datarecords)->create();

        // index表示パス
        $viewpath = 'book?page=';
        // 表示ページ
        $page = 3;

        $response = $this->get($viewpath.$page); // 各ページへアクセス
        $response->assertStatus(200); // 200ステータスであること

        // 表示ページにデータが出力されていないこと
        foreach($books as $book){
            $response->assertDontSeeText($book->title); // 対象ページに表示されていないこと
        }
    }
}
