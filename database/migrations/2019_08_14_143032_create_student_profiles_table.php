<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('school_id');
            $table->bigInteger('parent_profile_id');
            $table->enum('availability', ['available', 'notavailable'])->default('notavailable');
            $table->decimal('rating', 3, 2)->default('0.00');
            $table->integer('level')->default(1);
            $table->String('teacher')->nullable();
            $table->integer('classes_passed')->default(0);
            $table->integer('total_time')->default(0);
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
        Schema::dropIfExists('student_profiles');
    }
}
