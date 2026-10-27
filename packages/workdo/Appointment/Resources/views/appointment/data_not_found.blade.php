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
                </div>
            </nav>

            <div class="row align-items-center justify-content-center text-start">
                <div class="col-xl-12 text-center">
                    <div class="mx-3 mx-md-5 mt-3">

                    </div>
                    @if (Session::has('create_appointment'))
                        <div class="alert alert-success">
                            <p>{!! session('create_appointment') !!}</p>
                        </div>
                    @endif
                    <div class="card">
                        <div class="card-body w-100">
                            <!-- wrapper start -->
                            <div class="wrapper">

                                <section class="dedicated-themes-section padding-bottom padding-top">
                                    <div class="container">
                                        <div class="section-title text-center section">
                                            <h1 style="font-size: 115px">404</h1>
                                            <div>{{ __('Ooops!!! The Appointment you are looking for is not found') }}</div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                            <!-- wrapper end -->
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
