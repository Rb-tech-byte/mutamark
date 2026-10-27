@extends('layouts.auth')
@section('page-title')
    {{ __('Passwork Check') }}
@endsection
@section('language-bar')
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href  ="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ Str::upper($lang) }}</span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach (languages() as $key => $language)
                    <a href="{{ route('file.shared.link',[$id,$key]) }}"
                        class="dropdown-item @if ($lang == $key) text-primary @endif">
                        <span>{{ Str::ucfirst($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection

@push('css')
@endpush
@section('action-btn')
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="">
            <h2 class="mb-3 f-w-600">{{ __('Password required') }}</h2>
            <p class="text-xs text-muted">{{ __('This document is password-protected. Please enter a password.') }}</p>
        </div>
        <form method="POST" action="{{ route('file.password.check',[$id,$lang]) }}" class="needs-validation" novalidate="">
            @csrf
            <div>
                <div class="form-group mb-3">
                    <label class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control" name="password" placeholder="{{ __('Password') }}" required>
                    @if($message = Session::get('error'))
                        <span class="error invalid-password text-danger" role="alert">
                            <small>{{ $message }}</small>
                        </span>
                    @endif
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-block mt-2" tabindex="4">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
