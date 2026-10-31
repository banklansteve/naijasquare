<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('house_number')->nullable()->after('password');
            $table->string('postcode')->nullable()->after('house_number');
            $table->string('address_line')->nullable()->after('postcode');
            $table->string('place_label')->nullable()->after('address_line');
            $table->string('city')->nullable()->after('place_label');
            $table->string('life_stage')->nullable()->after('city');
            $table->json('interests')->nullable()->after('life_stage');
            $table->string('photo_path')->nullable()->after('interests');
            $table->timestamp('terms_accepted_at')->nullable()->after('photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'house_number',
                'postcode',
                'address_line',
                'place_label',
                'city',
                'life_stage',
                'interests',
                'photo_path',
                'terms_accepted_at',
            ]);
        });
    }
};
