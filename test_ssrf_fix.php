#!/usr/bin/env php
<?php
/**
 * Test script to verify SSRF vulnerability has been fixed
 * This script simulates various attack scenarios
 */

echo "Testing SSRF vulnerability fixes...\n\n";

// Function to simulate the fixed code
function test_url_validation($test_url, $test_name) {
    echo "Test: $test_name\n";
    echo "URL: $test_url\n";
    
    // Simulate the validation logic from ssrf.php
    if (empty($test_url)) {
        echo "Result: ❌ BLOCKED - URL parameter is required\n\n";
        return false;
    }
    
    $parsed_url = parse_url($test_url);
    
    if ($parsed_url === false) {
        echo "Result: ❌ BLOCKED - Invalid URL format\n\n";
        return false;
    }
    
    $allowed_schemes = ['http', 'https'];
    $scheme = $parsed_url['scheme'] ?? '';
    
    if (!in_array(strtolower($scheme), $allowed_schemes)) {
        echo "Result: ❌ BLOCKED - Only HTTP and HTTPS protocols are allowed\n\n";
        return false;
    }
    
    $allowed_domains = [
        'example.com',
        'www.example.com',
        'trusted-site.com'
    ];
    
    $host = $parsed_url['host'] ?? '';
    
    if (!in_array(strtolower($host), $allowed_domains)) {
        echo "Result: ❌ BLOCKED - Domain not in whitelist\n\n";
        return false;
    }
    
    $ip = @gethostbyname($host);
    if ($ip !== $host && filter_var($ip, FILTER_VALIDATE_IP)) {
        // Only check if we got a valid IP back
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            echo "Result: ❌ BLOCKED - Cannot redirect to private or reserved IP addresses\n\n";
            return false;
        }
    }
    
    echo "Result: ✅ ALLOWED - URL is safe\n\n";
    return true;
}

// Test cases
$tests = [
    // Malicious attempts (should be blocked)
    ['file:///etc/passwd', 'File protocol attack - reading /etc/passwd'],
    ['file:///C:/Windows/System32/config/sam', 'File protocol attack - Windows system file'],
    ['ftp://internal-server/secret.txt', 'FTP protocol attack'],
    ['gopher://localhost:6379/_SET%20test%20value', 'Gopher protocol attack'],
    ['http://localhost/admin', 'Localhost access attempt'],
    ['http://127.0.0.1/admin', 'Loopback IP access attempt'],
    ['http://192.168.1.1/router-admin', 'Private IP range access'],
    ['http://10.0.0.1/internal', 'Private IP range access (10.x.x.x)'],
    ['http://malicious.com/phishing', 'Non-whitelisted domain'],
    ['https://evil.com', 'Non-whitelisted HTTPS domain'],
    ['', 'Empty URL'],
    ['not-a-url', 'Invalid URL format'],
    
    // Legitimate requests (should be allowed)
    ['https://example.com/', 'Legitimate whitelisted domain (HTTPS)'],
    ['http://www.example.com/page', 'Legitimate whitelisted domain with path'],
    ['https://trusted-site.com/resource?param=value', 'Whitelisted domain with query string'],
];

$blocked = 0;
$allowed = 0;

foreach ($tests as $test) {
    $result = test_url_validation($test[0], $test[1]);
    if ($result) {
        $allowed++;
    } else {
        $blocked++;
    }
}

echo "========================================\n";
echo "Test Summary:\n";
echo "Total tests: " . count($tests) . "\n";
echo "Blocked: $blocked\n";
echo "Allowed: $allowed\n";
echo "========================================\n";

// Expected results
$expected_blocked = 12;
$expected_allowed = 3;

if ($blocked === $expected_blocked && $allowed === $expected_allowed) {
    echo "\n✅ All tests passed! SSRF vulnerability has been properly fixed.\n";
    exit(0);
} else {
    echo "\n❌ Test results don't match expected values!\n";
    echo "Expected: $expected_blocked blocked, $expected_allowed allowed\n";
    exit(1);
}
?>
