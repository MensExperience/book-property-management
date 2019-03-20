<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bookdata extends Model
{
  protected $table = 'bookdata';
  protected $guarded = array('id');
  public static $rules = array(
    'title' => 'required',
    'picture' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:2048'
  );
}
