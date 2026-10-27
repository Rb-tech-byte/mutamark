@extends('layouts.main')
@section('page-title')
    {{ __('Downloads') }}
@endsection
@section('page-breadcrumb')
    {{ __('Manage Downloads') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table mb-0 pc-dt-simple" id="products">
                            <thead>
                                <tr>
                                    <th>{{ __('Downloaded Files') }}</th>
                                    <th>{{ __('Ip Address') }}</th>
                                    <th>{{ __('Last Download') }}</th>
                                    <th>{{ __('Country') }}</th>
                                    <th>{{ __('Device') }}</th>
                                    <th>{{ __('OS') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($file_downloads as $file)
                                    @php
                                        $file_download = json_decode($file->details);

                                    @endphp
                                    <tr class="font-style">
                                        @php
                                            $path = get_file($file->file_path);

                                            $originalString = $file->file_path;
                                            $substringToRemove = 'uploads/filesshare/';

                                            $fileName = str_replace($substringToRemove, '', $originalString);
                                        @endphp
                                        <td>{{ $fileName }}</td>
                                        <td>{{ !empty($file->ip_address) ? $file->ip_address : '************' }}</td>

                                        <td>{{ !empty($file->date) ? company_datetime_formate($file->date) : '-' }}
                                        </td>
                                        <td>{{ !empty($file_download->country) ? $file_download->country : '-' }}</td>
                                        <td>{{ $file_download->device_type }}</td>
                                        <td>{{ $file_download->os_name }}</td>
                                        <td>

                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" class="mx-3 btn btn-sm align-items-center" data-size="lg" data-url="{{ route('download-detailes.show', [$file->id]) }}"
                                                data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="" data-title="{{ __('View File Download Logs') }}" data-bs-original-title="{{ __('View') }}">
                                                    <i class="ti ti-eye text-white"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
