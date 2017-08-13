<?php

namespace App\Models;

class SwanKeyOpenidMapModel implements MultiDrivers
{
    public static function createModel()
    {
        $dbConnection = env('DB_CONNECTION', 'mysql');

        if ('mysql' == $dbConnection) {
            return new \App\Models\SwanKeyOpenidMapMySQLModel();
        } else if ('mongodb' == $dbConnection) {
            return new \App\Models\SwanKeyOpenidMapMongoModel();
        }

        return null;
    }
}
