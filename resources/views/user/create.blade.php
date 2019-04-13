@extends('layouts.layout')

@section('title', 'CreateForm')

@section('stylesheet')
  <link href="/css/menulist.css" rel="stylesheet" type="text/css">
  <link href="/css/book-index.css" rel="stylesheet" type="text/css">
@endsection

@section('breadcrumbs')
  <div class="book-header__breadcrumbs">
    {{ Breadcrumbs::render('user.create') }}
  </div>
@endsection

@section('pagemenu')
  @include('components.menu_mypage')
@endsection

@section('content')
  <div class="index-content">
    <div class="books-list">
      <div class="books-list__title mypage-color">
        所有書籍登録
      </div>
      @if (isset($msg))
        <div class="books-list__msg">
            <span>{{$msg}}</span>
        </div>
      @endif
      <div class="book-new">
        <form action="/user" method="post" enctype="multipart/form-data">
          {{ csrf_field() }}
          <div class="form-contents">
            <div class="form-one-size">
              <div class="form-input">
                <select class="form-input__select" name="bookdata_id">
                @if (isset($books))
                  @foreach ($books as $book)
                    <option value="{{$book->id}}">{{$book->title}}</option>
                  @endforeach
                @endif
                </select>
              </div>
            </div>
          </div>
          <div class="form-foot">
            <input class="send" type="submit" value="登録">
          </div>
        </form>
      </div>
      @if (isset($property))
        <div>{{$property->bookdata->title}}</div>
      @endif
    </div>
  </div>
@endsection
