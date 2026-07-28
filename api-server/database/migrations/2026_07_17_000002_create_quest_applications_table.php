<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('applied')->comment('applied | approved | cancelled');
            $table->json('form_payload')->nullable()->comment('申込フォーム内容（将来用）');
            $table->timestamps();

            $table->unique(['quest_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_applications');
    }
};
