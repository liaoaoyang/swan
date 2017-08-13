<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwanKeyOpenidMapMySQLModel extends Model
{
    //
    protected $table = 'swan_key_openid_map';

    public $timestamps = [
        'created_at',
        'updated_at',
    ];
}
