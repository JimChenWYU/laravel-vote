# Laravel Vote System

[![Test Status](https://github.com/JimChenWYU/laravel-vote/workflows/Test/badge.svg)](https://github.com/JimChenWYU/laravel-vote/actions)
[![Check & fix styling](https://github.com/JimChenWYU/laravel-vote/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/JimChenWYU/laravel-vote/actions/workflows/php-cs-fixer.yml)
[![Latest Stable Version](https://poser.pugx.org/jimchen/laravel-vote/v/stable.svg)](https://packagist.org/packages/jimchen/laravel-vote)
[![License](https://poser.pugx.org/jimchen/laravel-vote/license)](https://packagist.org/packages/jimchen/laravel-vote)

:tada: This package helps you to add user based vote system to your model.

> fork from [jcc/laravel-vote](https://github.com/jcc/laravel-vote)

## Installation

You can install the package using Composer:

```sh
$ composer require "jimchen/laravel-vote"
```

Then add the service provider to `config/app.php`:

```php
JimChen\LaravelVote\VoteServiceProvider::class
```

Publish the migrations file:

```sh
$ php artisan vendor:publish --provider="JimChen\LaravelVote\VoteServiceProvider" --tag="migrations"
```

Finally, use VoteTrait in User model:

```php
use JimChen\LaravelVote\Traits\Voter;

class User extends Model
{
    use Voter;
}
```

Or use CanBeVoted in Comment model:

```php
use JimChen\LaravelVote\Traits\Votable;

class Comment extends Model
{
    use Votable;
}
```

## Usage

### For User model

#### Up vote a comment or comments

```php
$comment = Comment::find(1);

$user->upVote($comment);
```

### Down vote a comment or comments

```php
$comment = Comment::find(1);

$user->downVote($comment);
```

#### Cancel vote a comment or comments

```php
$comment = Comment::find(1);

$user->cancelVote($comment);
```

#### Get user has voted comment items

```php
$user->getVotedItems(Comment::class)->get();
```

#### Check if user has up or down vote

```php
$comment = Comment::find(1);

$user->hasVoted($comment);
```

#### Check if user has up vote

```php
$comment = Comment::find(1);

$user->hasUpVoted($comment);
```

#### Check if user has down vote

```php
$comment = Comment::find(1);

$user->hasDownVoted($comment);
```

### For Comment model

#### Get comment voters

```php
$comment->voters()->get();
```

#### Count comment voters

```php
$comment->voters()->count();
```

#### Get comment up voters

```php
$comment->upVoters()->get();
```

#### Count comment up voters

```php
$comment->upVoters()->count();
```

#### Get comment down voters

```php
$comment->downVoters()->get();
```

#### Count comment down voters

```php
$comment->downVoters()->count();
```

#### Check if voted by

```php
$user = User::find(1);

$comment->isVotedBy($user);
```

#### Check if up voted by

```php
$user = User::find(1);

$comment->isUpVotedBy($user);
```

#### Check if down voted by

```php
$user = User::find(1);

$comment->isDownVotedBy($user);
```

### N+1 issue

To avoid the N+1 issue, you can use eager loading to reduce this operation to just 2 queries. When querying, you may specify which relationships should be eager loaded using the `with` method:

```php
// Voter
$users = User::with('votes')->get();

foreach($users as $user) {
    $user->hasVoted($comment);
}

// Votable
$comments = Comment::with('voters')->get();

foreach($comments as $comment) {
    $comment->isVotedBy($user);
}
```

### Events

| **Event** | **Description** |
| --- | --- |
|  `JimChen\LaravelVote\Events\Voted` | Triggered when the relationship is created or updated. |
|  `JimChen\LaravelVote\Events\CancelVoted` | Triggered when the relationship is deleted. |


## Reference

- [laravel-follow](https://github.com/overtrue/laravel-follow)
- [laravel-like](https://github.com/overtrue/laravel-like)

## License

[MIT](LICENSE)
