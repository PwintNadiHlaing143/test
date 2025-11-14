<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('townships', function (Blueprint $table) {
      $table->bigIncrements('township_id'); // primary key
      $table->string('township_name', 100);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('townships');
  }
};
