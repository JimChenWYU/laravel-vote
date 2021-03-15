<?php

namespace JimChen\LaravelVote\Tests;

use Illuminate\Database\Eloquent\Model;
use JimChen\LaravelVote\Traits\Votable;

class Post extends Model
{
    use Votable;

    protected $fillable = ['title'];
}
