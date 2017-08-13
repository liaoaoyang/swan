<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class SwanMessageMongoModel extends Model
{
    //
    protected $collection = 'swan_message';

    public $timestamps = [
        'created_at',
        'updated_at',
    ];
}
