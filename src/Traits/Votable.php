<?php

namespace JimChen\LaravelVote\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use JimChen\LaravelVote\VoteItems;

/**
 * Trait Votable
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Votable
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return bool
     */
    public function isVotedBy(Model $user, ?string $type = null): bool
    {
        if (\is_a($user, \config('auth.providers.users.model'))) {
            if ($this->relationLoaded('voters')) {
                return $this->voters->when(\is_string($type), function ($votes) use ($type) {
                    /** @var \Illuminate\Database\Eloquent\Collection $votes */
                    return $votes->where('pivot.vote_type', '===', (string)new VoteItems($type));
                })->contains($user);
            }

            return $this->voters()
                ->where(\config('vote.user_foreign_key'), $user->getKey())
                ->when(\is_string($type), function ($builder) use ($type) {
                    $builder->where('vote_type', (string)new VoteItems($type));
                })
                ->exists();
        }

        return false;
    }

	public function votes(): \Illuminate\Database\Eloquent\Relations\MorphMany
	{
		return $this->morphMany(config('vote.vote_model'), 'votable');
	}

    /**
     * Return voters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function voters(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \config('auth.providers.users.model'),
            \config('vote.votes_table'),
            'votable_id',
            \config('vote.user_foreign_key')
        )
            ->withPivot(['vote_type'])
            ->where('votable_type', $this->getMorphClass());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return bool
     */
    public function isUpVotedBy(Model $user)
    {
        return $this->isVotedBy($user, VoteItems::UP);
    }

    /**
     * Return up voters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function upVoters(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->voters()->where('vote_type', VoteItems::UP);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $user
     *
     * @return bool
     */
    public function isDownVotedBy(Model $user)
    {
        return $this->isVotedBy($user, VoteItems::DOWN);
    }

    /**
     * Return down voters.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function downVoters(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->voters()->where('vote_type', VoteItems::DOWN);
    }

    public function totalVotes()
    {
        return $this->votes()->sum('votes');
    }

    public function totalUpVotes()
    {
        return $this->votes()->where('vote_type', VoteItems::UP)->sum('votes');
    }

    public function totalDownVotes()
    {
        return $this->votes()->where('vote_type', VoteItems::DOWN)->sum('votes');
    }

    public function scopeWithTotalVotes(Builder $builder)
    {
        return $builder->addSelect(
            DB::raw(sprintf(
                'cast(ifnull((select sum(`%s`.`votes`)
                                        from `votes` where %s = `%s`.`votable_id`
                                        and `%s`.`votable_type` = "%s"), 0) as SIGNED)
                            as `total_votes`',
                config('vote.votes_table'),
                $this->getTable() . '.' . $this->getKeyName(),
                config('vote.votes_table'),
                config('vote.votes_table'),
                $this->getTable()
            ))
        );
    }

    public function scopeWithTotalUpVotes(Builder $builder)
    {
        return $builder->addSelect(
            DB::raw(sprintf(
                'cast(ifnull((select sum(`%s`.`votes`)
                                        from `votes` where `vote_type` = "%s" and %s = `%s`.`votable_id`
                                        and `%s`.`votable_type` = "%s"), 0) as SIGNED)
                            as `total_votes`',
                config('vote.votes_table'),
                VoteItems::UP,
                $this->getTable() . '.' . $this->getKeyName(),
                config('vote.votes_table'),
                config('vote.votes_table'),
                $this->getTable()
            ))
        );
    }

    public function scopeWithTotalDownVotes(Builder $builder)
    {
        return $builder->addSelect(
            DB::raw(sprintf(
                'cast(ifnull((select sum(`%s`.`votes`)
                                        from `votes` where `vote_type` = "%s" and %s = `%s`.`votable_id`
                                        and `%s`.`votable_type` = "%s"), 0) as SIGNED)
                            as `total_votes`',
                config('vote.votes_table'),
                VoteItems::DOWN,
                $this->getTable() . '.' . $this->getKeyName(),
                config('vote.votes_table'),
                config('vote.votes_table'),
                $this->getTable()
            ))
        );
    }
}
