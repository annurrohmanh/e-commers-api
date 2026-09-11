<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Wajib untuk otomatisasi UUID
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes; // Wajib untuk softDeletes()

class Catalogue extends Model
{
    use HasUuids, SoftDeletes;

    // Menentukan nama tabel sesuai dengan migration Anda
    protected $table = 'catalogue';

    // Konfigurasi primary key UUID
    protected $keyType = 'string';
    public $incrementing = false;

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'weight',
        'description',
        'image',
        'price',
        'stock',
        'reserved_stock',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}