@extends('layouts.demo')
@section('title', __('Customer Reviews'))
@section('page', 'reviews')
@push('styles')
@include('pages.reviews._styles')
@endpush
@section('content')
<div class="wrap page review-page">
  <header class="review-hero"><span class="review-eyebrow">{{ __('GANG · COMMUNITY') }}</span><h1>{{ __('Customer Reviews') }}</h1><p>{{ __('Real experiences from players across our courts.') }}</p></header>
  @if(session('success'))<div class="review-alert" role="status">{{ session('success') }}</div>@endif
  @auth<p class="review-muted">{{ __('To add a review, open a completed booking and select Write Review.') }}</p>@endauth
  <div class="review-grid">
    @forelse($reviews as $review)
      <article class="review-card">
        <div class="review-card-top"><div class="review-author"><span class="review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->user->name ?? 'U', 0, 1)) }}</span><div><strong>{{ $review->user->name ?? __('User') }}</strong><small>{{ __('Verified community feedback') }}</small></div></div><span class="review-rating">{{ $review->rating }}/5</span></div>
        <div class="review-stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', (int)$review->rating) }}<span style="color:#d5ded7">{{ str_repeat('☆', max(0, 5 - (int)$review->rating)) }}</span></div>
        <div class="review-court">⌖ {{ $review->court->name ?? __('Unknown Court') }}</div>
        <p class="review-comment">{{ $review->comment ?: __('No comment provided.') }}</p>
        <div class="review-actions"><a class="review-link" href="{{ route('reviews.show', $review) }}">{{ __('View review') }} →</a>
          @auth @if($review->user_id === auth()->id())
            <a class="review-link" href="{{ route('reviews.edit', $review) }}">{{ __('Edit') }}</a>
            <form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review?')">@csrf @method('DELETE')<button class="review-danger" type="submit">{{ __('Delete') }}</button></form>
          @endif @endauth
        </div>
      </article>
    @empty
      <div class="review-empty"><div class="review-empty-icon">☆</div><h2>{{ __('No reviews yet') }}</h2><p class="review-muted">{{ __('Be the first to share your court experience.') }}</p></div>
    @endforelse
  </div>
  <div class="review-pagination">{{ $reviews->links() }}</div>
</div>
@endsection
