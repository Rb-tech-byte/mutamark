@extends('layouts.main')
@section('page-title')
    {{ __('Manage Location') }}
@endsection
@section('page-breadcrumb')
{{ __('Location') }}
@endsection
@push('css')
    <link rel="stylesheet" href="{{ asset('packages/workdo/Bookings/src/Resources/assets/css/custome.css') }}">
@endpush
@section('content')
    {{ Form::open(['route' => ['bookings-location.store'], 'enctype' => 'multipart/form-data','class'=>'needs-validation','novalidate']) }}
    <div class="row mt-3">
        @php
            $settings = getCompanyAllSetting();
        @endphp
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    <div class="row">
                        <div class="form-group col-md-6 col-12 mb-3">
                            {{ Form::label('bookings_our_country', __('Country'),['class'=>'form-label']) }}<x-required></x-required>
                            {{ Form::text('bookings_our_country', isset($settings['bookings_our_country']) ? $settings['bookings_our_country'] :'', ['class' => 'form-control','placeholder'=> 'Enter Country' ,'required'=>'required']) }}
                        </div>
                        <div class="form-group col-md-6 col-12 mb-3">
                            {{ Form::label('bookings_our_state', __('State'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::text('bookings_our_state',isset($settings['bookings_our_state']) ? $settings['bookings_our_state'] :'', ['class' => 'form-control ','placeholder'=> 'Enter State' , 'required' => 'required']) }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6 col-12 mb-3">
                            {{ Form::label('bookings_our_city', __('City'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::text('bookings_our_city',isset($settings['bookings_our_city']) ? $settings['bookings_our_city'] :'', ['class' => 'form-control ','placeholder'=> 'Enter City' , 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-6 col-12 mb-3">
                            {{ Form::label('bookings_our_zip_code', __('Zip code'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::number('bookings_our_zip_code',isset($settings['bookings_our_zip_code']) ? $settings['bookings_our_zip_code'] : '', ['class' => 'form-control ','placeholder'=> 'Enter Zip code' , 'required' => 'required']) }}
                        </div>
                    </div>
                    <div class="form-group col-md-12 text-end mb-0">
                        <input class="btn btn-print-invoice  btn-primary " type="submit" value="{{ __('Save Changes') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection



