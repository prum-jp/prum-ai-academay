<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('background')->nullable()->comment('前職/バックグラウンド');
            $table->string('hobby')->nullable()->comment('趣味');
            $table->text('weapon_skill')->nullable()->comment('【武器】今できること');
            $table->text('spell_goal')->nullable()->comment('【呪文】習得したいスキル');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
