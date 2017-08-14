<?php

namespace App\Models;

use App\Swan;
use Illuminate\Database\Eloquent\Model;

class SwanMessageMySQLModel extends Model
{
    const STATUS_CREATE = 1;

    //
    protected $table = Swan::TABLE_SWAN_MESSAGE;

    public $timestamps = [
        'created_at',
        'updated_at',
    ];
}
