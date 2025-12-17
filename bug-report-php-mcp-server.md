# Bug Report: PHP 8.4 Compatibility Issue in FileCache

## Description
Fatal error occurs when running php-mcp/server on PHP 8.4 due to incompatible method signature in the `FileCache` class.

## Environment
- **PHP Version**: 8.4
- **Package**: php-mcp/server ^1.0
- **PSR Package**: psr/simple-cache

## Error Message
```
Fatal error: Declaration of PhpMcp\Server\Defaults\FileCache::get(string $key, mixed $default = null): mixed 
must be compatible with Psr\SimpleCache\CacheInterface::get($key, $default = null) 
in vendor/php-mcp/server/src/Defaults/FileCache.php on line 38
```

## Root Cause
The `FileCache::get()` method has explicit type declarations (`string $key, mixed $default = null): mixed`) that don't match the PSR-16 SimpleCache interface signature (`$key, $default = null`). PHP 8.4 has stricter type checking for interface implementations and considers this a compatibility violation.

## Expected Behavior
The `FileCache` class should implement the `Psr\SimpleCache\CacheInterface` without causing compatibility errors on PHP 8.4.

## Proposed Solution
Remove explicit type hints from `FileCache::get()` to match the interface signature exactly, or update the interface implementation to be compatible with PHP 8.4's stricter requirements.

## Impact
This prevents php-mcp/server (and any packages depending on it) from running on PHP 8.4.

## Workaround
Use PHP 8.2 or 8.3 until this issue is resolved.
