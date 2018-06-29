<?php

use App\Swan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSwanMessageRequestIp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(Swan::TABLE_SWAN_MESSAGE, function (Blueprint $table) {
            $table->string('request_ip')->default('')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(Swan::TABLE_SWAN_MESSAGE, function (Blueprint $table) {
            $table->dropColumn('request_ip');
        });
    }
}
