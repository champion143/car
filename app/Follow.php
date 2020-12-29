<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    //
    public $table = 'follow';

    public function followerUser()
    {
        return $this->belongsTo('App\User','following_id','id');
    }

    public function followingUser()
    {
        return $this->belongsTo('App\User','follower_id','id');
    }
}
