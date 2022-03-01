<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsQueueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads_queue', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('lptracker_id');
            $table->integer('bitrix_id');
            $table->string('name');
            $table->string('phone');
            $table->integer('is_exported');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads_queue');
    }
}
