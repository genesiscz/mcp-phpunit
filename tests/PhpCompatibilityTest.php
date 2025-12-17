<?php

declare(strict_types=1);

namespace PhpMcp\Phpunit\Tests;

use PhpMcp\Phpunit\ExecutionResult;
use PhpMcp\Phpunit\McpPhpunitServer;
use PhpMcp\Phpunit\OutputFormatter;
use PhpMcp\Phpunit\Parsers\JunitXmlParser;
use PhpMcp\Phpunit\Parsers\TestdoxParser;
use PhpMcp\Phpunit\PhpunitExecutor;
use PHPUnit\Framework\TestCase;

/**
 * Test PHP compatibility for versions 8.2+, including 8.3 and 8.4
 * 
 * While the project requires PHP 8.2+, these tests validate that PHP 8.0+ and 8.1+
 * features (which are available in PHP 8.2+) work correctly and that all core
 * classes can be instantiated in the supported PHP versions.
 */
class PhpCompatibilityTest extends TestCase
{
    public function testPhpVersionIsSupported(): void
    {
        $version = PHP_VERSION;
        $this->assertGreaterThanOrEqual('8.2.0', $version, 'PHP version must be 8.2 or higher');
        $this->assertLessThan('9.0.0', $version, 'PHP version must be less than 9.0');
    }

    public function testRequiredExtensionsAreLoaded(): void
    {
        $this->assertTrue(extension_loaded('dom'), 'ext-dom is required');
        $this->assertTrue(extension_loaded('json'), 'ext-json is required');
        $this->assertTrue(extension_loaded('mbstring'), 'ext-mbstring is required');
    }

    public function testCoreClassesCanBeInstantiated(): void
    {
        // Test ExecutionResult with constructor property promotion (PHP 8.0+)
        $result = new ExecutionResult(0, 'stdout', 'stderr', null, null);
        $this->assertInstanceOf(ExecutionResult::class, $result);
        $this->assertTrue($result->isSuccess());

        // Test PhpunitExecutor
        $executor = new PhpunitExecutor();
        $this->assertInstanceOf(PhpunitExecutor::class, $executor);

        // Test parsers
        $junitParser = new JunitXmlParser();
        $this->assertInstanceOf(JunitXmlParser::class, $junitParser);

        $testdoxParser = new TestdoxParser();
        $this->assertInstanceOf(TestdoxParser::class, $testdoxParser);

        // Test OutputFormatter
        $formatter = new OutputFormatter($testdoxParser);
        $this->assertInstanceOf(OutputFormatter::class, $formatter);

        // Test McpPhpunitServer
        $server = new McpPhpunitServer($executor, $junitParser, $testdoxParser, $formatter);
        $this->assertInstanceOf(McpPhpunitServer::class, $server);
    }

    public function testStringFunctions(): void
    {
        // Test str_starts_with (PHP 8.0+)
        $this->assertTrue(str_starts_with('hello world', 'hello'));
        $this->assertFalse(str_starts_with('hello world', 'world'));

        // Test str_ends_with (PHP 8.0+)
        $this->assertTrue(str_ends_with('hello world', 'world'));
        $this->assertFalse(str_ends_with('hello world', 'hello'));

        // Test str_contains (PHP 8.0+)
        $this->assertTrue(str_contains('hello world', 'o w'));
        $this->assertFalse(str_contains('hello world', 'xyz'));
    }

    public function testNullsafeOperator(): void
    {
        // Test nullsafe operator (PHP 8.0+)
        // When used on null, it returns null instead of throwing an error
        $obj = null;
        $result = $obj?->method();
        $this->assertNull($result);
    }

    public function testNamedArguments(): void
    {
        // Test named arguments (PHP 8.0+)
        $result = $this->helperWithNamedArguments(value: 42, name: 'test');
        $this->assertSame(['name' => 'test', 'value' => 42], $result);
    }

    public function testMatchExpression(): void
    {
        // Test match expression (PHP 8.0+)
        $value = 2;
        $result = match ($value) {
            1 => 'one',
            2 => 'two',
            3 => 'three',
            default => 'other',
        };
        $this->assertSame('two', $result);
    }

    public function testArrayUnpackingWithStringKeys(): void
    {
        // Test array unpacking with string keys (PHP 8.1+)
        $array1 = ['a' => 1, 'b' => 2];
        $array2 = ['c' => 3, 'd' => 4];
        $result = [...$array1, ...$array2];
        
        $this->assertArrayHasKey('a', $result);
        $this->assertArrayHasKey('b', $result);
        $this->assertArrayHasKey('c', $result);
        $this->assertArrayHasKey('d', $result);
    }

    /**
     * Test readonly properties (PHP 8.1+) via ExecutionResult
     */
    public function testReadonlyProperties(): void
    {
        $result = new ExecutionResult(0, 'out', 'err', 'xml', 'tdx');
        
        $this->assertSame(0, $result->exitCode);
        $this->assertSame('out', $result->stdout);
        $this->assertSame('err', $result->stderr);
        $this->assertSame('xml', $result->junitXml);
        $this->assertSame('tdx', $result->testdoxText);
    }

    private function helperWithNamedArguments(string $name, int $value): array
    {
        return ['name' => $name, 'value' => $value];
    }
}
