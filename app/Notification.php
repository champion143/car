<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $table = 'notificaion';

    public function user()
    {
        return $this->belongsTo('App\User','sender_id','id');
    }
}
