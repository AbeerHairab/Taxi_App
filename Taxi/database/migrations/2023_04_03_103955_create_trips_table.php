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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->float('estimatedCost')->default(0);
            $table->float('realCost')->default(0);
            $table->string('estimatedDuration');
            $table->string('realDuration')->nullable();
            $table->string('from');
            $table->string('to');
            $table->integer('NumOfSeats')->nullable();
            $table->integer('bookedSeats')->nullable();
            $table->integer('availableSeats')->nullable();
            $table->boolean('rejected')->default(0);
            $table->boolean('accepted')->default(0);
            $table->char('status')->default('d');
            $table->boolean('paid')->default(0);
            $table->dateTime('startTime')->default('2023-08-28 11:28:44');
            $table->dateTime('endTime')->default(now());
            $table->boolean('visible')->default(true);
            //$table->string('addressNow');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trips');
    }
};
