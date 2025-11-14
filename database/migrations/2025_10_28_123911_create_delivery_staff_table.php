<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

  public function up(): void
  {
    Schema::create('delivery_staff', function (Blueprint $table) {
      $table->bigIncrements('staff_id'); // primary key
      $table->string('staff_name', 100);
      $table->string('staff_phone', 15);
      $table->string('staff_password', 100);
      $table->text('staff_address');
      $table->boolean('staff_status')->default(true);

      $table->unsignedBigInteger('group_id');

      $table->foreign('group_id')
        ->references('group_id')
        ->on('delivery_group')
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('delivery_staff');
  }
};