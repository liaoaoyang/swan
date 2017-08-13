<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwanMessageModel extends Model
{
    const STATUS_CREATE = 1;

    //
    protected $table = 'swan_message';

    public $timestamps = [
        'created_at',
        'updated_at',
    ];
}
