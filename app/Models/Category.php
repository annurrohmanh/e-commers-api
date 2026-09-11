<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Wajib untuk otomatisasi UUID
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; // Wajib untuk softDeletes()

class Category extends Model
{
    use HasUuids, SoftDeletes;

    // Menentukan nama tabel jika tidak mengikuti aturan jamak (plural) Laravel
    protected $table = 'categories';

    // Karena primary key menggunakan UUID (string), bukan auto-increment integer
    protected $keyType = 'string';
    public $incrementing = false;

    // Kolom yang boleh diisi secara massal (mass assignment)
    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * Relasi ke model Catalogue (One-to-Many)
     * Satu kategori memiliki banyak produk di katalog.
     */
    public function catalogues(): HasMany
    {
        return $this->hasMany(Catalogue::class, 'category_id', 'id');
    }
}