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

// 1. Upload simple file to root
$key1 = 'hello.txt';
echo "1. Uploading $key1...\n";
$s3->putObject([
    'Bucket' => $_ENV['AWS_BUCKET'],
    'Key'    => $key1,
    'Body'   => 'Hello World',
    'ACL'    => 'public-read',
]);

// 2. Upload file to folder
$key2 = '2026/01/hello.txt';
echo "2. Uploading $key2...\n";
$s3->putObject([
    'Bucket' => $_ENV['AWS_BUCKET'],
    'Key'    => $key2,
    'Body'   => 'Hello World in Folder',
    'ACL'    => 'public-read',
]);

echo "\n--- VERIFICATION ---\n";
$objects = $s3->listObjectsV2(['Bucket' => $_ENV['AWS_BUCKET']]);
foreach ($objects['Contents'] as $obj) {
    if ($obj['Key'] == $key1 || $obj['Key'] == $key2) {
        echo "FOUND Key: " . $obj['Key'] . "\n";
        
        // Generate Standard URL
        $url1 = $_ENV['AWS_ENDPOINT'] . '/' . $_ENV['AWS_BUCKET'] . '/' . $obj['Key'];
        echo "   URL 1 (Standard): $url1\n";
        
        // Generate URL without Bucket (Test)
        $url2 = $_ENV['AWS_ENDPOINT'] . '/' . $obj['Key'];
        echo "   URL 2 (No Bucket): $url2\n";
    }
}
