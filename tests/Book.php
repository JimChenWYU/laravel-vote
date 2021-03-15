<?php

namespace JimChen\LaravelVote\Tests;

use Illuminate\Database\Eloquent\Model;
use JimChen\LaravelVote\Traits\Votable;

class Book extends Model
{
    use Votable;

    protected $fillable = ['title'];
}
