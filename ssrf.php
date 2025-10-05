<?php
// Fixed SSRF vulnerability with proper input validation and sanitization

// Get and validate the URL parameter
$url = $_GET['url'] ?? '';

// Validate that URL is not empty
if (empty($url)) {
    http_response_code(400);
    die('Error: URL parameter is required');
}

// Parse the URL to validate its components
$parsed_url = parse_url($url);

// Check if URL parsing was successful
if ($parsed_url === false) {
    http_response_code(400);
    die('Error: Invalid URL format');
}

// Only allow http and https protocols (block file://, ftp://, etc.)
$allowed_schemes = ['http', 'https'];
$scheme = $parsed_url['scheme'] ?? '';

if (!in_array(strtolower($scheme), $allowed_schemes)) {
    http_response_code(400);
    die('Error: Only HTTP and HTTPS protocols are allowed');
}

// Whitelist of allowed domains (example - adjust as needed)
$allowed_domains = [
    'example.com',
    'www.example.com',
    'trusted-site.com'
];

$host = $parsed_url['host'] ?? '';

if (!in_array(strtolower($host), $allowed_domains)) {
    http_response_code(403);
    die('Error: Domain not in whitelist');
}

// Additional security: prevent redirects to private IP ranges
// Note: This check may need to be adjusted based on your use case
// In some environments, DNS resolution may fail or return unexpected results
$ip = @gethostbyname($host);
if ($ip !== $host && filter_var($ip, FILTER_VALIDATE_IP)) {
    // Only check if we got a valid IP back
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        http_response_code(403);
        die('Error: Cannot redirect to private or reserved IP addresses');
    }
}

// Sanitize and reconstruct the URL to prevent injection
$safe_url = $scheme . '://' . $host;
if (isset($parsed_url['port'])) {
    $safe_url .= ':' . $parsed_url['port'];
}
if (isset($parsed_url['path'])) {
    $safe_url .= $parsed_url['path'];
}
if (isset($parsed_url['query'])) {
    $safe_url .= '?' . $parsed_url['query'];
}

// Perform the redirect with the sanitized URL
header('Location: ' . $safe_url);
exit();
?>
