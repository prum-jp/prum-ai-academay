<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quest_id')->constrained()->cascadeOnDelete();
            $table->string('stat', 40)->comment('presentation など');
            $table->unsignedTinyInteger('points')->comment('加算ポイント');
            $table->timestamps();

            $table->unique(['quest_id', 'stat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_rewards');
    }
};
