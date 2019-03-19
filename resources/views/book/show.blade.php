@extends('layouts.layout')

@section('title', 'ShowPage')

@section('stylesheet')
  <link href="/css/sidebar.css" rel="stylesheet" type="text/css">
  <link href="/css/book-index.css" rel="stylesheet" type="text/css">
@endsection

@section('content')
  <div class="index-content">
    <!-- サイドバー(コンポーネント) -->
    @component('components.sidebar')
    @endcomponent
    <div class="books-list">
      <div class="books-list__title">
        詳細ページ
      </div>
      <div class="book-table">
        <table class="book-table__list">
          <tr>
            <td>id</td>
            <td>写真</td>
            <td>タイトル</td>
            <td>登録日</td>
          </tr>
          <tr>
            <td>{{$book->id}}</td>
            <td>{{$book->picture}}</td>
            <td>{{$book->title}}</td>
            <td>{{$book->created_at}}</td>
          </tr>
        </table>
      </div>
      <a href="/book/{{$book->id}}/edit">編集</a>
      ・
      <a href="">削除</a>
    </div>
  </div>
@endsection