<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /** 
     * Daftarkan semua resource yang ada di aplikasi Anda di sini
     * @var array<int, string> 
     */
    private array $resources = [
        'users',
        'catalogues',
        'categories',
    ];

    public function run(): void
    {
        // Wajib: Bersihkan cache Spatie agar permission baru langsung terbaca
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [];

        // Generate permission (view, create, edit, delete) secara otomatis untuk tiap resource
        foreach ($this->resources as $resource) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permission = Permission::query()->firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'api', // Sesuai dengan guard JWT / API Anda
                ]);
                $permissions[$resource][$action] = $permission;
            }
        }

        // Buat atau pastikan Role sudah ada
        $admin  = Role::query()->firstOrCreate(['name' => 'admin',  'guard_name' => 'api']);
        $seller = Role::query()->firstOrCreate(['name' => 'seller', 'guard_name' => 'api']);
        $buyer  = Role::query()->firstOrCreate(['name' => 'buyer',  'guard_name' => 'api']);

        // ==========================================
        // 1. PEMBAGIAN HAK AKSES ADMIN
        // ==========================================
        // Admin adalah superuser, berikan ALL permissions dari semua resource
        $admin->syncPermissions(collect($permissions)->flatten());

        // ==========================================
        // 2. PEMBAGIAN HAK AKSES SELLER
        // ==========================================
        $seller->syncPermissions([
            // Users: Hanya bisa melihat profil/data user lain (opsional)
            $permissions['users']['view'],

            // Catalogues: Full akses (bisa buat, edit, lihat, hapus katalog miliknya)
            $permissions['catalogues']['view'],
            $permissions['catalogues']['create'],
            $permissions['catalogues']['edit'],
            $permissions['catalogues']['delete'],

            // Categories: Hanya bisa melihat kategori yang ada, tidak bisa buat/ubah/hapus
            $permissions['categories']['view'],
        ]);

        // ==========================================
        // 3. PEMBAGIAN HAK AKSES BUYER
        // ==========================================
        $buyer->syncPermissions([
            // Users: Tidak punya akses ke manajemen users (sesuai rules awal Anda)
            
            // Catalogues & Categories: Hanya bisa MENGINTIP (view) daftar produk & kategori
            $permissions['catalogues']['view'],
            $permissions['categories']['view'],
        ]);
    }
}