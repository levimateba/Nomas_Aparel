<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            return;
        }

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number', 40)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->string('photo')->nullable();
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('national_id', 80)->nullable();
            $table->string('kra_pin', 80)->nullable();
            $table->string('nhif_number', 80)->nullable();
            $table->string('nssf_number', 80)->nullable();
            $table->string('phone', 80)->nullable();
            $table->string('alt_phone', 80)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postal_code', 40)->nullable();
            $table->string('department')->nullable();
            $table->string('job_title')->nullable();
            $table->string('employment_type', 40)->nullable(); // full_time, part_time, contract, casual
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->decimal('basic_salary', 12, 2)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account', 80)->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 80)->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
