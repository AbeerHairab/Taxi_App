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
        Schema::create('trip_drivers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('driver_id')->references('id')->on('users');
            $table->foreignId('trip_id')->references('id')->on('trips');
            $table->char('acceptOrReject');
            $table->boolean('visible')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trip_drivers');
    }
};
