<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table): void {
            $table->string('code', 40)->nullable()->after('id');
        });

        foreach (DB::table('tools')->orderBy('id')->get(['id']) as $tool) {
            DB::table('tools')
                ->where('id', $tool->id)
                ->update(['code' => 'tool-'.$tool->id]);
        }

        Schema::table('tools', function (Blueprint $table): void {
            $table->string('code', 40)->nullable(false)->change();
            $table->unique('code');
        });
    }
};
