@extends('appointment::layouts.master')
@section('page-title')
    {{ __('Create Appointment') }}
@endsection
@push('css')
    <style>
        .dark_background_color {
            background: #000 !important;
        }
    </style>
@endpush
@php
    $admin_settings = getAdminAllSetting();
    $company_settings = getCompanyAllSetting($workspace->created_by, $workspace->id);
@endphp
@section('content')
    <div class="auth-wrapper auth-v1">
        <div class="bg-auth-side bg-primary"></div>
        <div class="auth-content">

            <nav class="navbar navbar-expand-md navbar-dark default dark_background_color">
                <div class="container-fluid pe-2">
                    <a class="navbar-brand" href="#">

                        <img src="{{ !empty($company_settings['logo_light']) ? get_file($company_settings['logo_light']) : get_file(!empty($admin_settings['logo_light']) ? $admin_settings['logo_light'] : 'uploads/logo/logo_light.png') }}{{ '?' . time() }}"
                            class="navbar-brand-img auth-navbar-brand" style="
                        max-width: 168px;">
                    </a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                        <ul class="navbar-nav align-items-center ms-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('get.appointment.search', $workspace->slug) }}"
                                    style="color: #0CAF60;">{{ __('Search Appointment') }}</a>
                            </li>
                        </ul>
                    </div>

                </div>
            </nav>

            <div class="row align-items-center justify-content-center text-start">
                <div class="col-xl-12 text-center">
                    <div class="card">
                        <div class="card-body w-100">
                            <div class="">
                                <h4 class="text-primary mb-3">{{ __('Appointment created successfully.') }}</h4>
                                @if (
                                    !empty($company_settings['Appointment Send']) &&
                                        $company_settings['Appointment Send'] == true)
                                    <h6 class="text-primary mb-2">
                                        {{ __('Your appointment ticket has been sent to your email, please check it now.') }}
                                    </h6>
                                @endif
                            </div>
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
