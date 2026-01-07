<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $_ENV['AWS_DEFAULT_REGION'],
    'endpoint' => $_ENV['AWS_ENDPOINT'],
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
    ],
]);

$folder = date('Y/m');
$filename = 'test_u_antigravity_' . time() . '.txt';
$key = "$folder/$filename";

echo "Uploading test file to: $key ...\n";

try {
    $result = $s3->putObject([
        'Bucket' => $_ENV['AWS_BUCKET'],
        'Key'    => $key,
        'Body'   => 'This confirms Antigravity can upload to Year/Month folder.',
        'ACL'    => 'public-read',
    ]);

    echo "✅ Success!\n";
    echo "URL: " . $result['ObjectURL'] . "\n";
    
    // Verify listing
    echo "\nVerifying file in S3 list...\n";
    $objects = $s3->listObjectsV2([
        'Bucket' => $_ENV['AWS_BUCKET'],
        'Prefix' => $folder
    ]);
    
    foreach ($objects['Contents'] as $object) {
        if ($object['Key'] === $key) {
            echo "👀 FOUND in list: {$object['Key']}\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
