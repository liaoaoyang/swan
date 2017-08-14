<?php

namespace App\Models;

use App\Swan;
use Jenssegers\Mongodb\Eloquent\Model;

class SwanMessageMongoModel extends Model
{
    //
    protected $collection = Swan::TABLE_SWAN_MESSAGE;

    public $timestamps = [
        'created_at',
        'updated_at',
    ];
}
