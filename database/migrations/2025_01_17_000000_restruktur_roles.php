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
        // Langkah 1: Ubah enum di kolom roles terlebih dahulu agar nilai baru diizinkan
        Schema::table('users', function (Blueprint $table) {
            $table->enum('roles', ['sekretaris', 'direktur', 'kepalausaha', 'admin', 'user'])->change();
        });

        // Langkah 2: Update nilai roles di tabel users: sekretaris -> admin, kepalausaha -> user
        DB::table('users')->where('roles', 'sekretaris')->update(['roles' => 'admin']);
        DB::table('users')->where('roles', 'kepalausaha')->update(['roles' => 'user']);
        
        // Langkah 3: Hapus data pengguna dengan role direktur
        DB::table('users')->where('roles', 'direktur')->delete();

        // Langkah 4: Ubah enum ke nilai final (hanya admin dan user)
        Schema::table('users', function (Blueprint $table) {
            $table->enum('roles', ['admin', 'user'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: ubah kembali ke enum lama
        Schema::table('users', function (Blueprint $table) {
            $table->enum('roles', ['sekretaris', 'direktur', 'kepalausaha'])->change();
        });

        // Restore data (hanya untuk admin dan user, direktur tidak bisa dikembalikan)
        DB::table('users')->where('roles', 'admin')->update(['roles' => 'sekretaris']);
        DB::table('users')->where('roles', 'user')->update(['roles' => 'kepalausaha']);
    }
};
