<?php

namespace App\Services;

use App\Models\ExtensionSetting;
use RuntimeException;

class ExtensionConfigSigner
{
    public function sign(array $payload, ExtensionSetting $setting): array
    {
        $secret = $this->decodeKey($setting->signing_secret_key);
        $public = $this->decodeKey($setting->signing_public_key);

        if ($secret === null || $public === null) {
            return ['algorithm' => null, 'signature' => null, 'publicKey' => null];
        }

        if (strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new RuntimeException('Stored extension signing secret key is invalid.');
        }

        if (strlen($public) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new RuntimeException('Stored extension signing public key is invalid.');
        }

        $signature = sodium_crypto_sign_detached($this->canonicalJson($payload), $secret);

        return [
            'algorithm' => 'Ed25519',
            'signature' => base64_encode($signature),
            'publicKey' => base64_encode($public),
        ];
    }

    private function canonicalJson(array $payload): string
    {
        $sort = function (&$value) use (&$sort): void {
            if (!is_array($value)) return;
            foreach ($value as &$child) $sort($child);
            unset($child);
            if (!array_is_list($value)) ksort($value);
        };

        $sort($payload);

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function decodeKey(?string $key): ?string
    {
        if (!$key) return null;
        $decoded = base64_decode($key, true);
        if ($decoded === false) throw new RuntimeException('Extension signing keys must use valid base64.');
        return $decoded;
    }
}
