<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

  public function up(): void
  {
    Schema::create('delivery_group', function (Blueprint $table) {
      $table->bigIncrements('group_id'); // primary key
      $table->string('group_name', 100);
      $table->unsignedBigInteger('supervisor_id');

      $table->foreign('supervisor_id')
        ->references('supervisor_id')
        ->on('supervisors') // Changed from 'supervisor' to 'supervisors'
        ->onDelete('cascade')
        ->onUpdate('cascade');

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('delivery_group_status_to_delivery_group');
  }
};
