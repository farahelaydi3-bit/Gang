<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function createReviewFor(User $owner): Review
    {
        return Review::factory()->create([
            'user_id' => $owner->id,
        ]);
    }

    public function test_user_cannot_update_another_users_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = $this->createReviewFor($owner);

        $this->actingAs($otherUser)
            ->put(route('reviews.update', $review), [
                'rating' => 1,
                'comment' => 'Unauthorized update',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'user_id' => $owner->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
        ]);
    }

    public function test_user_cannot_delete_another_users_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = $this->createReviewFor($owner);

        $this->actingAs($otherUser)
            ->delete(route('reviews.destroy', $review))
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_owner_can_update_their_review(): void
    {
        $owner = User::factory()->create();
        $review = $this->createReviewFor($owner);

        $this->actingAs($owner)
            ->put(route('reviews.update', $review), [
                'rating' => 5,
                'comment' => 'Updated by owner',
            ])
            ->assertRedirect(route('reviews.show', $review));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Updated by owner',
        ]);
    }

    public function test_owner_can_delete_their_review(): void
    {
        $owner = User::factory()->create();
        $review = $this->createReviewFor($owner);

        $this->actingAs($owner)
            ->delete(route('reviews.destroy', $review))
            ->assertRedirect(route('reviews.index'));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }
}
