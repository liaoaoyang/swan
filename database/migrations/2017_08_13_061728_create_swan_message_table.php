<?php

use App\Swan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwanMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Swan::TABLE_SWAN_MESSAGE, function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('openid', 128)->index();
            $table->string('text', 255);
            $table->text('desp');
            $table->tinyInteger('status')->default(App\Models\SwanMessageModel::STATUS_CREATE);
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
        //
        //Schema::dropIfExists('swan_message');
    }
}
