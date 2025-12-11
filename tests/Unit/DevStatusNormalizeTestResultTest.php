<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Tests for DevStatus normalizeTestResult method.
 *
 * Ensures the method can handle string, array, and null inputs safely.
 */
class DevStatusNormalizeTestResultTest extends TestCase
{
    /**
     * Helper to access the private normalizeTestResult method via reflection.
     */
    private function normalizeTestResult(string|array|null $result): string
    {
        $devStatus = new \App\Filament\Pages\DevStatus;
        $reflection = new \ReflectionClass($devStatus);
        $method = $reflection->getMethod('normalizeTestResult');
        $method->setAccessible(true);

        return $method->invoke($devStatus, $result);
    }

    public function test_string_input_pass(): void
    {
        $this->assertEquals('pass', $this->normalizeTestResult('pass'));
    }

    public function test_string_input_fail(): void
    {
        $this->assertEquals('fail', $this->normalizeTestResult('fail'));
    }

    public function test_string_input_unknown(): void
    {
        $this->assertEquals('unknown', $this->normalizeTestResult('unknown'));
    }

    public function test_string_input_with_spaces(): void
    {
        $this->assertEquals('pass', $this->normalizeTestResult('  pass  '));
    }

    public function test_string_input_uppercase(): void
    {
        $this->assertEquals('fail', $this->normalizeTestResult('FAIL'));
    }

    public function test_string_input_passed_alias(): void
    {
        $this->assertEquals('pass', $this->normalizeTestResult('passed'));
    }

    public function test_string_input_failed_alias(): void
    {
        $this->assertEquals('fail', $this->normalizeTestResult('failed'));
    }

    public function test_empty_string_returns_unknown(): void
    {
        $this->assertEquals('unknown', $this->normalizeTestResult(''));
    }

    public function test_whitespace_only_string_returns_unknown(): void
    {
        $this->assertEquals('unknown', $this->normalizeTestResult('   '));
    }

    public function test_array_with_value_key(): void
    {
        $this->assertEquals('pass', $this->normalizeTestResult(['value' => 'pass']));
    }

    public function test_array_with_result_key(): void
    {
        $this->assertEquals('fail', $this->normalizeTestResult(['result' => 'fail']));
    }

    public function test_array_with_indexed_value(): void
    {
        $this->assertEquals('pass', $this->normalizeTestResult(['pass']));
    }

    public function test_array_with_indexed_value_at_position_0(): void
    {
        $this->assertEquals('unknown', $this->normalizeTestResult([0 => 'unknown']));
    }

    public function test_empty_array_returns_unknown(): void
    {
        $this->assertEquals('unknown', $this->normalizeTestResult([]));
    }

    public function test_array_with_non_scalar_values_returns_unknown(): void
    {
        $this->assertEquals('unknown', $this->normalizeTestResult([['nested' => 'array']]));
    }

    public function test_null_input_returns_unknown(): void
    {
        $this->assertEquals('unknown', $this->normalizeTestResult(null));
    }

    public function test_unknown_string_falls_back_to_default(): void
    {
        $this->assertEquals('unknown', $this->normalizeTestResult('some_random_status'));
    }
}
