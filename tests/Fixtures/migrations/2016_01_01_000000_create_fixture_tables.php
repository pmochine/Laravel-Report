<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id('member_id');
            $table->string('name');
        });

        Schema::create('uuid_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
        });

        Schema::create('uuid_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uuid_posts');
        Schema::dropIfExists('uuid_users');
        Schema::dropIfExists('members');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('users');
    }
};
