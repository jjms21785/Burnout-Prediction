<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename columns to match the new format
        // For SQLite, we need to use raw SQL as SQLite doesn't support ALTER COLUMN RENAME
        if (DB::getDriverName() === 'sqlite') {
            // Check if table exists and what columns it has
            if (!Schema::hasTable('assessments')) {
                // Table doesn't exist, create it with new format directly
                Schema::create('assessments', function (Blueprint $table) {
                    $table->id();
                    $table->string('name')->nullable();
                    $table->integer('age')->nullable();
                    $table->string('sex')->nullable();
                    $table->string('college')->nullable();
                    $table->string('year')->nullable();
                    $table->text('answers')->nullable();
                    $table->integer('Exhaustion')->nullable();
                    $table->integer('Disengagement')->nullable();
                    $table->string('Burnout_Category')->nullable();
                    $table->string('ip_address')->nullable();
                    $table->text('user_agent')->nullable();
                    $table->float('confidence')->nullable();
                    $table->timestamps();
                });
                return;
            }
            
            // SQLite requires recreating the table with new column names
            // Drop table if exists
            Schema::dropIfExists('assessments_new');
            
            // Check what columns exist in assessments table
            $columns = DB::select("PRAGMA table_info(assessments)");
            $columnNames = array_column($columns, 'name');
            
            // Determine which column format we're working with
            $hasOldColumns = in_array('gender', $columnNames) || in_array('program', $columnNames);
            
            DB::statement('
                CREATE TABLE assessments_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT,
                    age INTEGER,
                    sex TEXT,
                    college TEXT,
                    year TEXT,
                    answers TEXT,
                    Exhaustion INTEGER,
                    Disengagement INTEGER,
                    Burnout_Category TEXT,
                    ip_address TEXT,
                    user_agent TEXT,
                    confidence REAL,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
            
            if ($hasOldColumns) {
                // Table has old format columns (gender, program, etc.) - convert them
                DB::statement('
                    INSERT INTO assessments_new 
                    (id, name, age, sex, college, year, answers, Exhaustion, Disengagement, Burnout_Category, ip_address, user_agent, confidence, created_at, updated_at)
                    SELECT 
                        id, name, age, gender as sex, program as college, year_level as year, 
                        answers, exhaustion_score as Exhaustion, disengagement_score as Disengagement, 
                        overall_risk as Burnout_Category, ip_address, user_agent, confidence, created_at, updated_at
                    FROM assessments
                ');
            } else {
                // Table already has new format columns (sex, college, etc.) - just copy data
                DB::statement('
                    INSERT INTO assessments_new 
                    (id, name, age, sex, college, year, answers, Exhaustion, Disengagement, Burnout_Category, ip_address, user_agent, confidence, created_at, updated_at)
                    SELECT 
                        id, name, age, sex, college, year, 
                        answers, Exhaustion, Disengagement, 
                        Burnout_Category, ip_address, user_agent, confidence, created_at, updated_at
                    FROM assessments
                ');
            }
            
            DB::statement('DROP TABLE assessments');
            DB::statement('ALTER TABLE assessments_new RENAME TO assessments');
        } else {
            // For other databases (MySQL, PostgreSQL), use standard ALTER TABLE
            Schema::table('assessments', function (Blueprint $table) {
                $table->renameColumn('gender', 'sex');
                $table->renameColumn('program', 'college');
                $table->renameColumn('year_level', 'year');
                $table->renameColumn('exhaustion_score', 'Exhaustion');
                $table->renameColumn('disengagement_score', 'Disengagement');
                $table->renameColumn('overall_risk', 'Burnout_Category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the column renames
        if (DB::getDriverName() === 'sqlite') {
            // SQLite reverse migration - drop table if exists
            Schema::dropIfExists('assessments_old');
            
            // Check what columns exist in assessments table
            $columns = DB::select("PRAGMA table_info(assessments)");
            $columnNames = array_column($columns, 'name');
            
            // Determine which column format we're working with
            $hasNewColumns = in_array('sex', $columnNames) || in_array('college', $columnNames);
            
            DB::statement('
                CREATE TABLE assessments_old (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT,
                    age INTEGER,
                    gender TEXT,
                    program TEXT,
                    year_level TEXT,
                    answers TEXT,
                    exhaustion_score INTEGER,
                    disengagement_score INTEGER,
                    overall_risk TEXT,
                    ip_address TEXT,
                    user_agent TEXT,
                    confidence REAL,
                    created_at DATETIME,
                    updated_at DATETIME
                )
            ');
            
            if ($hasNewColumns) {
                // Table has new format columns (sex, college, etc.)
                DB::statement('
                    INSERT INTO assessments_old 
                    (id, name, age, gender, program, year_level, answers, exhaustion_score, disengagement_score, overall_risk, ip_address, user_agent, confidence, created_at, updated_at)
                    SELECT 
                        id, name, age, sex as gender, college as program, year as year_level, 
                        answers, Exhaustion as exhaustion_score, Disengagement as disengagement_score, 
                        Burnout_Category as overall_risk, ip_address, user_agent, confidence, created_at, updated_at
                    FROM assessments
                ');
            } else {
                // Table already has old format columns (gender, program, etc.) - just copy data
                DB::statement('
                    INSERT INTO assessments_old 
                    (id, name, age, gender, program, year_level, answers, exhaustion_score, disengagement_score, overall_risk, ip_address, user_agent, confidence, created_at, updated_at)
                    SELECT 
                        id, name, age, gender, program, year_level, 
                        answers, exhaustion_score, disengagement_score, 
                        overall_risk, ip_address, user_agent, confidence, created_at, updated_at
                    FROM assessments
                ');
            }
            
            DB::statement('DROP TABLE assessments');
            DB::statement('ALTER TABLE assessments_old RENAME TO assessments');
        } else {
            // For other databases
            Schema::table('assessments', function (Blueprint $table) {
                $table->renameColumn('sex', 'gender');
                $table->renameColumn('college', 'program');
                $table->renameColumn('year', 'year_level');
                $table->renameColumn('Exhaustion', 'exhaustion_score');
                $table->renameColumn('Disengagement', 'disengagement_score');
                $table->renameColumn('Burnout_Category', 'overall_risk');
            });
        }
    }
};
