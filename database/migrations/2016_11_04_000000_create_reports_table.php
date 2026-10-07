<?php

/*
 * This file is part of Laravel Reportable.
 *
 * (c) Brian Faust <hello@brianfaust.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->morphs('reportable');
            $table->morphs('reporter');
            $table->text('reason');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('reports_conclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->index();
            $table->morphs('judge');
            $table->text('conclusion');
            $table->text('action_taken');
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports_conclusions');
        Schema::dropIfExists('reports');
    }
};
