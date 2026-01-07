<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$accessKey = $_ENV['AWS_ACCESS_KEY_ID'] ?? null;
$secretKey = $_ENV['AWS_SECRET_ACCESS_KEY'] ?? null;
$bucket = $_ENV['AWS_BUCKET'] ?? null;
$endpoint = $_ENV['AWS_ENDPOINT'] ?? null;
$region = $_ENV['AWS_DEFAULT_REGION'] ?? 'us-east-1';

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $region,
    'endpoint' => $endpoint,
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => $accessKey,
        'secret' => $secretKey,
    ],
]);

echo "Searching for 'hoadao' in bucket '$bucket'...\n";

try {
    // List objects (simple loop, not recursive for full bucket if huge, but good for test)
    $objects = $s3->listObjectsV2([
        'Bucket' => $bucket
    ]);

    $found = false;
    if (isset($objects['Contents'])) {
        foreach ($objects['Contents'] as $object) {
            if (stripos($object['Key'], 'hoadao') !== false) {
                echo "🎯 FOUND: {$object['Key']}\n";
                echo "   URL: $endpoint/$bucket/{$object['Key']}\n";
                $found = true;
            }
        }
    } else {
        echo "Example file in bucket:\n";
        // Show first 5 files if not found
        $result = $s3->listObjectsV2(['Bucket' => $bucket, 'MaxKeys' => 5]);
        foreach ($result['Contents'] as $object) {
            echo " - {$object['Key']}\n";
        }
    }

    if (!$found) {
        echo "❌ 'hoadao' NOT FOUND.\n";
    }

} catch (AwsException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
