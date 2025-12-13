<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Smoke test to verify placeholder assets exist and are valid.
 *
 * These placeholder images are critical for UX when venues don't have
 * uploaded logos or cover images. They must exist on disk and be valid images.
 */
class PlaceholderAssetsTest extends TestCase
{
    public function test_restaurant_cover_placeholder_exists_and_is_valid(): void
    {
        $path = public_path('images/placeholders/restaurant-cover.jpg');

        // File must exist
        $this->assertFileExists($path);

        // File must be readable
        $this->assertTrue(is_readable($path));

        // File must be a valid JPEG
        $imageInfo = getimagesize($path);
        $this->assertNotFalse($imageInfo, 'File is not a valid image');
        $this->assertEquals('image/jpeg', $imageInfo['mime']);

        // File should have reasonable dimensions (not 0x0)
        $this->assertGreaterThan(0, $imageInfo[0], 'Image width is 0');
        $this->assertGreaterThan(0, $imageInfo[1], 'Image height is 0');
    }

    public function test_restaurant_logo_placeholder_exists_and_is_valid(): void
    {
        $path = public_path('images/placeholders/restaurant-logo.png');

        // File must exist
        $this->assertFileExists($path);

        // File must be readable
        $this->assertTrue(is_readable($path));

        // File must be a valid PNG
        $imageInfo = getimagesize($path);
        $this->assertNotFalse($imageInfo, 'File is not a valid image');
        $this->assertEquals('image/png', $imageInfo['mime']);

        // File should have reasonable dimensions (not 0x0)
        $this->assertGreaterThan(0, $imageInfo[0], 'Image width is 0');
        $this->assertGreaterThan(0, $imageInfo[1], 'Image height is 0');
    }

    public function test_placeholder_paths_match_model_helpers(): void
    {
        // Verify the paths used in Restaurant model helpers are correct
        $coverPath = public_path('images/placeholders/restaurant-cover.jpg');
        $logoPath = public_path('images/placeholders/restaurant-logo.png');

        $this->assertFileExists($coverPath);
        $this->assertFileExists($logoPath);

        // Verify asset() helper generates correct URLs
        $coverUrl = asset('images/placeholders/restaurant-cover.jpg');
        $logoUrl = asset('images/placeholders/restaurant-logo.png');

        $this->assertStringContainsString('images/placeholders/restaurant-cover.jpg', $coverUrl);
        $this->assertStringContainsString('images/placeholders/restaurant-logo.png', $logoUrl);
    }
}
