@extends('layouts.main')
@section('page-title')
    {{ __('Driver Detail') }}
@endsection
@section('page-breadcrumb')
    {{ __('Driver') }},
    {{ __('Detail') }}
@endsection
@push('css')
    <style>
        #card-element {
            border: 1px solid #a3afbb !important;
            border-radius: 10px !important;
            padding: 10px !important;
        }
    </style>
@endpush
@push('scripts')
    <script src="{{ asset('assets/js/plugins/dropzone-amd-module.min.js') }}"></script>
    <script>
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzonewidget", {
            url: "{{ route('driver.file.upload', [$driver->id]) }}",
            success: function(file, response) {
                if (response.is_success) {
                    // dropzoneBtn(file, response);
                    location.reload();
                    myDropzone.removeFile(file);
                    toastrs('{{ __('Success') }}', 'File Successfully Uploaded', 'success');
                } else {
                    location.reload();
                    myDropzone.removeFile(response.error);
                    toastrs('Error', response.error, 'error');
                }
            },
            error: function(file, response) {
                myDropzone.removeFile(file);
                location.reload();
                if (response.error) {
                    toastrs('Error', response.error, 'error');
                } else {
                    toastrs('Error', response, 'error');
                }
            }
        });
        myDropzone.on("sending", function(file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("driver_id", {{ $driver->id }});
        });
    </script>
@endpush
@section('content')
    <div class="row justify-content-between align-items-center mb-3">
        <div class="col-md-6">
            <ul class="nav nav-pills nav-fill cust-nav information-tab" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="details-tab" data-bs-toggle="pill" data-bs-target="#details"
                        type="button">{{ __('Details') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="attechment-tab" data-bs-toggle="pill"
                        data-bs-target="#attechment" type="button">{{ __('Attachment') }}</button>
                </li>
                {{-- <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vehicle-attechment-tab" data-bs-toggle="pill"
                        data-bs-target="#vehicle-attechment" type="button">{{ __('Attachment') }}</button>
                </li>
            </ul> --}}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade active show" id="details" role="tabpanel" aria-labelledby="pills-user-tab-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="row row-gap">
                                @php
                                    $driver_name = App\Models\User::find($driver->client_id);
                                @endphp
                                <div class="col-md-6">
                                    <ul class="p-0 list-none mb-0">
                                        <li class="d-flex gap-2 mb-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('Name :') }}</strong>
                                           {{ $driver_name->name ?? $driver->name ?? '-' }}</p>
                                        </li>
                                        <li class="d-flex gap-2 mb-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('Date of birth :') }}</strong>
                                            {{ $driver->dob }}</p>
                                        </li>
                                        <li class="d-flex gap-2 mb-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('License Number :') }}</strong>
                                            {{ !empty($driver->lincese_number) ? $driver->lincese_number : '-' }}</p>
                                        </li>
                                        <li class="d-flex gap-2 mb-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('License Expire Date :') }}</strong>
                                            {{ !empty($driver->expiry_date) ? $driver->expiry_date : '-' }}</p>
                                        </li>
                                        <li class="d-flex gap-2 mb-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('Working time :') }}</strong>
                                            {{ !empty($driver->Working_time) ? $driver->Working_time : '-' }}</p>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <pc class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('Mobile number :') }}</strong>
                                            {{ !empty($driver->phone) ? $driver->phone : '-' }}</pc>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-none p-0 mb-0">
                                        <li class="d-flex fap-2 mb-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('Join Date :') }}</strong>
                                            {{ !empty($driver->join_date) ? $driver->join_date : '-' }}</p>
                                        </li>
                                        <li class="d-flex gap-2 mb-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('License Type :') }}</strong>
                                            {{ !empty($driver->lincese->name) ? $driver->lincese->name : '-' }}</p>
                                        </li>
                                        <li class="d-flex gap-2 mb-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('Driver Status :') }}</strong>
                                            {{ !empty($driver->driver_status) ? $driver->driver_status : '-' }}</p>
                                        </li>
                                        <li class="d-flex gap-2">
                                            <p class="mb-0 fs-5 text-capitalize">
                                            <strong>{{ __('Address :') }}</strong>
                                            {{ !empty($driver->address) ? $driver->address : '-' }}</p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="attechment" role="tabpanel" aria-labelledby="pills-user-tab-2">
                    <div class="row">
                        <h5 class="d-inline-block my-3">{{ __('Attachments') }}</h5>
                        <div class="col-lg-3">
                            <div class="card border-primary border">
                                <div class="card-body table-border-style">
                                    <div class="col-md-12 dropzone browse-file" id="dropzonewidget">
                                        <div class="dz-message my-5" data-dz-message>
                                            <span>{{ __('Drop files here to upload') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-9">
                            <div class="card border-primary border">
                                <div class="card-body table-border-style">
                                    <div class="table-responsive">
                                        <table class="table mb-0 pc-dt-simple" id="attachment">
                                            <thead>
                                                <tr>
                                                    <th class="text-dark">{{ __('#') }}</th>
                                                    <th class="text-dark">{{ __('File Name') }}</th>
                                                    <th class="text-dark">{{ __('File Size') }}</th>
                                                    <th class="text-dark">{{ __('Date Created') }}</th>
                                                    <th class="text-dark">{{ __('Status') }}</th>
                                                    <th class="text-dark">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            @forelse($driver_attachment as $key =>$attachment)
                                                <td>{{ ++$key }}</td>
                                                <td>{{ $attachment->file_name }}</td>
                                                <td>{{ $attachment->file_size }}</td>
                                                <td>{{ company_date_formate($attachment->created_at) }}</td>
                                                {{-- <td>{{ $attachment->file_status }}</td> --}}
                                                <td>
                                                    @if ($attachment->file_status == 0)
                                                        <span
                                                            class="status_badge badge bg-warning p-2 px-3">{{ __('Panding') }}</span>
                                                    @elseif($attachment->file_status == 1)
                                                        <span
                                                            class="status_badge badge bg-primary p-2 px-3">{{ __('Verify') }}</span>
                                                    @elseif($attachment->file_status == 2)
                                                        <span
                                                            class="status_badge badge bg-danger p-2 px-3">{{ __('Unverified') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($attachment->file_status == 0)
                                                        <div class="action-btn me-2">
                                                            <form action="{{ route('driver.status', [$attachment->id,1]) }}" method="POST" class="m-0">
                                                                @csrf
                                                                @method('POST')
                                                                <a href="#" class="btn btn-sm align-items-center bs-pass-para show_confirm bg-warning"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Verify" aria-label="Verify"
                                                                    data-confirm="{{ __('Are You Sure?') }}"
                                                                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="verify-form-{{ $attachment->id }}">
                                                                    <i class="ti ti-check text-white"></i>
                                                                </a>
                                                            </form>
                                                        </div>
                                                        <div class="action-btn me-2">
                                                            <form action="{{ route('driver.status', [$attachment->id, 2]) }}" method="POST" class="m-0">
                                                                @csrf
                                                                @method('POST')
                                                                <a href="#" class="btn btn-sm align-items-center bs-pass-para show_confirm bg-secondary"
                                                                    data-bs-toggle="tooltip" title=""
                                                                    data-bs-original-title="Unverified" aria-label="Unverified"
                                                                    data-confirm="{{ __('Are You Sure?') }}"
                                                                    data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                    data-confirm-yes="unverified-form-{{ $attachment->id }}">
                                                                    <i class="ti ti-x text-white"></i>
                                                                </a>
                                                            </form>
                                                        </div>
                                                    @endif
                                                    <div class="action-btn me-2">
                                                        <a href="{{ url($attachment->file_path) }}"
                                                            class="btn btn-sm align-items-center bg-primary" data-bs-toggle="tooltip"
                                                            title="{{ __('Download') }}" target="_blank" download>
                                                            <i class="ti ti-download text-white"></i>
                                                        </a>
                                                    </div>

                                                    <div class="action-btn">
                                                        {{ Form::open(['route' => ['driver.attachment.destroy', $attachment->id], 'class' => 'm-0']) }}
                                                        @method('DELETE')
                                                        <a href="#"
                                                            class="btn btn-sm  align-items-center bs-pass-para show_confirm bg-danger"
                                                            data-bs-toggle="tooltip" title=""
                                                            data-bs-original-title="Delete" aria-label="Delete"
                                                            data-confirm="{{ __('Are You Sure?') }}"
                                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="delete-form-{{ $attachment->id }}">
                                                            <i class="ti ti-trash text-white text-white"></i>
                                                        </a>
                                                        {{ Form::close() }}
                                                    </div>
                                                </td>
                                                </tr>
                                            @empty
                                                @include('layouts.nodatafound')
                                            @endforelse
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
