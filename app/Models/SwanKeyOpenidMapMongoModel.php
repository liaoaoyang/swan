<?php

namespace App\Models;

use App\Swan;
use Jenssegers\Mongodb\Eloquent\Model;

class SwanKeyOpenidMapMongoModel extends Model
{
    //
    protected $collection = Swan::TABLE_SWAN_KEY_OPENID_MAP;

    public $timestamps = [
        'created_at',
        'updated_at',
    ];
}
