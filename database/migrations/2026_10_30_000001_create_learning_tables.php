<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('parent');
            $t->boolean('active')->default(true);
            $t->string('google_id')->nullable()->unique();
            $t->text('bio')->nullable();
            $t->string('subjects')->nullable();
            $t->string('photo')->nullable();
            $t->boolean('public_profile')->default(false);
            $t->boolean('is_demo')->default(false);
        });
        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('grade');
            $t->string('school')->nullable();
            $t->boolean('active')->default(true);
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('guardians', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->string('email')->index();
            $t->unique(['student_id', 'email']);
        });
        Schema::create('assignments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('type')->default('primary');
            $t->timestamp('ended_at')->nullable();
            $t->timestamps();
        });
        Schema::create('reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->date('lesson_date');
            $t->string('subject')->nullable();
            $t->text('material')->nullable();
            $t->text('mastered')->nullable();
            $t->text('practice')->nullable();
            $t->string('understanding')->nullable();
            $t->string('concentration')->nullable();
            $t->string('problem_solving')->nullable();
            $t->text('notes')->nullable();
            $t->string('status')->default('draft');
            $t->string('grade_snapshot')->nullable();
            $t->string('school_snapshot')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->unsignedInteger('revision')->default(0);
            $t->timestamps();
        });
        Schema::create('attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('report_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->string('name');
            $t->unsignedInteger('size');
            $t->timestamps();
        });
        Schema::create('publications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('report_id')->constrained()->cascadeOnDelete();
            $t->uuid('token')->unique();
            $t->unsignedInteger('revision');
            $t->timestamps();
        });
        Schema::create('deliveries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('publication_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->timestamp('sent_at')->nullable();
            $t->unique(['publication_id', 'user_id']);
            $t->timestamps();
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->json('value');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['settings', 'deliveries', 'publications', 'attachments', 'reports', 'assignments', 'guardians', 'students'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'active', 'google_id', 'bio', 'subjects', 'photo', 'public_profile', 'is_demo']));
    }
};
