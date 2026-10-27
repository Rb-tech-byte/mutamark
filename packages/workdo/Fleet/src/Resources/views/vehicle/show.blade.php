@extends('layouts.main')
@section('page-title')
    {{ __('Vehicle Detail') }}
@endsection
@section('page-breadcrumb')
    {{ __('Vehicle') }},
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
            url: "{{ route('vehicle.file.upload', [$vehicle->id]) }}",
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
            formData.append("vehicle_id", {{ $vehicle->id }});
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
                    <button class="nav-link" id="insurance-tab" data-bs-toggle="pill"
                        data-bs-target="#insurance" type="button">{{ __('Insurance') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bookings-tab" data-bs-toggle="pill"
                        data-bs-target="#bookings" type="button">{{ __('Bookings') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="maintenance-tab" data-bs-toggle="pill"
                        data-bs-target="#maintenance" type="button">{{ __('Maintenance') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fuel-history-tab" data-bs-toggle="pill"
                        data-bs-target="#fuel-history" type="button">{{ __('Fuel History') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vehicle-attechment-tab" data-bs-toggle="pill"
                        data-bs-target="#vehicle-attechment" type="button">{{ __('Attachment') }}</button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade active show" id="details" role="tabpanel" aria-labelledby="pills-user-tab-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Vehicle Name:') }}</strong> {{ isset($vehicle->name) ? $vehicle->name : '' }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Registration date:') }}</strong> {{ $vehicle->registration_date }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Fuel type:') }}</strong> {{ !empty($vehicle->FuelType) ? $vehicle->FuelType->name : '' }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('License Plate:') }}</strong> {{ $vehicle->lincense_plate }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Model year:') }}</strong> {{ $vehicle->model_year }}
                                    </dd>
                                    <dd class="col-lg-6 mt-3 text-lg">
                                        <strong>{{ __('Seat Capacity:') }}</strong> {{ $vehicle->seat_capacity }}
                                    </dd>
                                </div>
                                <div class="col-md-6">
                                    <dt class="col-lg-4 h6 text-lg">
                                        <dd class="col-lg-6 mt-3 text-lg">
                                            <strong>{{ __('Vehicle Type:') }}</strong> {{ !empty($vehicle->VehicleType) ? $vehicle->VehicleType->name : '' }}
                                        </dd>
                                        <dd class="col-lg-6 mt-3 text-lg">
                                            <strong>{{ __('Register expiry date:') }}</strong> {{ $vehicle->register_ex_date }}
                                        </dd>
                                        <dd class="col-lg-6 mt-3 text-lg">
                                            <strong>{{ __('Driver name:') }}</strong> {{ !empty($vehicle->driver) ? (isset($vehicle->driver->client) ? $vehicle->driver->client->name : $vehicle->driver->name ) : '-' }}
                                        </dd>
                                        <dd class="col-lg-6 mt-3 text-lg">
                                            <strong>{{ __('Vehicle identification number:') }}</strong> {{ $vehicle->vehical_id_num }}
                                        </dd>
                                        <dd class="col-lg-6 mt-3 text-lg">
                                            <strong>{{ __('Status:') }}</strong> {{ $vehicle->status }}
                                        </dd>
                                    </dt>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="insurance" role="tabpanel" aria-labelledby="pills-user-tab-2">
                    <h5 class="h4 d-inline-block font-weight-400 my-2">{{ __('Insurance') }}</h5>
                    <div class="card mt-2">
                            <div class="card-body">
                                <div class="card-body table-border-style">
                                    <div class="table-responsive">
                                        <table class="table mb-0 pc-dt-simple" id="assets">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Insurance Provider Name') }}</th>
                                                    <th>{{ __('Vehicle name') }}</th>
                                                    <th>{{ __('Start date') }}</th>
                                                    <th>{{ __('End date') }}</th>
                                                    <th>{{ __('Recurring date') }}</th>
                                                    <th>{{ __('Recurring Period') }}</th>
                                                    <th>{{ __('Insurance Deductible') }}</th>
                                                    <th>{{ __('Initial Charge') }}</th>
                                                    <th>{{ __('Policy Number') }}</th>
                                                    <th>{{ __('Policy Document') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($insurances as $insurance)
                                                <tr>
                                                        <td> {{ isset($insurance->insurance_provider) ? $insurance->insurance_provider : '' }} </td>
                                                        <td> {{ !empty($insurance->VehicleName) ? $insurance->VehicleName->name : '' }} </td>
                                                        <td> {{ isset($insurance->start_date) ? $insurance->start_date : '' }}</td>
                                                        <td> {{ isset($insurance->end_date) ? $insurance->end_date : '' }}</td>
                                                        <td> {{ isset($insurance->scheduled_date) ? $insurance->scheduled_date : '' }}</td>
                                                        <td> {{ isset($insurance->scheduled_period) ? $insurance->scheduled_period : '' }}</td>
                                                        <td> {{ isset($insurance->deductible) ? $insurance->deductible : '' }}</td>
                                                        <td> {{ isset($insurance->charge_payable) ? $insurance->charge_payable :'' }}</td>
                                                        <td> {{ isset($insurance->policy_number) ? $insurance->policy_number : '' }}</td>
                                                        <td> {{ isset($insurance->policy_document) ? $insurance->policy_document : '-'}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="bookings" role="tabpanel" aria-labelledby="pills-user-tab-3">
                    <h5 class="h4 d-inline-block font-weight-400 my-2">{{ __('Bookings') }}</h5>
                    <div class="card mt-2">
                            <div class="card-body">
                                <div class="card-body table-border-style">
                                    <div class="table-responsive">
                                        <table class="table mb-0 pc-dt-simple" id="assets">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Start Date') }}</th>
                                                    <th>{{ __('End date') }}</th>
                                                    <th>{{ __('Amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($bookings as $booking)
                                                    <tr>
                                                        <td> {{ isset($booking->start_date) ? $booking->start_date : '' }}</td>
                                                        <td> {{ isset($booking->end_date) ? $booking->end_date : '' }}</td>
                                                        <td> {{ isset($booking->total_price) ? $booking->total_price : '' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="pills-user-tab-3">
                    <div class="card mt-2">
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table ">
                                    <thead>
                                    <tr>
                                        <th>{{__('Service type')}}</th>
                                        <th>{{__('Maintenance Type')}}</th>
                                        <th>{{__('Maintenance Service Name')}}</th>
                                        <th>{{__('Maintenance Date')}}</th>
                                        <th>{{__('Priority')}}</th>
                                        <th>{{__('Total Cost')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($maintenances as $maintenance)
                                            <tr>
                                                <td class="text-dark"><span>{{ $maintenance->service_type}}</span></td>
                                                <td class="text-dark"><span>{{ $maintenance->MaintenanceType->name}}</span></td>
                                                <td class="text-dark"><span>{{ $maintenance->service_name}}</span></td>
                                                <td class="text-dark"><span>{{ $maintenance->maintenance_date}}</span></td>
                                                <td class="text-dark"><span>{{ $maintenance->priority}}</span></td>
                                                <td class="text-dark"><span>{{ $maintenance->total_cost}}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="fuel-history" role="tabpanel" aria-labelledby="pills-user-tab-4">
                    <div class="card mt-2">
                        <div class="card-body table-border-style">
                            <div class="table-responsive">
                                <table class="table ">
                                    <thead>
                                    <tr>
                                        <th>{{__('Driver name')}}</th>
                                        <th>{{__('Vehicle Name')}}</th>
                                        <th>{{__('Fuelling date and time')}}</th>
                                        <th>{{__('Fuel type')}}</th>
                                        <th>{{__('Gallons/Liters of Fuel')}}</th>
                                        <th>{{__('Cost per Fallon/Liter')}}</th>
                                        <th>{{__('Total Cost')}}</th>
                                        <th>{{__('Reading')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fuelTypes as $fuelType)
                                            <tr>
                                                <td class="text-dark"><span>{{ $fuelType->driver->name}}</span></td>
                                                <td class="text-dark"><span>{{ $fuelType->vehicle->name}}</span></td>
                                                <td class="text-dark"><span>{{ $fuelType->fill_date}}</span></td>
                                                <td class="text-dark"><span>{{ $fuelType->FuelType->name}}</span></td>
                                                <td class="text-dark"><span>{{ $fuelType->quantity}}</span></td>
                                                <td class="text-dark"><span>{{ $fuelType->cost}}</span></td>
                                                <td class="text-dark"><span>{{ $fuelType->total_cost}}</span></td>
                                                <td class="text-dark"><span>{{ $fuelType->odometer_reading}}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="vehicle-attechment" role="tabpanel" aria-labelledby="pills-user-tab-5">
                    <div class="row">
                        <h5 class="d-inline-block my-3">{{ __('Attachments') }}</h5>
                        <div class="col-3">
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
                        <div class="col-9">
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
                                                    <th class="text-dark">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                                @forelse($attechments as $key =>$attachment)
                                                    <td>{{ ++$key }}</td>
                                                    <td>{{ $attachment->file_name }}</td>
                                                    <td>{{ $attachment->file_size }}</td>
                                                    <td>{{ company_date_formate($attachment->created_at) }}</td>
                                                    <td>
                                                        <div class="action-btn me-2">
                                                            <a href="{{ url($attachment->file_path) }}"
                                                                class="btn btn-sm align-items-center bg-primary" data-bs-toggle="tooltip"
                                                                title="{{ __('Download') }}" target="_blank" download>
                                                                <i class="ti ti-download text-white"></i>
                                                            </a>
                                                        </div>
                                                        <div class="action-btn">
                                                            {{ Form::open(['route' => ['vehicle.attachment.destroy', $attachment->id], 'class' => 'm-0']) }}
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
