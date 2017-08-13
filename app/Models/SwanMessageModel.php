<?php

namespace App\Models;

class SwanMessageModel implements MultiDrivers
{
    const STATUS_CREATE = 1;

    public static function createModel()
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        if ('mysql' == $dbConnection) {
            return new \App\Models\SwanMessageMySQLModel();
        } else if ('mongodb' == $dbConnection) {
            return new \App\Models\SwanMessageMongoModel();
        }

        return null;
    }
}
