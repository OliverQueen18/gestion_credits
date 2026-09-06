<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->after('name');
            $table->string('telephone', 30)->nullable()->after('email');
        });

        User::query()->orderBy('id')->each(function (User $user): void {
            $base = Str::lower((string) Str::of($user->email)->before('@')->replaceMatches('/[^a-z0-9._-]/', ''));
            if ($base === '') {
                $base = 'user'.$user->id;
            }

            $username = $base;
            $suffix = 1;
            while (User::query()->where('username', $username)->where('id', '!=', $user->id)->exists()) {
                $username = $base.$suffix;
                $suffix++;
            }

            $user->forceFill(['username' => $username])->save();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
            $table->unique('telephone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropUnique(['telephone']);
            $table->dropColumn(['username', 'telephone']);
        });
    }
};
