<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTutorProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutor_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('identity_id');
            $table->bigInteger('w9form_id');
            $table->bigInteger('payment_id')->nullable();
            $table->bigInteger('billing_id')->nullable();
            $table->enum('availability', ['available', 'notavailable'])->default('notavailable');
            $table->decimal('rating', 3, 2)->default('0.00');
            $table->integer('level')->default(1);
            $table->integer('classes_passed')->default(0);
            $table->integer('total_time')->default(0);
            $table->enum('payment_type', ['paypal', 'credit_card', 'code'])->nullable();
            $table->enum('billing_type', ['paypal', 'credit_card', 'code'])->nullable();
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
        Schema::dropIfExists('tutor_profiles');
    }
}
