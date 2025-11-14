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
    Schema::create('owner', function (Blueprint $table) {
      $table->bigIncrements('owner_id'); // primary key
      $table->string('owner_name', 100);
      $table->string('owner_phone', 15);
      $table->string('owner_password', 100);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('_owner');
  }
};