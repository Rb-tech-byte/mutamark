<!DOCTYPE html>
<html lang="en" dir="{{ $settings['site_rtl'] == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        {{ $job_candidate->name . __(' Resume') }}
        |
        {{ !empty(company_setting('title_text', $company_id, $workspace)) ? company_setting('title_text', $company_id, $workspace) : (!empty(admin_setting('title_text')) ? admin_setting('title_text') : 'WorkDo') }}
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <style type="text/css">
        :root {
            --theme-color: {{ $color }};
            --white: #ffffff;
            --black: #000000;
        }

        body {
            font-family: 'Raleway', sans-serif;
            font-size: 14px;
        }

        h2 {
            font-size: 18px;
        }

        h3 {
            font-size: 17px;
            margin-bottom: 10px;
        }

        p,
        li,
        ul,
        ol {
            margin: 0;
            padding: 0;
            list-style: none;
            line-height: 1.5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table tr th {
            padding: 0.75rem;
            text-align: left;
        }

        table tr td {
            padding: 0.75rem;
            text-align: left;
        }

        table th small {
            display: block;
            font-size: 12px;
        }

        .invoice-preview-main {
            max-width: 950px;
            width: 100%;
            margin: 0 auto;
            background: #ffff;
            box-shadow: 0 0 10px #ddd;
        }

        .invoice-logo {
            max-width: 200px;
            width: 100%;
        }

        .invoice-header table td {
            padding: 30px 0 30px 30px;
        }

        .text-right {
            text-align: right;
        }

        .no-space tr td {
            padding: 0;
        }

        .vertical-align-top td {
            vertical-align: top;
        }

        .invoice-body {
            padding: 10px 30px;
        }

        table.add-border tr {
            border-top: 1px solid var(--theme-color);
        }

        tfoot tr:first-of-type {
            border-bottom: 1px solid var(--theme-color);
        }

        .total-table tr:first-of-type td {
            padding-top: 0;
        }

        .total-table tr:first-of-type {
            border-top: 0;
        }

        .sub-total {
            padding-right: 0;
            padding-left: 0;
        }

        .border-0 {
            border: none !important;
        }

        .total-table td:last-of-type {
            width: 146px;
        }

        html[dir="rtl"] table tr td,
        html[dir="rtl"] table tr th {
            text-align: right;
        }

        html[dir="rtl"] .text-right {
            text-align: left;
        }

        html[dir="rtl"] .view-qrcode {
            margin-left: 0;
            margin-right: auto;
        }

        p:not(:last-of-type) {
            margin-bottom: 15px;
        }

        .vertical-align-top tbody tr td h2 {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .vertical-align-top tbody tr td h2 svg {
            height: 30px;
            width: 30px;
        }

        .vertical-align-top tbody tr td ul li {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .vertical-align-top tbody tr td ul {
            margin-left: 45px;
        }

        .vertical-align-top tbody tr td:nth-child(2) ul li span {
            max-width: 100px;
            width: 100%;
            font-weight: 600;
        }

        .vertical-align-top tbody tr td:nth-child(3) ul li:first-of-type {
            margin-bottom: 10px;
        }

        .vertical-align-top .name-img {
            max-width: 130px;
            width: 100%;
            max-height: 130px;
            border-radius: 65px;
        }

        .invoice-body h2,
        .invoice-body h3,
        .invoice-body h4 {
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .invoice-body h2 {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .invoice-body .invoice-title {
            margin-bottom: 15px;
        }

        .invoice-body .invoice-title h3 {
            font-size: 17px;
            margin-bottom: 10px;
        }

        .invoice-body .information:not(:last-of-type) {
            margin-bottom: 15px;
        }

        .invoice-body .information .subtitle {
            display: flex;
            align-items: center;
            gap: 40px;
            margin-bottom: 10px;
        }

        .invoice-body .information .subtitle .sub-content {
            display: flex;
            align-items: center;
            flex: 1;
            justify-content: space-between;
        }

        .invoice-body .information ul li {
            list-style-type: disc;
            margin-left: 15px;
        }

        .invoice-body .information .content {
            margin-left: 58px;
        }

        .invoice-body .information .content:not(:last-of-type) {
            margin-bottom: 10px;
        }

        .invoice-body ul,
        .invoice-body p {
            margin-bottom: 15px;
        }

        .invoice-body table {
            margin: 15px 0;
        }

        .invoice-body table thead td {
            font-weight: 600;
            text-transform: uppercase;
        }

        .invoice-body table tr td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .invoice-body table thead {
            background-color: var(--theme-color);
            color: var(--white);
        }

        .invoice-body table tr td:last-of-type {
            text-align: end;
        }
    </style>
</head>

<body>
    <div class="invoice-preview-main" id="boxes">
        <div class="invoice-header" style="background-color: var(--theme-color); color: {{ $font_color }};">
            <table class="vertical-align-top">
                <tbody>
                    <tr>
                        <td>
                            <img class="name-img" src="{{ $img }}" alt="">
                        </td>
                        <td>
                            <h2> <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 83 83"
                                    width="83" height="83">
                                    <style>
                                        .a {
                                            fill: #2d2e31;
                                            stroke: #fefefe;
                                            paint-order: stroke fill markers;
                                            stroke-linejoin: round;
                                            stroke-width: 10
                                        }

                                        .b {
                                            fill: #fefefe
                                        }
                                    </style>
                                    <path class="a"
                                        d="m41.5 5c20.2 0 36.6 16.3 36.6 36.5 0 20.1-16.4 36.5-36.6 36.5-20.1 0-36.5-16.4-36.5-36.5 0-20.2 16.4-36.5 36.5-36.5z" />
                                    <path class="b"
                                        d="m42.4 18.8c1.2 0.3 2.4 0.4 3.4 0.9 4.1 1.6 6.6 4.7 7.4 9 0.8 4.9-1.6 9.8-6 12.1-4.2 2.3-8.5 2.1-12.5-0.7-3.2-2.2-4.9-5.4-4.9-9.2-0.1-4.4 1.8-7.8 5.3-10.2 1.6-1.1 3.4-1.6 5.3-1.8q0.1-0.1 0.2-0.1 0.9 0 1.8 0zm-0.2 45.4c-5.9-0.1-10.6-1.6-14.7-4.7q-3.7-2.7-6.1-6.6c-0.2-0.4-0.3-0.8-0.1-1.2 2.1-4.3 5.4-7.1 10-8.2 0.4-0.1 0.8-0.2 1.1-0.2 0.7-0.2 1.4 0 1.9 0.4q3.5 2.5 7.8 2.3c2.2-0.1 4.3-0.6 6-1.9 1.4-1 2.6-0.9 4-0.5 4 1.1 6.9 3.4 9.1 6.9 0.9 1.5 0.9 1.5-0.1 3-2.3 3.6-5.4 6.4-9.2 8.2-3.5 1.7-6.9 2.5-9.7 2.5z" />
                                </svg>
                                {{ __('Profile') }}</h2>
                            <ul>
                                <li>
                                    <span>{{ __('Name') }}</span>
                                    <p>{{ !empty($job_candidate->name) ? $job_candidate->name : '' }}</p>
                                </li>
                                <li>
                                    <span>{{ __('Birthday') }}</span>
                                    <p>{{ !empty($job_candidate->dob) ? company_date_formate($job_candidate->dob, $company_id, $workspace) : '' }}</p>
                                </li>
                                {{-- <li>
                                    <span>Relationship</span>
                                    <p>Single</p>
                                </li> --}}
                                <li>
                                    <span>{{ __('National') }}</span>
                                    <p>{{ !empty($job_candidate->country) ? $job_candidate->country : '' }}</p>
                                </li>
                                {{-- <li>
                                    <span>Language</span>
                                    <p>English</p>
                                </li> --}}
                            </ul>
                        </td>
                        <td>
                            <h2> <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 83 83"
                                    width="83" height="83">
                                    <style>
                                        .a {
                                            fill: #2d2e31;
                                            stroke: #fefefe;
                                            paint-order: stroke fill markers;
                                            stroke-linejoin: round;
                                            stroke-width: 10
                                        }

                                        .b {
                                            fill: #fefefe
                                        }
                                    </style>
                                    <path class="a"
                                        d="m41.5 5c20.2 0 36.6 16.3 36.6 36.5 0 20.1-16.4 36.5-36.6 36.5-20.1 0-36.5-16.4-36.5-36.5 0-20.2 16.4-36.5 36.5-36.5z" />
                                    <path class="b"
                                        d="m33.2 36.7c3.6 5 7.8 9.3 12.9 12.8q0.6-0.9 1.2-1.7c1.5-2.2 4.1-2.7 6.3-1.1q2.7 1.8 5.4 3.7c2 1.4 2.5 3.5 1.5 5.8-1.2 2.5-3.3 3.8-6 4.3-3.1 0.5-5.9-0.2-8.8-1.3-3.9-1.5-7.4-3.8-10.6-6.5-4.5-3.8-8.1-8.2-10.7-13.5-1.3-2.7-2.3-5.6-2.3-8.6-0.1-2.7 0.4-5.1 2.4-6.9 1.2-1.2 2.6-2 4.4-1.9 1.2 0 2.3 0.5 3.1 1.6 1.5 2 2.9 4 4.3 6.1 1.2 1.9 0.7 4.3-1 5.7-0.7 0.5-1.4 0.9-2.1 1.5z" />
                                </svg>
                                {{ __('Contact') }}</h2>

                            <ul>
                                <li>{{ !empty($job_candidate->address) ? $job_candidate->address : '' }} <br>
                                    {{ !empty($job_candidate->country) ? $job_candidate->country : '' }},
                                    {{ !empty($job_candidate->state) ? $job_candidate->state : '' }},
                                    {{ $job_candidate->city }}
                                </li>
                                <li>
                                    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"
                                        width="14" height="14">
                                        <filter id="f0">
                                            <feFlood flood-color="#fefefe" flood-opacity="1" />
                                            <feBlend mode="normal" in2="SourceGraphic" />
                                            <feComposite in2="SourceAlpha" operator="in" />
                                        </filter>
                                        <g filter="url(#f0)">
                                            <path class="b"
                                                d="m23.4 19.3c-2.1 2.1-2.1 4.1-4.1 4.1-2 0-4.1-2-6.1-4.1-2.1-2-4.1-4.1-4.1-6.1 0-2 2-2 4.1-4.1 2-2-4.1-8.1-6.2-8.1-2 0-6.1 6.1-6.1 6.1 0 4.1 4.2 12.3 8.2 16.3 4 4 12.2 8.2 16.3 8.2 0 0 6.2-4.1 6.2-6.2 0-2-6.2-8.1-8.2-6.1z" />
                                        </g>
                                    </svg>
                                    <span>{{ !empty($job_candidate->phone) ? $job_candidate->phone : '' }}</span>
                                </li>
                                @foreach ($job_candidate->JobExperience as $job_experience)
                                    <li>
                                        <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"
                                            width="14" height="14">
                                            <filter id="f0">
                                                <feFlood flood-color="#fefefe" flood-opacity="1" />
                                                <feBlend mode="normal" in2="SourceGraphic" />
                                                <feComposite in2="SourceAlpha" operator="in" />
                                            </filter>
                                            <g filter="url(#f0)">
                                                <path fill-rule="evenodd" class="b"
                                                    d="m2.6 1.8l0.1-0.1c2.7-2.4 7-2.4 9.7 0l6.1 5.5c2.7 2.4 2.7 6.3 0 8.7l-0.1 0.1q-0.3 0.3-0.7 0.5l-2.3-2q0.4-0.2 0.8-0.5l0.1-0.1c1.4-1.3 1.4-3.4 0-4.7l-6.2-5.5c-1.4-1.3-3.7-1.3-5.2 0l-0.1 0.1c-1.4 1.2-1.4 3.4 0 4.6l2.8 2.5c-0.5 1.1-0.7 2.3-0.7 3.4l-4.3-3.9c-2.7-2.4-2.7-6.2 0-8.6zm9.1 7.9q0.3-0.3 0.7-0.5l2.2 2q-0.4 0.2-0.7 0.5l-0.1 0.1c-1.5 1.3-1.5 3.4 0 4.7l6.1 5.5c1.5 1.3 3.8 1.3 5.3 0l0.1-0.1c1.4-1.3 1.4-3.4 0-4.7l-2.8-2.5c0.5-1 0.7-2.2 0.7-3.3l4.3 3.8c2.6 2.4 2.6 6.3 0 8.7l-0.1 0.1c-2.7 2.4-7 2.4-9.7 0l-6.1-5.5c-2.7-2.4-2.7-6.3 0-8.7z" />
                                            </g>
                                        </svg>
                                        <span>{{ !empty($job_experience->website) ? $job_experience->website : $job_candidate->website }}</span>
                                    </li>
                                @endforeach
                                {{-- <li>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 25 19"
                                        fill="none">
                                        <path
                                            d="M16.199 10.5381L14.1052 12.639C13.2561 13.491 11.7622 13.5094 10.8947 12.639L8.80083 10.5381L1.28052 18.0828C1.56045 18.2123 1.86909 18.2895 2.19722 18.2895H22.8027C23.1308 18.2895 23.4394 18.2124 23.7192 18.0829L16.199 10.5381Z"
                                            fill="white"></path>
                                        <path
                                            d="M22.8028 0.710938H2.19736C1.86924 0.710938 1.5606 0.788184 1.28076 0.917627L9.31675 8.98027C9.31728 8.98081 9.31792 8.98091 9.31846 8.98145C9.31899 8.98198 9.31909 8.98272 9.31909 8.98272L11.932 11.6043C12.2096 11.8818 12.7907 11.8818 13.0683 11.6043L15.6807 8.98315C15.6807 8.98315 15.6813 8.98198 15.6818 8.98145C15.6818 8.98145 15.683 8.98081 15.6835 8.98027L23.7193 0.917578C23.4395 0.788086 23.131 0.710938 22.8028 0.710938Z"
                                            fill="white"></path>
                                        <path
                                            d="M0.233691 1.94238C0.0888672 2.23525 0 2.56035 0 2.90859V16.0922C0 16.4404 0.0887695 16.7655 0.233643 17.0584L7.76699 9.50063L0.233691 1.94238Z"
                                            fill="white"></path>
                                        <path
                                            d="M24.7662 1.94141L17.2329 9.49976L24.7662 17.0576C24.911 16.7647 24.9999 16.4396 24.9999 16.0913V2.90771C24.9999 2.55938 24.911 2.23428 24.7662 1.94141Z"
                                            fill="white"></path>
                                    </svg>
                                    <span>www.yourwebsiteurl.com</span>
                                </li> --}}
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="invoice-body">
            <h3>{{ __('Summary') }}</h3>
            <p>{{ !empty($job_candidate->summary) ? $job_candidate->summary : '' }}</p>
            <div class="information">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 83 83" width="40"
                        height="40">
                        <style>
                            .a {
                                fill: #2d2e31;
                                stroke: #fefefe;
                                paint-order: stroke fill markers;
                                stroke-linejoin: round;
                                stroke-width: 10
                            }

                            .b {
                                fill: #fefefe
                            }
                        </style>
                        <path class="a"
                            d="m41.8 5c20.2 0 36.5 16.3 36.5 36.5 0 20.1-16.3 36.5-36.5 36.5-20.1 0-36.5-16.4-36.5-36.5 0-20.2 16.4-36.5 36.5-36.5z" />
                        <path class="b"
                            d="m67.3 29.9c-0.3 0.7-0.8 1-1.4 1.3q-11.5 4-23 8.1c-0.6 0.3-1.1 0.3-1.8 0.1q-11.6-4.2-23.3-8.3c-0.9-0.4-1.4-1.2-1-2 0.2-0.5 0.6-0.8 1-1q8.8-3.1 17.6-6.2 2.9-1 5.8-2 0.8-0.3 1.5-0.1 11.7 4.2 23.4 8.3c0.6 0.2 0.9 0.6 1.2 1.2q0 0.3 0 0.6zm-40.2 7.6c1.8 0.7 3.6 1.3 5.3 1.9q3.9 1.4 7.9 2.8c1.2 0.5 2.3 0.4 3.5 0q6.2-2.3 12.5-4.5c0.1 0 0.3-0.1 0.5-0.2q0.1 0.3 0.1 0.5-0.1 2.9 0 5.7c0 1-0.4 1.8-1.1 2.4-1.2 1-2.5 1.5-3.9 2-3.8 1.2-7.6 1.5-11.5 1.4-3.4-0.2-6.7-0.7-9.9-2-0.8-0.4-1.6-0.9-2.3-1.4-0.8-0.6-1.1-1.4-1.1-2.4q0-2.8 0-5.6c0-0.2 0-0.3 0-0.6zm36.6-2.4q0 0.3 0 0.6 0 5.9 0 11.8c0 0.3 0.1 0.6 0.4 0.8 1.5 1.2 1.5 3.5-0.1 4.7-0.2 0.2-0.3 0.4-0.3 0.6q0 3.4 0 6.8c0 0.6-0.2 1.1-0.8 1.4q-0.7 0.4-1.5-0.1c-0.5-0.3-0.7-0.7-0.6-1.3q0-3.4 0-6.7c0-0.3-0.1-0.5-0.4-0.7-1.5-1.2-1.5-3.5 0-4.7 0.3-0.2 0.4-0.5 0.4-0.8q0-5.4-0.1-10.9c0-0.4 0.1-0.5 0.5-0.6 0.8-0.3 1.6-0.6 2.5-0.9z" />
                    </svg>
                    {{ __('Work Experience') }}
                </h2>
                @foreach ($job_candidate->JobExperience as $job_experience)
                    @php
                        $endDate = Illuminate\Support\Carbon::parse($job_experience['end_date']);
                        $startDate = Illuminate\Support\Carbon::parse($job_experience['start_date']);

                        $endDateFormatted = $startDate->format('F Y') . '-' . $endDate->format(' F Y');
                    @endphp
                    <div class="content">
                        <div class="subtitle">
                            <h4>{{ !empty($job_experience->title) ? $job_experience->title : $job_candidate->work_experience_title }}
                            </h4>
                            <div class="sub-content">
                                <i>{{ !empty($job_experience->organization) ? $job_experience->organization : $job_candidate->company_name }}</i>
                                <span>{{ !empty($endDateFormatted) ? $endDateFormatted : $job_candidate->work_experience_date }}</span>
                            </div>
                        </div>
                        <p>{!! !empty($job_experience->description)
                            ? $job_experience->description
                            : $job_candidate->work_experience_description !!}</p>
                    </div>
                @endforeach
            </div>
            <div class="information">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 83 83" width="40"
                        height="40">
                        <style>
                            .a {
                                fill: #2d2e31;
                                stroke: #fefefe;
                                paint-order: stroke fill markers;
                                stroke-linejoin: round;
                                stroke-width: 10
                            }

                            .b {
                                fill: #fefefe
                            }
                        </style>
                        <path class="a"
                            d="m41.8 5c20.2 0 36.5 16.3 36.5 36.5 0 20.1-16.3 36.5-36.5 36.5-20.1 0-36.5-16.4-36.5-36.5 0-20.2 16.4-36.5 36.5-36.5z" />
                        <path class="b"
                            d="m67.3 29.9c-0.3 0.7-0.8 1-1.4 1.3q-11.5 4-23 8.1c-0.6 0.3-1.1 0.3-1.8 0.1q-11.6-4.2-23.3-8.3c-0.9-0.4-1.4-1.2-1-2 0.2-0.5 0.6-0.8 1-1q8.8-3.1 17.6-6.2 2.9-1 5.8-2 0.8-0.3 1.5-0.1 11.7 4.2 23.4 8.3c0.6 0.2 0.9 0.6 1.2 1.2q0 0.3 0 0.6zm-40.2 7.6c1.8 0.7 3.6 1.3 5.3 1.9q3.9 1.4 7.9 2.8c1.2 0.5 2.3 0.4 3.5 0q6.2-2.3 12.5-4.5c0.1 0 0.3-0.1 0.5-0.2q0.1 0.3 0.1 0.5-0.1 2.9 0 5.7c0 1-0.4 1.8-1.1 2.4-1.2 1-2.5 1.5-3.9 2-3.8 1.2-7.6 1.5-11.5 1.4-3.4-0.2-6.7-0.7-9.9-2-0.8-0.4-1.6-0.9-2.3-1.4-0.8-0.6-1.1-1.4-1.1-2.4q0-2.8 0-5.6c0-0.2 0-0.3 0-0.6zm36.6-2.4q0 0.3 0 0.6 0 5.9 0 11.8c0 0.3 0.1 0.6 0.4 0.8 1.5 1.2 1.5 3.5-0.1 4.7-0.2 0.2-0.3 0.4-0.3 0.6q0 3.4 0 6.8c0 0.6-0.2 1.1-0.8 1.4q-0.7 0.4-1.5-0.1c-0.5-0.3-0.7-0.7-0.6-1.3q0-3.4 0-6.7c0-0.3-0.1-0.5-0.4-0.7-1.5-1.2-1.5-3.5 0-4.7 0.3-0.2 0.4-0.5 0.4-0.8q0-5.4-0.1-10.9c0-0.4 0.1-0.5 0.5-0.6 0.8-0.3 1.6-0.6 2.5-0.9z" />
                    </svg>
                    {{ __('project Experience') }}
                </h2>
                @foreach ($job_candidate->JobProject as $job_project)
                    @php
                        $endDate = Illuminate\Support\Carbon::parse($job_project['end_date']);
                        $startDate = Illuminate\Support\Carbon::parse($job_project['start_date']);

                        $endDateFormatted = $startDate->format('F Y') . '-' . $endDate->format(' F Y');
                    @endphp
                    <div class="content">
                        <div class="subtitle">
                            <h4>{{ !empty($job_project->title) ? $job_project->title : $job_candidate->project_experience_title }}
                            </h4>
                            <div class="sub-content">
                                <i>{{ !empty($job_project->organization) ? $job_project->organization : $job_candidate->company_name }}</i>
                                <span>{{ !empty($endDateFormatted) ? $endDateFormatted : $job_candidate->project_experience_date }}</span>
                            </div>
                        </div>
                        <p>{!! !empty($job_project->description)
                            ? $job_project->description
                            : $job_candidate->project_experience_description !!}</p>
                    </div>
                @endforeach
            </div>
            <div class="information">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 83 83" width="40"
                        height="40">
                        <style>
                            .a {
                                fill: #2d2e31;
                                stroke: #fefefe;
                                paint-order: stroke fill markers;
                                stroke-linejoin: round;
                                stroke-width: 10
                            }

                            .b {
                                fill: #fefefe
                            }
                        </style>
                        <path class="a"
                            d="m41.8 5c20.2 0 36.5 16.3 36.5 36.5 0 20.1-16.3 36.5-36.5 36.5-20.1 0-36.5-16.4-36.5-36.5 0-20.2 16.4-36.5 36.5-36.5z" />
                        <path class="b"
                            d="m67.3 29.9c-0.3 0.7-0.8 1-1.4 1.3q-11.5 4-23 8.1c-0.6 0.3-1.1 0.3-1.8 0.1q-11.6-4.2-23.3-8.3c-0.9-0.4-1.4-1.2-1-2 0.2-0.5 0.6-0.8 1-1q8.8-3.1 17.6-6.2 2.9-1 5.8-2 0.8-0.3 1.5-0.1 11.7 4.2 23.4 8.3c0.6 0.2 0.9 0.6 1.2 1.2q0 0.3 0 0.6zm-40.2 7.6c1.8 0.7 3.6 1.3 5.3 1.9q3.9 1.4 7.9 2.8c1.2 0.5 2.3 0.4 3.5 0q6.2-2.3 12.5-4.5c0.1 0 0.3-0.1 0.5-0.2q0.1 0.3 0.1 0.5-0.1 2.9 0 5.7c0 1-0.4 1.8-1.1 2.4-1.2 1-2.5 1.5-3.9 2-3.8 1.2-7.6 1.5-11.5 1.4-3.4-0.2-6.7-0.7-9.9-2-0.8-0.4-1.6-0.9-2.3-1.4-0.8-0.6-1.1-1.4-1.1-2.4q0-2.8 0-5.6c0-0.2 0-0.3 0-0.6zm36.6-2.4q0 0.3 0 0.6 0 5.9 0 11.8c0 0.3 0.1 0.6 0.4 0.8 1.5 1.2 1.5 3.5-0.1 4.7-0.2 0.2-0.3 0.4-0.3 0.6q0 3.4 0 6.8c0 0.6-0.2 1.1-0.8 1.4q-0.7 0.4-1.5-0.1c-0.5-0.3-0.7-0.7-0.6-1.3q0-3.4 0-6.7c0-0.3-0.1-0.5-0.4-0.7-1.5-1.2-1.5-3.5 0-4.7 0.3-0.2 0.4-0.5 0.4-0.8q0-5.4-0.1-10.9c0-0.4 0.1-0.5 0.5-0.6 0.8-0.3 1.6-0.6 2.5-0.9z" />
                    </svg>
                    {{ __('Qualification Experience') }}
                </h2>
                @foreach ($job_candidate->JobQualification as $job_qualification)
                    @php
                        $endDate = Illuminate\Support\Carbon::parse($job_qualification['end_date']);
                        $startDate = Illuminate\Support\Carbon::parse($job_qualification['start_date']);

                        $endDateFormatted = $startDate->format('F Y') . '-' . $endDate->format(' F Y');
                    @endphp
                    <div class="content">
                        <div class="subtitle">
                            <h4>{{ !empty($job_qualification->title) ? $job_qualification->title : $job_candidate->qualification_experience_title }}
                            </h4>
                            <div class="sub-content">
                                <i>{{ !empty($job_qualification->organization) ? $job_qualification->organization : $job_candidate->company_name }}</i>
                                <span>{{ !empty($endDateFormatted) ? $endDateFormatted : $job_candidate->qualification_experience_date }}</span>
                            </div>
                        </div>
                        <p>{!! !empty($job_qualification->description)
                            ? $job_qualification->description
                            : $job_candidate->qualification_experience_description !!}</p>
                    </div>
                @endforeach
            </div>
            <div class="information">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 83 83" width="40"
                        height="40">
                        <style>
                            .a {
                                fill: #2d2e31;
                                stroke: #fefefe;
                                paint-order: stroke fill markers;
                                stroke-linejoin: round;
                                stroke-width: 10
                            }

                            .b {
                                fill: #fefefe
                            }
                        </style>
                        <path class="a"
                            d="m41.8 5c20.2 0 36.5 16.3 36.5 36.5 0 20.1-16.3 36.5-36.5 36.5-20.1 0-36.5-16.4-36.5-36.5 0-20.2 16.4-36.5 36.5-36.5z" />
                        <path class="b"
                            d="m67.3 29.9c-0.3 0.7-0.8 1-1.4 1.3q-11.5 4-23 8.1c-0.6 0.3-1.1 0.3-1.8 0.1q-11.6-4.2-23.3-8.3c-0.9-0.4-1.4-1.2-1-2 0.2-0.5 0.6-0.8 1-1q8.8-3.1 17.6-6.2 2.9-1 5.8-2 0.8-0.3 1.5-0.1 11.7 4.2 23.4 8.3c0.6 0.2 0.9 0.6 1.2 1.2q0 0.3 0 0.6zm-40.2 7.6c1.8 0.7 3.6 1.3 5.3 1.9q3.9 1.4 7.9 2.8c1.2 0.5 2.3 0.4 3.5 0q6.2-2.3 12.5-4.5c0.1 0 0.3-0.1 0.5-0.2q0.1 0.3 0.1 0.5-0.1 2.9 0 5.7c0 1-0.4 1.8-1.1 2.4-1.2 1-2.5 1.5-3.9 2-3.8 1.2-7.6 1.5-11.5 1.4-3.4-0.2-6.7-0.7-9.9-2-0.8-0.4-1.6-0.9-2.3-1.4-0.8-0.6-1.1-1.4-1.1-2.4q0-2.8 0-5.6c0-0.2 0-0.3 0-0.6zm36.6-2.4q0 0.3 0 0.6 0 5.9 0 11.8c0 0.3 0.1 0.6 0.4 0.8 1.5 1.2 1.5 3.5-0.1 4.7-0.2 0.2-0.3 0.4-0.3 0.6q0 3.4 0 6.8c0 0.6-0.2 1.1-0.8 1.4q-0.7 0.4-1.5-0.1c-0.5-0.3-0.7-0.7-0.6-1.3q0-3.4 0-6.7c0-0.3-0.1-0.5-0.4-0.7-1.5-1.2-1.5-3.5 0-4.7 0.3-0.2 0.4-0.5 0.4-0.8q0-5.4-0.1-10.9c0-0.4 0.1-0.5 0.5-0.6 0.8-0.3 1.6-0.6 2.5-0.9z" />
                    </svg>
                    {{ __('Award') }}
                </h2>
                @foreach ($job_candidate->JobAward as $job_award)
                    <div class="content">
                        {!! !empty($job_award->description) ? $job_award->description : $job_candidate->award_description !!}
                    </div>
                @endforeach
            </div>
            <div class="information">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 83 83" width="40"
                        height="40">
                        <style>
                            .a {
                                fill: #2d2e31;
                                stroke: #fefefe;
                                paint-order: stroke fill markers;
                                stroke-linejoin: round;
                                stroke-width: 10
                            }

                            .b {
                                fill: #fefefe
                            }
                        </style>
                        <path class="a"
                            d="m41.8 5c20.2 0 36.5 16.3 36.5 36.5 0 20.1-16.3 36.5-36.5 36.5-20.1 0-36.5-16.4-36.5-36.5 0-20.2 16.4-36.5 36.5-36.5z" />
                        <path class="b"
                            d="m67.3 29.9c-0.3 0.7-0.8 1-1.4 1.3q-11.5 4-23 8.1c-0.6 0.3-1.1 0.3-1.8 0.1q-11.6-4.2-23.3-8.3c-0.9-0.4-1.4-1.2-1-2 0.2-0.5 0.6-0.8 1-1q8.8-3.1 17.6-6.2 2.9-1 5.8-2 0.8-0.3 1.5-0.1 11.7 4.2 23.4 8.3c0.6 0.2 0.9 0.6 1.2 1.2q0 0.3 0 0.6zm-40.2 7.6c1.8 0.7 3.6 1.3 5.3 1.9q3.9 1.4 7.9 2.8c1.2 0.5 2.3 0.4 3.5 0q6.2-2.3 12.5-4.5c0.1 0 0.3-0.1 0.5-0.2q0.1 0.3 0.1 0.5-0.1 2.9 0 5.7c0 1-0.4 1.8-1.1 2.4-1.2 1-2.5 1.5-3.9 2-3.8 1.2-7.6 1.5-11.5 1.4-3.4-0.2-6.7-0.7-9.9-2-0.8-0.4-1.6-0.9-2.3-1.4-0.8-0.6-1.1-1.4-1.1-2.4q0-2.8 0-5.6c0-0.2 0-0.3 0-0.6zm36.6-2.4q0 0.3 0 0.6 0 5.9 0 11.8c0 0.3 0.1 0.6 0.4 0.8 1.5 1.2 1.5 3.5-0.1 4.7-0.2 0.2-0.3 0.4-0.3 0.6q0 3.4 0 6.8c0 0.6-0.2 1.1-0.8 1.4q-0.7 0.4-1.5-0.1c-0.5-0.3-0.7-0.7-0.6-1.3q0-3.4 0-6.7c0-0.3-0.1-0.5-0.4-0.7-1.5-1.2-1.5-3.5 0-4.7 0.3-0.2 0.4-0.5 0.4-0.8q0-5.4-0.1-10.9c0-0.4 0.1-0.5 0.5-0.6 0.8-0.3 1.6-0.6 2.5-0.9z" />
                    </svg>
                    {{ __('Jobs') }}
                </h2>
                <div class="content">
                    <table>
                        <thead>
                            <tr>
                                <td>{{ __('company name') }}</td>
                                <td>{{ __('year') }}</td>
                            </tr>
                        </thead>
                        @foreach ($job_candidate->JobExperienceCandidate as $job_experience_candidate)
                            @php
                                $endDate = Illuminate\Support\Carbon::parse($job_experience_candidate['end_date']);
                                $startDate = Illuminate\Support\Carbon::parse($job_experience_candidate['start_date']);

                                $endDateFormatted = $startDate->format('F Y') . '-' . $endDate->format(' F Y');
                            @endphp
                            <tbody>
                                <tr>
                                    <td>{{ !empty($job_experience_candidate->organization) ? $job_experience_candidate->organization : $job_candidate->company_name }}
                                    </td>
                                    <td>{{ !empty($endDateFormatted) ? $endDateFormatted : __('2023') }}</td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="information">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 83 83" width="40"
                        height="40">
                        <style>
                            .a {
                                fill: #2d2e31;
                                stroke: #fefefe;
                                paint-order: stroke fill markers;
                                stroke-linejoin: round;
                                stroke-width: 10
                            }

                            .b {
                                fill: #fefefe
                            }
                        </style>
                        <path class="a"
                            d="m41.8 5c20.2 0 36.5 16.3 36.5 36.5 0 20.1-16.3 36.5-36.5 36.5-20.1 0-36.5-16.4-36.5-36.5 0-20.2 16.4-36.5 36.5-36.5z" />
                        <path class="b"
                            d="m67.3 29.9c-0.3 0.7-0.8 1-1.4 1.3q-11.5 4-23 8.1c-0.6 0.3-1.1 0.3-1.8 0.1q-11.6-4.2-23.3-8.3c-0.9-0.4-1.4-1.2-1-2 0.2-0.5 0.6-0.8 1-1q8.8-3.1 17.6-6.2 2.9-1 5.8-2 0.8-0.3 1.5-0.1 11.7 4.2 23.4 8.3c0.6 0.2 0.9 0.6 1.2 1.2q0 0.3 0 0.6zm-40.2 7.6c1.8 0.7 3.6 1.3 5.3 1.9q3.9 1.4 7.9 2.8c1.2 0.5 2.3 0.4 3.5 0q6.2-2.3 12.5-4.5c0.1 0 0.3-0.1 0.5-0.2q0.1 0.3 0.1 0.5-0.1 2.9 0 5.7c0 1-0.4 1.8-1.1 2.4-1.2 1-2.5 1.5-3.9 2-3.8 1.2-7.6 1.5-11.5 1.4-3.4-0.2-6.7-0.7-9.9-2-0.8-0.4-1.6-0.9-2.3-1.4-0.8-0.6-1.1-1.4-1.1-2.4q0-2.8 0-5.6c0-0.2 0-0.3 0-0.6zm36.6-2.4q0 0.3 0 0.6 0 5.9 0 11.8c0 0.3 0.1 0.6 0.4 0.8 1.5 1.2 1.5 3.5-0.1 4.7-0.2 0.2-0.3 0.4-0.3 0.6q0 3.4 0 6.8c0 0.6-0.2 1.1-0.8 1.4q-0.7 0.4-1.5-0.1c-0.5-0.3-0.7-0.7-0.6-1.3q0-3.4 0-6.7c0-0.3-0.1-0.5-0.4-0.7-1.5-1.2-1.5-3.5 0-4.7 0.3-0.2 0.4-0.5 0.4-0.8q0-5.4-0.1-10.9c0-0.4 0.1-0.5 0.5-0.6 0.8-0.3 1.6-0.6 2.5-0.9z" />
                    </svg>
                    {{ __('Skills') }}
                </h2>
                @foreach ($job_candidate->JobSkill as $job_skill)
                    <div class="content">
                        <p>{!! !empty($job_skill->description) ? $job_skill->description : $job_candidate->skill_description !!}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @if (!isset($preview))
        @include('recruitment::templates.script');
    @endif
</body>

</html>
