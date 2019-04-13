<div class="book-table">
  @if (isset($books))
    @foreach ($books as $book)
      <div class="book-table__list">
        <div class="book-table__list--picture">
          <a href="/{{$page_path}}/{{$book->id}}">
            @if (isset($book->picture))
              <img src="{{$book->picture}}">
            @elseif (isset($book->cover))
              <img src="{{$book->cover}}">
            @else
              <img src="../image/no-entry.jpg">
            @endif
          </a>
        </div>
        <div class="book-table__list--detail">
          <h3 class="book-title">{{$book->title}}</h3>
        </div>
      </div>
    @endforeach
  @endif
</div>
