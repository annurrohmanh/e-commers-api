<?php

namespace App\Services\Tokens;

use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use JsonException; // Ditambahkan untuk catch error JSON jika dibutuhkan

abstract class RedisTokenService
{
    abstract protected function getPrefix(): string;
    abstract protected function getIndexPrefix(): string;
    abstract protected function getTtl(): int;

    public function __construct(
        private readonly RedisFactory $redis,
    ) {}

    protected function connection()
    {
        return $this->redis->connection('default');
    }

    protected function storeToken(string $key, array $payload): string
    {
        $token = Str::random(64);
        $hash  = hash('sha256', $token);

        $indexKey = $this->getIndexPrefix() . $key;

        // Hapus token lama jika ada
        if ($oldHash = $this->connection()->get($indexKey)) {
            $this->connection()->del($this->getPrefix() . $oldHash);
        }

        // Amankan dengan menyimpan identifier key asli ke dalam payload
        $payload['_index_key'] = $key; 

        $this->connection()->setex(
            $this->getPrefix() . $hash,
            $this->getTtl(),
            json_encode($payload)
        );

        $this->connection()->setex(
            $indexKey,
            $this->getTtl(),
            $hash
        );

        return $token;
    }

    protected function consumeToken(string $token): array
    {
        $hash = hash('sha256', $token);
        $key  = $this->getPrefix() . $hash;

        $payload = $this->connection()->get($key);

        if (! $payload) {
            throw ValidationException::withMessages([
                'token' => ['Token expired or invalid.'],
            ]);
        }

        $data = json_decode($payload, true);

        // Ambil key index yang asli dari payload, fallback ke token jika tidak ada
        $indexIdentifier = $data['_index_key'] ?? $token;

        // Hapus token setelah digunakan (one-time use)
        $this->connection()->del($key);
        $this->connection()->del($this->getIndexPrefix() . $indexIdentifier);

        // Hapus meta data internal sebelum mengembalikan payload ke service utama
        unset($data['_index_key']); 

        return $data;
    }

    // RedisTokenService.php — tambah method ini
    protected function peekToken(string $token): array
    {
        $hash    = hash('sha256', $token);
        $key     = $this->getPrefix() . $hash;
        $payload = $this->connection()->get($key);

        if (! $payload) {
            throw ValidationException::withMessages([
                'token' => ['Token expired or invalid.'],
            ]);
        }

        $data = json_decode($payload, true);
        unset($data['_index_key']);

        return $data;
    }
}