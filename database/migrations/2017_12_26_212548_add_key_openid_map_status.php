<?php

use App\Swan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKeyOpenidMapStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Swan::TABLE_SWAN_KEY_OPENID_MAP, function (Blueprint $table) {
            $table->tinyInteger('status')->default(App\Models\SwanKeyOpenidMapModel::STATUS_ENABLED);;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(Swan::TABLE_SWAN_KEY_OPENID_MAP, function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });
    }
}
