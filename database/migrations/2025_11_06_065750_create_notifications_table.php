<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

  public function up(): void
  {
    Schema::create('notifications', function (Blueprint $table) {
      $table->id('notification_id');

      // Foreign keys
      $table->unsignedBigInteger('user_id')->nullable();
      $table->unsignedBigInteger('supervisor_id')->nullable();


      $table->string('noti_title', 100);
      $table->text('noti_message');
      $table->dateTime('created_at')->useCurrent();
      $table->boolean('is_read')->default(false);

      $table->unsignedBigInteger('from')->nullable();
      $table->unsignedBigInteger('to')->nullable();
      $table->string('created_by', 10)->nullable();

      $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
      $table->foreign('supervisor_id')->references('supervisor_id')->on('supervisors')->onDelete('cascade');
    });
  }


  public function down(): void
  {
    Schema::dropIfExists('notifications');
  }
};