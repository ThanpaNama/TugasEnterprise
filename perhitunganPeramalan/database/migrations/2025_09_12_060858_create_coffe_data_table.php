<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coffe_data', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->datetime('datetime')->nullable();
            $table->string('cash_type', 50)->nullable();
            $table->string('card',100)->nullable();
            $table->decimal('moneyy', 10 , 2)->default(0);
            $table->string('coffe_name',100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coffe_data');
    }
};
