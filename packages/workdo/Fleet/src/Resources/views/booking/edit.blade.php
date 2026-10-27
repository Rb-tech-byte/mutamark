<style>
    .pac-container {
        z-index: 9999 !important;
      }
</style>
@php
    $company_settings        = getCompanyAllSetting();
    $api = isset($company_settings['api_key']) ? $company_settings['api_key'] :'';
    $is_enable = isset($company_settings['is_enable']) ? $company_settings['is_enable'] : 'off';
@endphp

{{Form::model($booking,array('route' => array('booking.update', $booking->id), 'method' => 'PUT','class'=>'needs-validation','novalidate')) }}

<div class="modal-body">
    <div class="row">
        @if(\Auth::user()->type != "client")
            <div class="form-group col-6">
                {{ Form::label('customer_name', __('Customer Name'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::select('customer_name',$customer , null, array('class' => 'form-control','required'=>'required')) }}
            </div>
        @endif
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('vehicle_name', __('Vehicle Name'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::select('vehicle_name', $vehicle, null, array('class' => 'form-control','required'=>'required')) }}
            </div>
        </div>
        <div class="form-group col-md-6  mb-1">
            <label for="datetime" class="form-label">{{ __('Start Date/Time') }}</label><x-required></x-required>
            <input class="form-control" placeholder="{{ __('Select Date/Time') }}"
                required="required" name="start_date" type="datetime-local"
                value="{{ isset($booking) ? date('Y-m-d\TH:i', strtotime($booking->start_date)) : '' }}">
        </div>
         <div class="form-group col-md-6">
            <label for="datetime" class="form-label">{{ __('End Date/Time') }}</label><x-required></x-required>
            <input class="form-control" value="{{ date('Y-m-d h:i') }}" placeholder="{{ __('Select Date/Time') }}"
                required="required" name="end_date" type="datetime-local" value="{{ isset($booking) ? date('Y-m-d\TH:i', strtotime($booking->end_date)) : '' }}">
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('start_address', __('Start Location'), ['class' => 'form-label']) !!}<x-required></x-required>
            {{ Form::text('start_address', null, array('class' => 'form-control','id'=>"ship-address",'required'=>'required','placeholder'=>__('Enter Start Location'),'autocomplete' => 'off')) }}
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('end_address', __('End Location'), ['class' => 'form-label']) !!}<x-required></x-required>
            {{ Form::text('end_address', null, array('class' => 'form-control','id'=>"ship-addresses",'required'=>'required','placeholder'=>__('Enter End Location'),'autocomplete' => 'off')) }}
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('trip_type', __('Trip Type'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::select('trip_type', ['Single Trip'=>'Single Trip', 'Round Trip'=>'Round Trip'], null, array('class' => 'form-control','required'=>'required','placeholder'=> __('Select Trip Type'))) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('total_price', __('Total Price'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::number('total_price', null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Enter Total Price'))) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('status', __('Status'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::select('status', ['Yet to start'=>'Yet to start','Completed'=>'Completed','OnGoing'=>'OnGoing','Cancelled'=>'Cancelled'], null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Select Trip Status'))) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('notes', __('Notes'),['class'=>'form-label']) }}<x-required></x-required>
                {{ Form::text('notes', null, array('class' => 'form-control','required'=>'required','placeholder'=>__('Enter Description'))) }}
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>

{{Form::close()}}

@if($is_enable == 'on' && !empty($api))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $api }}&callback=initAutocomplete&libraries=places&v=weekly" defer></script>
    <script type="text/javascript">
        function addaddress(address){
            var html = `
            <div class="form-group col-md-12 adress_div">
                <textarea  class="form-control"  name="addresses[]"  readonly>${address}</textarea>
                <button type="button" class="btn btn-sm btn-danger delete_address">
                    <i class="ti ti-trash text-white py-1"></i>
                </button>
            </div>

            `;

            $('#add_addresses').append(html);
        }

        $(document).on("click",".delete_address",function() {
                $(this).parent('.adress_div').remove();
            });

        let location_data = '';

        /**
        * @license
        * Copyright 2019 Google LLC. All Rights Reserved.
        * SPDX-License-Identifier: Apache-2.0
        */
        // This sample uses the Places Autocomplete widget to:
        // 1. Help the user select a place
        // 2. Retrieve the address components associated with that place
        // 3. Populate the form fields with those address components.
        // This sample requires the Places library, Maps JavaScript API.
        // Include the libraries=places parameter when you first load the API.
        // For example: <script
        // src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">
        let autocomplete;
        // let address1Field;
        // let address2Field;
        // let postalField;

        function initAutocomplete() {
        address1Field = document.querySelector("#ship-address");
        address1Fields = document.querySelector("#ship-addresses");

        // address2Field = document.querySelector("#address_address2");
        // postalField = document.querySelector("#address_postcode");

        // Create the autocomplete object, restricting the search predictions to
        // addresses in the US and Canada.
        autocomplete = new google.maps.places.Autocomplete(address1Field, {
        componentRestrictions: { country: ["us", "ca", "in"] },
        fields: ["address_components", "geometry"],
        types: ["address"],
        });
        address1Field.focus();

        autocomplete = new google.maps.places.Autocomplete(address1Fields, {
        componentRestrictions: { country: ["us", "ca", "in"] },
        fields: ["address_components", "geometry"],
        types: ["address"],
        });
        address1Fields.focus();

        // When the user selects an address from the drop-down, populate the
        // address fields in the form.
        autocomplete.addListener("place_changed", function() {
            fillInAddress();
            });
        }

        function fillInAddress() {
        // Get the place details from the autocomplete object.
        const place = autocomplete.getPlace();
        // let address1 = "";
        // let postcode = "";

        // Get each component of the address from the place details,
        // and then fill-in the corresponding field on the form.
        // place.address_components are google.maps.GeocoderAddressComponent objects
        // which are documented at http://goo.gle/3l5i5Mr
        for (const component of place.address_components) {
        // @ts-ignore remove once typings fixed
        const componentType = component.types[0];
        // switch (componentType) {
        // case "street_number": {
        //   address1 = `${component.long_name} ${address1}`;
        //   break;
        // }

        // case "route": {
        //   address1 += component.short_name;
        //   break;
        // }

        // case "postal_code": {
        //   postcode = `${component.long_name}${postcode}`;
        //   break;
        // }

        // case "postal_code_suffix": {
        //   postcode = `${postcode}-${component.long_name}`;
        //   break;
        // }
        // case "locality":
        //   document.querySelector("#address_locality").value = component.long_name;
        //   break;
        // case "administrative_area_level_1": {
        //   document.querySelector("#address_state").value = component.short_name;
        //   break;
        // }
        // case "country":
        //   document.querySelector("#address_country").value = component.long_name;
        //   break;
        // }

        }
        location_data = address1Field.value;
        addaddress(location_data);
        // console.log(location_data);

        // address1Field.value = address1;
        // postalField.value = postcode;
        // After filling the form with address components from the Autocomplete
        // prediction, set cursor focus on the second address line to encourage
        // entry of subpremise information such as apartment, unit, or floor number.
        // address2Field.focus();
        }
        window.initAutocomplete = initAutocomplete;

    </script>
@endif
