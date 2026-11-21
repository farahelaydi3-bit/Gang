@extends('layouts.demo')
@section('title', __('Write a Review'))
@section('page', 'reviews')
@push('styles')
@include('pages.reviews._styles')
@endpush
@section('content')
<div class="wrap page review-page">
  <header class="review-hero"><span class="review-eyebrow">GANG · YOUR EXPERIENCE</span><h1>{{ __('Write a Review') }}</h1><p>{{ __('Share your experience and help other players choose their next court.') }}</p></header>
  <div class="review-form-card">
    <a class="review-link review-back" href="{{ route('reviews.index') }}">← {{ __('Back to Reviews') }}</a>
    <div class="review-booking-pill">{{ __('Booking') }} #{{ $booking->id }}</div>
    @if($errors->any())<div class="review-alert error" role="alert"><strong>{{ __('Please correct the following:') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form action="{{ route('reviews.store') }}" method="POST">@csrf
      <input type="hidden" name="booking_id" value="{{ $booking->id }}">
      <span class="review-label">{{ __('Your rating') }} *</span>
      <div class="review-rating-choices" role="group" aria-label="{{ __('Your rating') }}">
        @for($i=1;$i<=5;$i++)<input type="radio" name="rating" id="rating-{{ $i }}" value="{{ $i }}" @checked((int)old('rating')===$i) @if($i===1) required @endif><label for="rating-{{ $i }}" title="{{ $i }} {{ __('stars') }}" aria-label="{{ $i }} {{ __('stars') }}">★</label>@endfor
      </div>
      <label class="review-label" for="comment">{{ __('Your comment') }} <span class="review-muted">({{ __('optional') }})</span></label>
      <textarea class="review-textarea" name="comment" id="comment" maxlength="2000" placeholder="{{ __('Tell us about the court, atmosphere, and your experience...') }}">{{ old('comment') }}</textarea>
      <p class="review-note">{{ __('Maximum 2000 characters.') }}</p>
      <div class="review-form-actions"><button class="review-submit" type="submit">{{ __('Submit Review') }}</button><a class="review-link" href="{{ route('reviews.index') }}">{{ __('Cancel') }}</a></div>
    </form>
  </div>
</div>
@endsection
