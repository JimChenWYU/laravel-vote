<?php

return array(
    /*
     * Table name for vote records.
     */
    'votes_table' => 'votes',

    /*
     * User tables foreign key name.
     */
    'user_foreign_key' => 'user_id',

    /*
     * Model name for Vote record.
     */
    'vote_model' => \JimChen\LaravelVote\Vote::class,
);
