<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //
    public $table = 'banner';

    public function item()
    {
        return $this->hasMany(Item::class,'banner_id','id');
    }
}
