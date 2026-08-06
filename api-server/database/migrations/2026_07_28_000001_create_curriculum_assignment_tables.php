<?php

use App\Models\QuestUnit;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curricula', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('curriculum_quest_units', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('curriculum_id')->constrained('curricula')->cascadeOnDelete();
            $table->foreignId('quest_unit_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['curriculum_id', 'quest_unit_id']);
        });

        Schema::create('student_curriculum_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('curriculum_id')->constrained('curricula')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->unique(['user_id', 'curriculum_id']);
        });

        Schema::create('student_quest_unit_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->unique(['user_id', 'quest_unit_id']);
        });

        $this->migrateExistingPublishedUnits();
    }

    public function down(): void
    {
        Schema::dropIfExists('student_quest_unit_assignments');
        Schema::dropIfExists('student_curriculum_assignments');
        Schema::dropIfExists('curriculum_quest_units');
        Schema::dropIfExists('curricula');
    }

    private function migrateExistingPublishedUnits(): void
    {
        $studentIds = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->pluck('id');

        $publishedUnitIds = QuestUnit::query()->pluck('id');

        if ($studentIds->isEmpty() || $publishedUnitIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = [];

        foreach ($studentIds as $studentId) {
            foreach ($publishedUnitIds as $unitId) {
                $rows[] = [
                    'user_id' => $studentId,
                    'quest_unit_id' => $unitId,
                    'assigned_by' => null,
                    'assigned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('student_quest_unit_assignments')->insert($chunk);
        }
    }
};
