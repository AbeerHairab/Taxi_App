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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('customer_id')->references('id')->on('users');
            $table->softDeletes();
            $table->float('estimatedCost')->nullable();
            $table->float('realCost')->default(0);
            $table->float('estimatedDuration')->nullable();
            $table->float('realDuration')->default(0);
            $table->string('from');
            $table->string('to');
            $table->boolean('accepted')->default(0);
            $table->boolean('rejected')->default(0);
            $table->boolean('paid')->default(0);
            $table->char('status')->default('d');
            $table->dateTime('startTime')->nullable();
            $table->dateTime('endTime')->nullable();
            $table->string('addressNow')->nullable();
            //$table->bool('visible')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
