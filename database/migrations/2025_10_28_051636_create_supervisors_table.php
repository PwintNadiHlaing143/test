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
    Schema::create('supervisors', function (Blueprint $table) {
      $table->bigIncrements('supervisor_id'); // primary key
      $table->string('supervisor_name', 100);
      $table->string('supervisor_phone', 15);
      $table->string('supervisor_password', 100);
      $table->text('supervisor_address');
      $table->boolean('supervisor_status')->default(true);

      $table->unsignedBigInteger('owner_id');

      $table->foreign('owner_id')
        ->references('owner_id')
        ->on('owner')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('supervisors');
  }
};
