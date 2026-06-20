<?php

namespace App\Services;

use App\Models\ExtensionSetting;
use RuntimeException;

class ExtensionConfigSigner
{
    public function sign(array $payload, ExtensionSetting $setting): array
    {
        $secret = $setting->signing_secret_key;
        $public = $setting->signing_public_key;

        if ($secret === null || $public === null) {
            return ['algorithm' => null, 'signature' => null, 'publicKey' => null];
        }

        if (!function_exists('openssl_sign')) {
            throw new RuntimeException('PHP OpenSSL extension is required for extension config signing.');
        }

        $privateKey = openssl_pkey_get_private($secret);
        if ($privateKey === false) throw new RuntimeException('Stored extension signing private key is invalid.');
        if (!openssl_sign($this->canonicalJson($payload), $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign extension config.');
        }

        return [
            'algorithm' => 'RS256',
            'signature' => base64_encode($signature),
            'publicKey' => $public,
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

}
