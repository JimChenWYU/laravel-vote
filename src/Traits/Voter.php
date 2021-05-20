<?php

namespace JimChen\LaravelVote\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JimChen\LaravelVote\Vote;
use JimChen\LaravelVote\VoteItems;

/**
 * Trait Voter
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Voter
{
    /**
     * @param Model  $object
     * @param string $type
     * @return \JimChen\LaravelVote\Vote
     * @throws \JimChen\LaravelVote\Exceptions\UnexpectValueException
     */
    public function vote(Model $object, string $type): Vote
    {
        $type = (string)new VoteItems($type);

        return $type === VoteItems::UP ? $this->upVote($object) : $this->downVote($object);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $object
     *
     * @return bool
     */
    public function hasVoted(Model $object, ?string $type = null): bool
    {
        return ($this->relationLoaded('votes') ? $this->votes : $this->votes())
                ->where('votable_id', $object->getKey())
                ->where('votable_type', $object->getMorphClass())
                ->when(\is_string($type), function ($builder) use ($type) {
                    $builder->where('vote_type', (string)new VoteItems($type));
                })
                ->count() > 0;
    }

    /**
     * @param Model $object
     * @return bool
     * @throws \Exception
     */
    public function cancelVote(Model $object): bool
    {
        /* @var \JimChen\LaravelVote\Vote $relation */
        $relation = \app(\config('vote.vote_model'))
            ->where('votable_id', $object->getKey())
            ->where('votable_type', $object->getMorphClass())
            ->where(\config('vote.user_foreign_key'), $this->getKey())
            ->first();

        if ($relation) {
	        $this->unsetRelation('votes');

            return $relation->delete();
        }

        return true;
    }

    /**
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(\config('vote.vote_model'), \config('vote.user_foreign_key'), $this->getKeyName());
    }

    /**
     * Get Query Builder for votes
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getVotedItems(string $model, ?string $type = null)
    {
        return \app($model)->whereHas(
            'voters',
            function ($builder) use ($type) {
                return $builder->where(\config('vote.user_foreign_key'), $this->getKey())->when(
                    \is_string($type),
                    function ($builder) use ($type) {
                        $builder->where('vote_type', (string)new VoteItems($type));
                    }
                );
            }
        );
    }

    public function upVote(Model $object): Vote
    {
        /* @var Votable|Model $object */
        if ($this->hasVoted($object)) {
            $this->cancelVote($object);
        }

        $vote = app(config('vote.vote_model'));
        $vote->{config('vote.user_foreign_key')} = $this->getKey();
        $vote->vote_type = VoteItems::UP;
        $object->votes()->save($vote);

        return $vote;
    }

    public function downVote(Model $object): Vote
    {
        /* @var Votable|Model $object */
        if ($this->hasVoted($object)) {
            $this->cancelVote($object);
        }

        $vote = app(config('vote.vote_model'));
        $vote->{config('vote.user_foreign_key')} = $this->getKey();
        $vote->vote_type = VoteItems::DOWN;
        $object->votes()->save($vote);

        return $vote;
    }

    public function hasUpVoted(Model $object)
    {
        return $this->hasVoted($object, VoteItems::UP);
    }

    public function hasDownVoted(Model $object)
    {
        return $this->hasVoted($object, VoteItems::DOWN);
    }

    public function toggleUpVote(Model $object)
    {
        return $this->hasUpVoted($object) ? $this->cancelVote($object) : $this->upVote($object);
    }

    public function toggleDownVote(Model $object)
    {
        return $this->hasDownVoted($object) ? $this->cancelVote($object) : $this->downVote($object);
    }

    public function getUpVotedItems(string $model)
    {
        return $this->getVotedItems($model, VoteItems::UP);
    }

    public function getDownVotedItems(string $model)
    {
        return $this->getVotedItems($model, VoteItems::DOWN);
    }

    public function attachVoteStatusToVotables(Collection $votables)
    {
        $voterVoted = $this->votes()->get()->keyBy(function ($item) {
            return \sprintf('%s-%s', $item->votable_type, $item->votable_id);
        });

        $votables->map(function (Model $votable) use ($voterVoted) {
            $key = \sprintf('%s-%s', $votable->getMorphClass(), $votable->getKey());
            $votable->setAttribute('has_voted', $voterVoted->has($key));
            $votable->setAttribute('has_up_voted', $voterVoted->has($key) && $voterVoted->get($key)->is_up_voted);
            $votable->setAttribute('has_down_voted', $voterVoted->has($key) && $voterVoted->get($key)->is_down_voted);
        });
    }
}
