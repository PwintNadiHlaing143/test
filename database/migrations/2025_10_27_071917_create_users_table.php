<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('users', function (Blueprint $table) {
      $table->bigIncrements('user_id'); // primary key
      $table->string('user_name', 100);
      $table->string('phone_number', 15);
      $table->string('user_password', 255);
      $table->text('user_address');
      $table->integer('current_bottles')->default(0);
      $table->decimal('change_return', 10, 2)->default(0.0);
      $table->integer('empty_collected')->default(0);
      $table->boolean('is_active')->default(true);
      $table->softDeletes(); // Adds deleted_at column


      $table->unsignedBigInteger('township_id');

      $table->foreign('township_id')
        ->references('township_id')
        ->on('townships')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};