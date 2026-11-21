@extends('layouts.demo')
@section('title', __('Review Details'))
@section('page', 'reviews')
@push('styles')
@include('pages.reviews._styles')
@endpush
@section('content')
<div class="wrap page review-page">
  <header class="review-hero"><span class="review-eyebrow">GANG · COMMUNITY FEEDBACK</span><h1>{{ __('Review Details') }}</h1><p>{{ __('See what players are saying about their court experience.') }}</p></header>
  <div class="review-detail">
    <a class="review-link review-back" href="{{ route('reviews.index') }}">← {{ __('Back to Reviews') }}</a>
    @if(session('success'))<div class="review-alert" role="status">{{ session('success') }}</div>@endif
    <div class="review-card-top"><div class="review-author"><span class="review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->user->name ?? 'U', 0, 1)) }}</span><div><strong>{{ $review->user->name ?? __('Unknown User') }}</strong><small>{{ __('Review') }} #{{ $review->id }}</small></div></div><span class="review-rating">{{ $review->rating }}/5</span></div>
    <div class="review-divider"></div>
    <div class="review-stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', (int)$review->rating) }}<span style="color:#d5ded7">{{ str_repeat('☆', max(0,5-(int)$review->rating)) }}</span></div>
    <p class="review-court">⌖ {{ $review->court->name ?? __('Unknown Court') }}</p>
    <p class="review-comment">{{ $review->comment ?: __('No comment provided.') }}</p>
    @auth @if($review->user_id === auth()->id())
      <div class="review-divider"></div><div class="review-actions"><a class="review-submit" style="text-decoration:none" href="{{ route('reviews.edit', $review) }}">{{ __('Edit Review') }}</a><form method="POST" action="{{ route('reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review?')">@csrf @method('DELETE')<button type="submit" class="review-danger">{{ __('Delete Review') }}</button></form></div>
    @endif @endauth
  </div>
</div>
@endsection
