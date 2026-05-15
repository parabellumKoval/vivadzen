<?php

namespace Tests\Feature\Reviews;

use Backpack\Reviews\app\Http\Requests\ReviewRequest;
use Illuminate\Routing\Redirector;
use Tests\TestCase;

class ReviewRequestTest extends TestCase
{
    public function test_photo_gallery_json_string_is_normalized_before_validation(): void
    {
        $request = ReviewRequest::create('/admin/review/240', 'PUT', [
            'type' => '240',
            'review_type' => 'photo',
            'photo_gallery' => json_encode([
                [
                    'src' => 'http://localhost:8000/uploads/reviews/photos/2026/05/photo.jpg',
                    'alt' => 'Preview',
                ],
            ], JSON_UNESCAPED_SLASHES),
        ]);

        $request->setContainer($this->app);
        $request->setRedirector($this->app->make(Redirector::class));
        $request->validateResolved();

        $validated = $request->validated();

        $this->assertIsArray($validated['photo_gallery']);
        $this->assertCount(1, $validated['photo_gallery']);
        $this->assertSame(
            'http://localhost:8000/uploads/reviews/photos/2026/05/photo.jpg',
            $validated['photo_gallery'][0]['src']
        );
        $this->assertSame('Preview', $request->input('photo_gallery.0.alt'));
    }
}
