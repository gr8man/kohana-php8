# Kohana Modern

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-BSD-brightgreen.svg)](http://kohanaframework.org/license)

A modernized fork of the Kohana framework, ensuring full support for PHP 8.3+ while preserving the elegant HMVC architecture developers love.

> **Note**: This is an active modernization project. The original Kohana framework development has ceased, making this fork essential for projects requiring PHP 8.3 compatibility and modern security standards.

## About

Kohana is an elegant, open source, and object oriented HMVC framework built using PHP5. This modernized version maintains the framework's core philosophy—swift, secure, and small—while updating it to meet contemporary web development requirements.

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | **8.3.0+** |
| PHPUnit | 9.6+ |
| Extensions | PDO, mbstring, hash |

## Installation

```bash
# Clone the repository
git clone https://github.com/gr8man/kohana-php8.git
cd kohana-php8

# Install dependencies
composer install

# Run tests to verify installation
./vendor/bin/phpunit
```

## Quick Start

```php
// application/bootstrap.php
Kohana::init([
    'base_url'   => '/',
    'index_file' => FALSE,
]);

// Initialize cookie security
Cookie::init();

Route::set('default', '(<controller>(/<action>(/<id>)))')
    ->defaults(['controller' => 'welcome']);
```

## Test Suite

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suites
./vendor/bin/phpunit --testsuite="Migration"
./vendor/bin/phpunit --testsuite="System"
./vendor/bin/phpunit --testsuite="Modules"

# Run security-specific tests
./vendor/bin/phpunit system/tests/kohana/SQLInjectionTest.php
./vendor/bin/phpunit system/tests/kohana/CookieSecurityTest.php

# Run Apache simulation tests (CLI environment)
./vendor/bin/phpunit system/tests/kohana/ApacheSimulationTest.php
```

## What's New / Migration

### PHP 8.3 Compatibility

- **Exception Handling**: Updated to accept `Throwable` for proper error handling in PHP 8.x
- **Iterator Interfaces**: Fixed `Countable`, `Iterator`, `ArrayAccess`, and `SeekableIterator` with `#[ReturnTypeWillChange]` attributes
- **ArrayAccess Signatures**: Updated method signatures in `HTTP_Header`, `Validation`, and `Config_Group` to match PHP 8 standards
- **Deprecated Functions Removed**: Eliminated `get_magic_quotes_gpc()` and `each()` usage

### PHPUnit 9/10 Compatibility

- **Assertion Updates**: Replaced `assertRegExp` with `assertMatchesRegularExpression`
- **Exception Annotations**: Migrated from `@expectedException` to `expectException()` methods
- **Reflection Fixes**: Updated property access for PHP 8.x compatibility
- **Timezone Updates**: Fixed deprecated IANA timezone names

### Security Enhancements

| Vulnerability | Fix |
|--------------|-----|
| **CVE-2019-8979** (SQL Injection) | Added strict validation for `order_by()` direction parameter |
| **Password Storage** | Implemented bcrypt hashing with `Auth::hash_password()` |
| **Cookie Security** | HTTP-only, SameSite=Lax enabled by default; SHA-256 for cookie signing |
| **CSRF Protection** | `hash_equals()` for timing-safe comparisons |
| **XSS Prevention** | Fixed `Security::strip_image_tags()` to properly encode URLs |

### Code Modernization

- **Constructor Property Promotion**: Applied to `Database_Expression`, `Log_File`, `Log_Syslog`, and `Config_File_Reader`
- **Match Expressions**: Updated `Text::random()` for cleaner control flow
- **Strict Types**: Added `declare(strict_types=1)` to all core classes
- **Type Safety**: Fixed type casting issues in `Profiler`, `File`, and UTF8 functions

## Configuration

### Cookie Security (Recommended)

```php
// application/config/cookie.php
return [
    'salt'     => 'your-long-random-string-at-least-32-chars',
    'httponly' => TRUE,      // Prevents JavaScript access
    'samesite' => 'Lax',    // CSRF protection
    'secure'   => TRUE,      // Set TRUE for HTTPS
];
```

### Auth with bcrypt

```php
// application/config/auth.php
return [
    'bcrypt_cost' => 12,    // Recommended: 10-12 for security/performance
];
```

### Password Migration

Existing SHA256 users can be migrated to bcrypt:

```php
if ($auth->needs_rehash($user->password)) {
    $user->password = $auth->hash_password($plaintext);
    $user->save();
}
```

## Documentation

Official documentation is available at [kohanaframework.org](http://kohanaframework.org/documentation).

For local documentation, enable the `userguide` module in `bootstrap.php`:

```php
Kohana::modules([
    'auth'       => MODPATH.'auth',
    'database'   => MODPATH.'database',
    'userguide' => MODPATH.'userguide',
]);
```

Access via `/guide` or `/index.php/guide` (depending on URL rewriting).

## Contributing

Found a bug or have a fix? We welcome contributions:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure all tests pass before submitting:

```bash
./vendor/bin/phpunit
```

## License

Kohana is released under the [BSD license](http://kohanaframework.org/license). This allows you to use it legally for any open source, commercial, or personal project.

## Acknowledgments

- Original Kohana Team for creating an elegant PHP framework
- Contributors to this modernization effort

---

**Status**: Actively Maintained | **Last Updated**: 2026 | **PHP**: 8.3+
