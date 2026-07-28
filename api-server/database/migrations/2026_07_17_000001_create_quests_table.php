<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quests', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->comment('件名');
            $table->text('description')->nullable()->comment('詳細');
            $table->string('type', 20)->comment('personal | team | special');
            $table->boolean('is_required')->default(true)->comment('必須クエストか');
            $table->unsignedTinyInteger('unlock_level')->nullable()->comment('解放に必要なレベル');
            $table->string('reward_text')->nullable()->comment('報酬表示テキスト');
            $table->string('badge_label')->nullable()->comment('タグ表示（例: 全員歓迎！）');
            $table->date('starts_at')->nullable()->comment('開始日');
            $table->date('ends_at')->nullable()->comment('期限日');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quests');
    }
};
