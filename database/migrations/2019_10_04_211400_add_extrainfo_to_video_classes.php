<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtrainfoToVideoClasses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('video_classes', function (Blueprint $table) {
            //
            $table->dateTime('ended_at')->nullable();
            $table->integer('price_id')->nullable();
            $table->boolean('paid')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('video_classes', function (Blueprint $table) {
            //
        });
    }
}
