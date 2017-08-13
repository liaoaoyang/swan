<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class SwanKeyOpenidMapMongoModel extends Model
{
    //
    protected $collection = 'swan_key_openid_map';

    public $timestamps = [
        'created_at',
        'updated_at',
    ];
}
