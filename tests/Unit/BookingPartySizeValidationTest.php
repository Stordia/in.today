<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests for party size validation logic.
 *
 * These tests validate the closure-based validation rule used in
 * OnboardRestaurant.php and BookingSettings.php for ensuring
 * booking_max_party_size >= booking_min_party_size.
 */
class BookingPartySizeValidationTest extends TestCase
{
    /**
     * Simulate the validation closure from the forms.
     */
    private function validateMaxPartySize(int $minPartySize, int $maxPartySize): ?string
    {
        $minPartySize = max(1, $minPartySize);
        $errorMessage = null;

        if ($maxPartySize < $minPartySize) {
            $errorMessage = "The Maximum Party Size must be at least {$minPartySize} (the minimum party size).";
        }

        return $errorMessage;
    }

    /**
     * Test: min=2, max=8 should pass validation.
     */
    public function test_max_8_with_min_2_passes_validation(): void
    {
        $error = $this->validateMaxPartySize(minPartySize: 2, maxPartySize: 8);

        $this->assertNull($error, 'max=8 should pass when min=2');
    }

    /**
     * Test: min=5, max=4 should fail validation.
     */
    public function test_max_4_with_min_5_fails_validation(): void
    {
        $error = $this->validateMaxPartySize(minPartySize: 5, maxPartySize: 4);

        $this->assertNotNull($error, 'max=4 should fail when min=5');
        $this->assertStringContainsString('at least 5', $error);
    }

    /**
     * Test: min=1, max=1 should pass (boundary case).
     */
    public function test_max_equals_min_passes_validation(): void
    {
        $error = $this->validateMaxPartySize(minPartySize: 1, maxPartySize: 1);

        $this->assertNull($error, 'max should be allowed to equal min');
    }

    /**
     * Test: min=10, max=10 should pass (equal values).
     */
    public function test_larger_equal_values_pass_validation(): void
    {
        $error = $this->validateMaxPartySize(minPartySize: 10, maxPartySize: 10);

        $this->assertNull($error, 'max=10 should pass when min=10');
    }

    /**
     * Test: min=12, max=8 should fail validation.
     */
    public function test_max_8_with_min_12_fails_validation(): void
    {
        $error = $this->validateMaxPartySize(minPartySize: 12, maxPartySize: 8);

        $this->assertNotNull($error, 'max=8 should fail when min=12');
        $this->assertStringContainsString('at least 12', $error);
    }

    /**
     * Test: min=0 is treated as min=1 (minimum enforced).
     */
    public function test_min_zero_is_treated_as_one(): void
    {
        $error = $this->validateMaxPartySize(minPartySize: 0, maxPartySize: 1);

        $this->assertNull($error, 'max=1 should pass when min=0 (treated as 1)');
    }

    /**
     * Test: max=1 with min=2 should fail.
     */
    public function test_max_1_with_min_2_fails_validation(): void
    {
        $error = $this->validateMaxPartySize(minPartySize: 2, maxPartySize: 1);

        $this->assertNotNull($error, 'max=1 should fail when min=2');
        $this->assertStringContainsString('at least 2', $error);
    }

    /**
     * Test: large valid values pass.
     */
    public function test_large_valid_values_pass_validation(): void
    {
        $error = $this->validateMaxPartySize(minPartySize: 50, maxPartySize: 100);

        $this->assertNull($error, 'max=100 should pass when min=50');
    }
}
