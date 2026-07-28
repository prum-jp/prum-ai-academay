<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_unit_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quest_unit_id')->constrained()->cascadeOnDelete();
            $table->string('stat', 40);
            $table->unsignedTinyInteger('points');
            $table->timestamps();

            $table->unique(['quest_unit_id', 'stat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_unit_rewards');
    }
};
