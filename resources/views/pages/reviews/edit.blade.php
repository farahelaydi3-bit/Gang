@extends('layouts.demo')
@section('title', __('Edit Review'))
@section('page', 'reviews')
@push('styles')
@include('pages.reviews._styles')
@endpush
@section('content')
<div class="wrap page review-page">
  <header class="review-hero"><span class="review-eyebrow">{{ __('GANG · YOUR FEEDBACK') }}</span><h1>{{ __('Edit Review') }}</h1><p>{{ __('Update your rating or comment whenever you need.') }}</p></header>
  <div class="review-form-card">
    <a class="review-link review-back" href="{{ route('reviews.show', $review) }}">← {{ __('Back to Review') }}</a>
    <div class="review-booking-pill">{{ __('Review') }} #{{ $review->id }}</div>
    @if($errors->any())<div class="review-alert error" role="alert"><strong>{{ __('Please correct the following:') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form action="{{ route('reviews.update', $review) }}" method="POST">@csrf @method('PUT')
      <span class="review-label">{{ __('Your rating') }} *</span>
      <div class="review-rating-choices" role="group" aria-label="{{ __('Your rating') }}">
        @for($i=1;$i<=5;$i++)<input type="radio" name="rating" id="rating-{{ $i }}" value="{{ $i }}" @checked((int)old('rating',$review->rating)===$i) @if($i===1) required @endif><label for="rating-{{ $i }}" title="{{ $i }} {{ __('stars') }}" aria-label="{{ $i }} {{ __('stars') }}">★</label>@endfor
      </div>
      <label class="review-label" for="comment">{{ __('Your comment') }} <span class="review-muted">({{ __('optional') }})</span></label>
      <textarea class="review-textarea" name="comment" id="comment" maxlength="2000">{{ old('comment',$review->comment) }}</textarea>
      <p class="review-note">{{ __('Maximum 2000 characters.') }}</p>
      <div class="review-form-actions"><button class="review-submit" type="submit">{{ __('Save Changes') }}</button><a class="review-link" href="{{ route('reviews.show', $review) }}">{{ __('Cancel') }}</a></div>
    </form>
  </div>
</div>
@endsection
