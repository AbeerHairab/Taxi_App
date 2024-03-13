<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('car_id')->references('id')->on('cars');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('photo');
            $table->float('evaluate')->default(0);
            $table->boolean('availability')->default(false);
            $table->integer('evaluateNum')->default(0);
            $table->string('drivingCertificate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers');
    }
};
