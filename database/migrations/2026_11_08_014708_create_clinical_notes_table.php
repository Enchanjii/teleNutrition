<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dietitian_id')->constrained('users')->restrictOnDelete();
            $table->date('note_date');
            $table->text('assessment');
            $table->string('diagnosis_term');
            $table->text('etiology');
            $table->text('signs_symptoms');
            $table->text('intervention');
            $table->text('monitoring_evaluation');
            $table->timestamps();

            $table->index(['patient_id', 'note_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
