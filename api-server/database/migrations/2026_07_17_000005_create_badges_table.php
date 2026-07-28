<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique()->comment('バッジ識別子');
            $table->string('title')->comment('表示名');
            $table->string('description')->nullable();
            $table->string('icon')->comment('Font Awesome クラス');
            $table->string('unlock_type', 40)->default('quest_complete')->comment('quest_complete など');
            $table->foreignId('unlock_quest_id')->nullable()->constrained('quests')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
