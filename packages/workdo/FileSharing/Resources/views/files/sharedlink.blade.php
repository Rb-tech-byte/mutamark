@php
    $admin_settings = getAdminAllSetting();
    $temp_lang = \App::getLocale('lang');
    if ($temp_lang == 'ar' || $temp_lang == 'he') {
        $rtl = 'on';
    } else {
        $rtl = isset($company_settings['site_rtl']) ? $company_settings['site_rtl'] : '';
    }
@endphp
@section('page-title')
    {{ __('Files') }}
@endsection

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $rtl == 'on' ? 'rtl' : '' }}">
<meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">

<head>

    <title>@yield('page-title') |
        {{ isset($company_settings['title_text']) ? $company_settings['title_text'] : (isset($admin_settings['title_text']) ? $admin_settings['title_text'] : 'WorkDo') }}
    </title>

    <meta name="title"
        content="{{ isset($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Mutamark ERP' }}">
    <meta name="keywords"
        content="{{ isset($admin_settings['meta_keywords']) ? $admin_settings['meta_keywords'] : 'Mutamark ERP,SaaS solution,Multi-workspace' }}">
    <meta name="description"
        content="{{ isset($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Discover the efficiency of Dash, a user-friendly web application by Rajodiya Apps.' }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title"
        content="{{ isset($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Mutamark ERP' }}">
    <meta property="og:description"
        content="{{ isset($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Discover the efficiency of Dash, a user-friendly web application by Rajodiya Apps.' }} ">
    <meta property="og:image"
        content="{{ get_file(isset($admin_settings['meta_image']) ? (check_file(isset($admin_settings['meta_image']) ? $admin_settings['meta_image'] : '') ? $admin_settings['meta_image'] : 'uploads/meta/meta_image.png') : 'uploads/meta/meta_image.png') }}{{ '?' . time() }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ env('APP_URL') }}">
    <meta property="twitter:title"
        content="{{ isset($admin_settings['meta_title']) ? $admin_settings['meta_title'] : 'Mutamark ERP' }}">
    <meta property="twitter:description"
        content="{{ isset($admin_settings['meta_description']) ? $admin_settings['meta_description'] : 'Discover the efficiency of Dash, a user-friendly web application by Rajodiya Apps.' }} ">
    <meta property="twitter:image"
        content="{{ get_file(isset($admin_settings['meta_image']) ? (check_file(isset($admin_settings['meta_image']) ? $admin_settings['meta_image'] : '') ? $admin_settings['meta_image'] : 'uploads/meta/meta_image.png') : 'uploads/meta/meta_image.png') }}{{ '?' . time() }}">

    <meta name="author" content="Workdo.io">

    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">

    <!-- Favicon icon -->
    <link rel="icon"
        href="{{ get_file(isset($company_settings['favicon']) ? $company_settings['favicon'] : (isset($admin_settings['favicon']) ? $admin_settings['favicon'] : 'uploads/logo/favicon.png')) }}"
        type="image/x-icon" />

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">
    <link rel="stylesheet" href="{{ asset('Modules/FileSharing/Resources/assets/css/filesharecustom.css') }}">

    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/bootstrap-switch-button.min.css') }}"
        id="main-style-link">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/datepicker-bs5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custome.css') }}">
    @stack('css')

    @if ($rtl == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}">
    @endif

    @if (isset($company_settings['cust_darklayout']) && $company_settings['cust_darklayout'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-dark.css') }}" id="main-style-link">
    @endif
    @if ($rtl != 'on' && (empty($company_settings['cust_darklayout']) || $company_settings['cust_darklayout'] != 'on'))
        <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">
    @endif
</head>

<body class="download-page-bg {{ isset($company_settings['color']) ? $company_settings['color'] : 'theme-1' }}">
    <div class="download-bg-img">
        <img src="{{ asset('Modules/FileSharing/Resources/assets/img/d2.svg') }}" class="bg-img-2-1 bg-img-5">
        <img src="{{ asset('Modules/FileSharing/Resources/assets/img/d3.svg') }}" class="bg-img-2-1 bg-img-6">
        <img src="{{ asset('Modules/FileSharing/Resources/assets/img/d4.svg') }}" class="bg-img-2-1 bg-img-7">
        <img src="{{ asset('Modules/FileSharing/Resources/assets/img/d5.svg') }}" class="bg-img-2-1 bg-img-8">
        <img src="{{ asset('Modules/FileSharing/Resources/assets/img/d6.png') }}" class="bg-img-2-1 bg-img-9">
    </div>
        <div class="dash-content">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-xl-5 col-md-6">
                        <div class="download-content text-center">
                            <h2 class="text-center">{{ __('Your Download')}} <br>{{ __('is ready')}}</h2>
                            <p class="text-start">{{ __('The following files are included in this link') }}</p>
                            @php
                                if ($file->auto_destroy == 'on' && $file->file_status == 'Available') {
                                    $status = true;
                                } elseif ($file->auto_destroy == 'off') {
                                    $status = true;
                                } else {
                                    $status = false;
                                }
                                $originalString = $file->file_path;
                                $substringToRemove = 'uploads/filesshare/';
                                $fileName = str_replace($substringToRemove, '', $originalString);

                            @endphp
                            <div class="download-size d-flex align-items-center gap-3 flex-wrap">
                                <div class="file-name btn btn-primary">{{ $fileName }}</div>
                                <div class="file-size btn btn-primary">{{ $file->file_size }}</div>
                            </div>
                            @if(!empty($file->description))
                                  <div class="download-size">{{ $file->description }} </div>
                            @endif
                            @if ($status)
                                <a href="{{ get_file($file->file_path) }}"
                                    class="btn btn-xs btn btn-secondary btn-icon-only width-auto text-white w-100"
                                    onclick="uploadButton(this)" id="{{ $file->id }}" download>
                                    {{ __('Download') }}</a>
                            @else
                                @php
                                    if (!empty($file->file_path)) {
                                        delete_file($file->file_path);
                                    }
                                @endphp
                                <p>{{ __('The file you are  trying to download is on auto destroy mode and has already been downloaded.') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-7 col-md-6">
                        <div class="download-img">
                            <img src="{{ asset('Modules/FileSharing/Resources/assets/img/4.png') }}">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <footer class="dash-footer">
        <div class="footer-wrapper">
            <div class="py-1">
                <span class="text-muted">
                    @if (isset($company_settings['footer_text']))
                        {{ $company_settings['footer_text'] }}
                    @elseif(isset($admin_settings['footer_text']))
                        {{ $admin_settings['footer_text'] }}
                    @else
                        {{ __('Copyright') }} &copy; {{ config('app.name', 'Mutamark ERP') }}
                    @endif{{ date('Y') }}
                </span>
            </div>
        </div>
    </footer>
    @if (isset($admin_settings['enable_cookie']) && $admin_settings['enable_cookie'] == 'on')
        @include('layouts.cookie_consent')
    @endif
</body>

</html>
<script src="{{ asset('js/jquery.min.js') }} "></script>
<script>
    function uploadButton(element) {
        var id = element.id;
        $.ajax({
            url: "{{ route('file.download', ['file' => $file->id]) }}",
            type: 'POST',
            data: {
                "file_id": id,
                "_token": "{{ csrf_token() }}",
            },

        });
    };
</script>
