<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Lokasi;
use App\Models\Satuan;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::create([
            'name'      => 'Dwi Purnomo',
            'email'     => 'purnomodwi174@gmail.com',
            'password'  => bcrypt('1234'),
            'roles'     => 'admin'
        ]);

        User::create([
            'name'      => 'Galang Adi Trianto',
            'email'     => 'wartabolanet@gmail.com',
            'password'  => bcrypt('1234'),
            'roles'     => 'user'
        ]);

                User::create([
            'name'      => 'Muh.Akmal Fahim',
            'email'     => 'akmlfhm@gmail.com',
            'password'  => bcrypt('1234'),
            'roles'     => 'user'
        ]);

        Kategori::create([
            'nama'      => 'Elektronik',
            'deskripsi' => 'Deskripsi dari kategori elektronik',
            'user_id'   => 1
        ]);

        Kategori::create([
            'nama'      => 'Mable',
            'deskripsi' => 'Kategori mable',
            'user_id'   => 1
        ]);

        Kategori::create([
            'nama'      => 'Furniture',
            'deskripsi' => 'Kategori furniture',
            'user_id'   => 1
        ]);

        Kategori::create([
            'nama'      => 'Alat makan',
            'deskripsi' => 'Kategori alat makan',
            'user_id'   => 1
        ]);


        Lokasi::create([
            'nama_lokasi'   => 'MPI',
            'deskripsi'     => 'MPI Lantai 4',
            'user_id'       => 1
        ]);

        Lokasi::create([
            'nama_lokasi'   => 'R.Rapat Lantai 2',
            'deskripsi'     => 'Ruang Rapat Lantai 2',
            'user_id'       => 1
        ]);

        Lokasi::create([
            'nama_lokasi'   => 'R.PWA Lantai 2',
            'deskripsi'     => 'Ruang PWA Lantai 2',
            'user_id'       => 1
        ]);

        Lokasi::create([
            'nama_lokasi'   => 'R.Kerja Lantai 2',
            'deskripsi'     => 'Ruang Kerja Lantai 2',
            'user_id'       => 1
        ]);

        Lokasi::create([
            'nama_lokasi'   => 'R.ketua',
            'deskripsi'     => 'Ruang Ketua',
            'user_id'       => 1
        ]);

        Lokasi::create([
            'nama_lokasi'   => 'R.Tamu Ketua',
            'deskripsi'     => 'Ruang Tamu Ketua',
            'user_id'       => 1
        ]);

        Lokasi::create([
            'nama_lokasi'   => 'R.Tamu Lantai 2',
            'deskripsi'     => 'Ruang Tamu Lantai 2',
            'user_id'       => 1
        ]);

        Lokasi::create([
            'nama_lokasi'   => 'R.Gudang',
            'deskripsi'     => 'Ruang Gudang',
            'user_id'       => 1
        ]);

        Lokasi::create([
            'nama_lokasi'   => 'KTAM lantai 1',
            'deskripsi'     => 'KTAM Lantai 1',
            'user_id'       => 1
        ]);
        
        Satuan::create([
            'nama'      => 'Unit',
            'deskripsi' => 'Deskripsi dari satuan unit',
            'user_id'   => 1
        ]);
    }
}
