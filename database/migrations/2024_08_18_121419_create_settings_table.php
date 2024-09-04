<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {

        Schema::create('setting_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable();
            $table->enum('type', array_keys(\NinjaPortal\Admin\Constants::SETTING_TYPES))->default('string');
            $table->foreignId('setting_group_id')->nullable()->constrained('setting_groups')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_groups');
        Schema::dropIfExists('settings');
    }
};
