
@extends('recruitment::layouts.master')
@section('page-title')
    {{$job->title}}
@endsection
@section('content')
<div class="job-wrapper">
    <div class="job-content">
        <nav class="navbar">
            <div class="container">
                <a class="navbar-brand" href="#">
                    <img src="{{ !empty (get_file(company_setting('logo_light',$job->created_by,$job->workspace))) ? get_file('uploads/logo/logo_light.png') :'WorkDo'}}" alt="logo" style="width: 90px">
                </a>
                <li class="dropdown dash-h-item drp-language">
                    <div class="dropdown global-icon" data-toggle="tooltip" data-original-titla="{{ __('Choose Language') }}" >
                        <a class="nav-link px-0  btn bg-white px-3 py-2"  role="button" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false" data-offset="0,10">
                            <span class="d-none d-lg-inline-block">{{ \Str::upper($currantLang) }}</span>
                            <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                        </a>
                        <div class="dropdown-menu  dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="min-width: auto">
                            @foreach ($languages as $key=>$language)
                                <a class="dropdown-item @if($key == $currantLang) text-danger @endif" href="{{route('job.terms.and.conditions',[$job->code,$key])}}">{{$language}}</a>
                            @endforeach
                        </div>
                    </div>
                </li>
            </div>
        </nav>
        <section class="job-banner">
            <div class="job-banner-bg">
                <img src="{{ asset('Modules/Recruitment/Resources/assets/image/banner.png') }}" alt="">

            </div>
            <div class="container">
                <div class="job-banner-content text-center text-white">
                    <h1 class="text-white mb-3">
                        {{__(' We help')}} <br> {{__('businesses grow')}}
                    </h1>
                    <p>{{ __('Work there. Find the dream job you’ve always wanted..') }}</p>
                    </p>
                </div>
            </div>
        </section>
        <section class="apply-job-section">
            <div class="container">
                <div class="apply-job-wrapper bg-light">
                    @if (!empty($job->terms_and_conditions))
                        <h3>{{ __('Terms and conditions') }}</h3><hr>
                        {!! $job->terms_and_conditions !!}
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/site.core.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/autosize.min.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/site.js') }}"></script>
    <script src="{{ asset('Modules/Recruitment/Resources/assets/js/demo.js') }} "></script>
@endpush


