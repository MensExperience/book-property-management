<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bookdata extends Model
{
  protected $table = 'bookdata';
  protected $guarded = array('id');
  public static $rules = array(
    'title' => 'required',
    'picture' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048'
  );
  public function properties()
  {
      return $this->hasMany('App\Property');
  }
  public static $isbnEntryRules = array(
    'isbn' => 'required|digits:13|unique:bookdata,isbn'
  );
  public static $searchRules = array(
    'find' => 'required'
  );
}
