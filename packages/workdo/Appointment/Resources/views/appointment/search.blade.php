@extends('appointment::layouts.master')
@section('page-title')
    {{ __('Search Your Appointment') }}
@endsection
@section('content')
    <div class="auth-wrapper auth-v3">
        <div class="bg-auth-side bg-primary"></div>
        <div class="auth-content">

            <nav class="navbar navbar-expand-md navbar-light default">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">
                        <img src="{{ get_file(sidebar_logo()) }}{{ '?' . time() }}" alt="" class="logo logo-lg"
                            style="max-width: 110px;" />
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarTogglerDemo01" style="flex-grow: 0;">
                        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="card">
                <div class="row align-items-center text-start">
                    <div class="col-xl-6">
                        <div class="card-body">
                            <div class="">
                                <h3 class="mb-3 f-w-600">{{ __('Search Your Appointment') }}</h3>
                            </div>
                            <form method="POST" action="{{ route('appointment.search', $workspace->slug) }}">

                                @csrf
                                @if (session()->has('info'))
                                    <div class="alert alert-danger">
                                        {{ session()->get('info') }}
                                    </div>
                                @endif
                                @if (session()->has('status'))
                                    <div class="alert alert-info">
                                        {{ session()->get('status') }}
                                    </div>
                                @endif

                                <div class="">
                                    <div class="form-group mb-3">
                                        <label for="unique_id" class="form-label">{{ __('Appointment Number') }}</label>
                                        <input type="number"
                                            class="form-control {{ $errors->has('unique_id') ? 'is-invalid' : '' }}"
                                            min="0" id="unique_id" name="unique_id"
                                            placeholder="{{ __('Enter Appointment Number') }}" required=""
                                            value="{{ old('unique_id') }}" autofocus>
                                        <div class="invalid-feedback d-block">
                                            {{ $errors->first('unique_id') }}
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">{{ __('Email') }}</label>
                                        <input type="email"
                                            class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                            id="email" name="email" placeholder="{{ __('Enter Email') }}"
                                            reuired="" value="{{ old('email') }}">
                                        <div class="invalid-feedback d-block">
                                            {{ $errors->first('email') }}
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <button
                                            class="btn btn-primary btn-submit btn-block mt-2">{{ __('Search') }}</button>
                                    </div>


                                </div>
                            </form>

                        </div>
                    </div>
                    <div class="col-xl-6 img-card-side">
                        <div class="auth-img-content">
                            <img src="{{ asset('assets/images/auth/img-auth-3.svg') }}" alt="" class="img-fluid">
                            <h3 class="text-white mb-4 mt-5">{{ __('“Attention is the new currency”') }}</h3>
                            <p class="text-white">{{ __('The more effortless the writing looks, the more effort the writer
                                actually put into the process.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted">{{ env('FOOTER_TEXT') }}</p>
                        </div>
                        <div class="col-6 text-end">

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@push('scripts')
    <script>
        // for Choose file
        $(document).on('change', 'input[type=file]', function() {
            var names = '';
            var files = $('input[type=file]')[0].files;

            for (var i = 0; i < files.length; i++) {
                names += files[i].name + '<br>';
            }
            $('.' + $(this).attr('data-filename')).html(names);
        });
    </script>
@endpush
