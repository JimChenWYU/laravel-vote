<?php

namespace JimChen\LaravelVote\Tests;

use Illuminate\Database\Eloquent\Model;
use JimChen\LaravelVote\Traits\Votable;
use JimChen\LaravelVote\Traits\Voter;

class User extends Model
{
    use Voter;
    use Votable;

    protected $fillable = ['name'];
}
