<?php

namespace App\Models;

use App\Swan;
use Illuminate\Database\Eloquent\Model;

class SwanKeyOpenidMapMySQLModel extends Model
{
    //
    protected $table = Swan::TABLE_SWAN_KEY_OPENID_MAP;

    public $timestamps = [
        'created_at',
        'updated_at',
    ];
}
