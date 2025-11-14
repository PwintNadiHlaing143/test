<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

  public function up(): void
  {
    Schema::create('fcm_tokens', function (Blueprint $table) {
      $table->id();


      $table->unsignedBigInteger('user_id')->nullable();
      $table->unsignedBigInteger('supervisor_id')->nullable();

      // Device information
      $table->text('device_token');
      $table->string('device_type')->nullable()->comment('android, ios, web');
      $table->string('device_name')->nullable();

      // Status
      $table->boolean('is_active')->default(true);

      // Timestamps
      $table->timestamps();


      $table->foreign('user_id')
        ->references('user_id')
        ->on('users')
        ->onDelete('cascade');

      $table->foreign('supervisor_id')
        ->references('supervisor_id')
        ->on('supervisors')
        ->onDelete('cascade');


      $table->index(['user_id', 'is_active']);
      $table->index(['supervisor_id', 'is_active']);
      $table->index('device_token');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('fcm_tokens');
  }
};
