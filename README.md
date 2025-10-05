# ssrf

## SSRF Vulnerability Fix

This repository demonstrates and fixes a Server-Side Request Forgery (SSRF) vulnerability in PHP.

### Vulnerability

The original code had a critical SSRF vulnerability:
```php
header('location:file://'.$_GET['url']);
```

This allowed attackers to:
- Access local files using `file://` protocol
- Scan internal network using various protocols
- Perform open redirect attacks
- Access private services

### Security Fix

The fixed version (`ssrf.php`) implements multiple security layers:

1. **Protocol Validation**: Only allows `http` and `https` protocols
2. **Domain Whitelist**: Restricts redirects to pre-approved domains
3. **Private IP Protection**: Prevents access to private/reserved IP ranges
4. **Input Validation**: Validates URL format and components
5. **URL Sanitization**: Reconstructs URL from validated components

### Configuration

Update the `$allowed_domains` array in `ssrf.php` with your trusted domains:

```php
$allowed_domains = [
    'example.com',
    'www.example.com',
    'trusted-site.com'
];
```

### Testing

Run the included test suite to verify the security fixes:

```bash
php test_ssrf_fix.php
```

The test validates that:
- ✅ Malicious protocols (file://, ftp://, gopher://) are blocked
- ✅ Private IP ranges are blocked
- ✅ Non-whitelisted domains are blocked
- ✅ Empty or invalid URLs are rejected
- ✅ Legitimate whitelisted domains are allowed

### Security Best Practices

1. Always validate and sanitize user input
2. Use whitelists instead of blacklists
3. Restrict protocols to only what's necessary
4. Block access to private IP ranges
5. Log and monitor redirect attempts
6. Implement rate limiting to prevent abuse