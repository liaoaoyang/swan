<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwanKeyOpenidMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('swan_key_openid_map', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('key', 128)->unique();
            $table->string('openid', 128)->unique();
            $table->timestamps();
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('swan_key_openid_map');
    }
}
