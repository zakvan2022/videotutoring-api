<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_classes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('tutor_profile_id');
            $table->string('name');
            $table->string('description');
            $table->dateTime('started_at')->nullable();
            $table->integer('duration')->default(0);
            $table->decimal('rating', 3, 2)->default('0.00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_classes');
    }
}
