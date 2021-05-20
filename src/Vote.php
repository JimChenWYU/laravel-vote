<?php

namespace JimChen\LaravelVote;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;
use JimChen\LaravelVote\Events\CancelVoted;
use JimChen\LaravelVote\Events\Voted;

/**
 * Class Vote
 *
 * @property string $vote_type
 * @property string $votable_type
 */
class Vote extends Model
{
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => Voted::class,
        'deleted' => CancelVoted::class,
    ];

    protected $appends = [
        'is_up_vote',
        'is_down_vote',
    ];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = \config('vote.votes_table');

        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (Vote $vote) {
            $userForeignKey = \config('vote.user_foreign_key');
            $vote->{$userForeignKey} = $vote->{$userForeignKey} ?: Auth::id();
        });
    }

    public function votable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\config('auth.providers.users.model'), \config('vote.user_foreign_key'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function voter()
    {
        return $this->user();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithVotableType(Builder $query, string $type)
    {
        return $query->where('votable_type', \app($type)->getMorphClass());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithVoteType(Builder $query, string $type)
    {
        return $query->where('vote_type', (string)new VoteItems($type));
    }

    public function isUp(): bool
    {
        return $this->vote_type === VoteItems::UP;
    }

    public function isDown(): bool
    {
        return $this->vote_type === VoteItems::DOWN;
    }

    public function getIsUpVoteAttribute(): bool
    {
        return $this->isUp();
    }

    public function getIsDownVoteAttribute(): bool
    {
        return $this->isDown();
    }
}
