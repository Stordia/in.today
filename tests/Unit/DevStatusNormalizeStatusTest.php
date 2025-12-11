<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Tests for DevStatus normalizeFeatureStatus method.
 *
 * Ensures the method can handle both string and array inputs safely.
 */
class DevStatusNormalizeStatusTest extends TestCase
{
    /**
     * Helper to access the private normalizeFeatureStatus method via reflection.
     */
    private function normalizeFeatureStatus(string|array $status): string
    {
        $devStatus = new \App\Filament\Pages\DevStatus;
        $reflection = new \ReflectionClass($devStatus);
        $method = $reflection->getMethod('normalizeFeatureStatus');
        $method->setAccessible(true);

        return $method->invoke($devStatus, $status);
    }

    public function test_string_input_tested_ok(): void
    {
        $this->assertEquals('tested_ok', $this->normalizeFeatureStatus('tested_ok'));
    }

    public function test_string_input_in_progress(): void
    {
        $this->assertEquals('in_progress', $this->normalizeFeatureStatus('in_progress'));
    }

    public function test_string_input_planned(): void
    {
        $this->assertEquals('planned', $this->normalizeFeatureStatus('planned'));
    }

    public function test_string_input_with_spaces(): void
    {
        $this->assertEquals('in_progress', $this->normalizeFeatureStatus('  in_progress  '));
    }

    public function test_string_input_with_alternative_format(): void
    {
        $this->assertEquals('in_progress', $this->normalizeFeatureStatus('in progress'));
    }

    public function test_array_with_value_key(): void
    {
        $this->assertEquals('tested_ok', $this->normalizeFeatureStatus(['value' => 'tested_ok']));
    }

    public function test_array_with_status_key(): void
    {
        $this->assertEquals('in_progress', $this->normalizeFeatureStatus(['status' => 'in_progress']));
    }

    public function test_array_with_indexed_value(): void
    {
        $this->assertEquals('tested_ok', $this->normalizeFeatureStatus(['tested_ok']));
    }

    public function test_array_with_indexed_value_at_position_0(): void
    {
        $this->assertEquals('planned', $this->normalizeFeatureStatus([0 => 'planned']));
    }

    public function test_empty_array_returns_unknown(): void
    {
        $this->assertEquals('unknown', $this->normalizeFeatureStatus([]));
    }

    public function test_array_with_non_scalar_values_returns_unknown(): void
    {
        $this->assertEquals('unknown', $this->normalizeFeatureStatus([['nested' => 'array']]));
    }

    public function test_unknown_string_falls_back_to_default(): void
    {
        $this->assertEquals('in_progress', $this->normalizeFeatureStatus('some_unknown_status'));
    }

    public function test_string_input_blocked(): void
    {
        $this->assertEquals('blocked', $this->normalizeFeatureStatus('blocked'));
    }

    public function test_string_input_ready_for_tests(): void
    {
        $this->assertEquals('ready_for_tests', $this->normalizeFeatureStatus('ready_for_tests'));
    }
}
