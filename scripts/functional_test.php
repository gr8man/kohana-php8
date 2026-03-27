<?php
declare(strict_types=1);

$urls = [
    'http://localhost:8181/',
    'http://localhost:8181/welcome/index',
];

$all_passed = true;

foreach ($urls as $url) {
    echo "Testing $url...\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($info['http_code'] !== 200) {
        echo "FAIL: Expected 200, got {$info['http_code']}\n";
        $all_passed = false;
    } else {
        echo "PASS: Status 200\n";
    }

    $header_size = $info['header_size'];
    $body = substr($response, $header_size);

    $error_patterns = [
        'Fatal error',
        'Uncaught Error',
        'ErrorException',
        'Warning:',
        'Notice:',
        'Deprecated:'
    ];

    foreach ($error_patterns as $pattern) {
        if (stripos($body, $pattern) !== false) {
            echo "FAIL: Found error pattern '$pattern' in response body!\n";
            // echo "Body snippet: " . substr(strip_tags($body), 0, 200) . "...\n";
            $all_passed = false;
        }
    }
}

if ($all_passed) {
    echo "\nAll functional tests passed!\n";
} else {
    echo "\nSome functional tests failed!\n";
    exit(1);
}
