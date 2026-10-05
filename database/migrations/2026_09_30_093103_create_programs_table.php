<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('education_requirement', ['none', 'secondary_professional', 'higher'])->default('none');
            $table->enum('retraining_period', ['none', '1_year', '3_years', '5_years', 'custom'])->default('none');
            $table->integer('custom_months')->nullable();
            $table->string('duration')->nullable();
            $table->enum('status', ['active', 'archive'])->default('active');
            $table->integer('notify_days_before')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
