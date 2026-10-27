<script>
    var selector = "body";
    if ($(selector + " .repeater").length) {
        var $dragAndDrop = $("body .repeater tbody").sortable({
            handle: '.sort-handler'
        });
        var $repeater = $(selector + ' .repeater').repeater({
            initEmpty: false,
            defaultValues: {
                'status': 1
            },
            show: function() {
                $(this).slideDown();
                var file_uploads = $(this).find('input.multi');
                if (file_uploads.length) {
                    $(this).find('input.multi').MultiFile({
                        max: 3,
                        accept: 'png|jpg|jpeg',
                        max_size: 2048
                    });
                }
                // for item SearchBox ( this function is  custom Js )
                JsSearchBox();
            },
            hide: function(deleteElement) {
                if (confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                    $(this).remove();

                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }
                    $('.subTotal').html(subTotal.toFixed(2));
                    $('.totalAmount').html(subTotal.toFixed(2));
                }
            },
            ready: function(setIndexes) {
                $dragAndDrop.on('drop', setIndexes);
            },
            isFirstItemUndeletable: true
        });
        var value = $(selector + " .repeater").attr('data-value');
        if (typeof value != 'undefined' && value.length != 0) {
            value = JSON.parse(value);
            $repeater.setList(value);
        }
    }
</script>

<script>
    $(document).ready(function() {
        function updateAmount() {
            $('.repeater tbody tr').each(function() {
                var rate = parseFloat($(this).find('.price').val());
                var distance = parseFloat($(this).find('.distance').val());
                var amount = rate * distance;
                $(this).find('.amount').text(amount.toFixed(2));
            });

            var subTotal = 0;
            $('.amount').each(function() {
                subTotal += parseFloat($(this).text());
            });
            $('.subTotal').text(subTotal.toFixed(2));
            $('.totalAmount').text(subTotal.toFixed(2));
        }

        updateAmount();
        $(document).on('change', '.price, .distance', function() {
            updateAmount();
        });
    });
</script>

@if ($acction == 'edit')
    <script>
        $(document).ready(function() {
            $("#customer").trigger('change');
            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
                for (var i = 0; i < value.length; i++) {
                    var tr = $('#sortable-table .id[value="' + value[i].id + '"]').parent();
                    tr.find('.item').val(value[i].product_id);

                    var element = tr.find('.product_type');
                    var product_id = value[i].product_id;
                    // ProductType(element, product_id, 'edit');
                    changeItem(tr.find('.item'));
                }
            }
            const elementsToRemove = document.querySelectorAll('.bs-pass-para.repeater-action-btn');
            if (elementsToRemove.length > 0) {
                elementsToRemove[0].remove();
            }
        });
    </script>

    <script>
        var sale_id = '{{ $invoice->id }}';
        function changeItem(element) {
            var iteams_id = element.val();
            var url = element.data('url');
            var el = element;
            $.ajax({
                url: '{{ route('fleet.sale.items') }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'sale_id': sale_id,
                    'item': iteams_id,
                },

                cache: false,
                success: function(data) {
                    var item = JSON.parse(data);
                    if (item != null) {
                        $(el.parent().parent().find('.quantity')).val(1);
                        $(el.parent().parent().find('.price')).val(item.rate);
                        $(el.parent().parent().find('.start_location')).val(item.start_location);
                        $(el.parent().parent().find('.end_location')).val(item.end_location);
                        $(el.parent().parent().find('.trip_type')).val(item.trip_type);
                        $(el.parent().parent().parent().find('.start_date')).val(item.start_date);
                        $(el.parent().parent().parent().find('.end_date')).val(item.end_date);
                        $(el.parent().parent().parent().find('.distance')).val(item.distance);

                        $(el.parent().parent().find('.price')).trigger('change');
                    }
                }
            });
        }
    </script>
@endif
<script>
    $(document).on('click', '[data-repeater-create]', function() {
        $('.item :selected').each(function() {
            var id = $(this).val();
            if (id != '') {
                $(".item option[value=" + id + "]").addClass("d-none");
            }
        });
    })
</script>

@php
    $company_settings = getCompanyAllSetting();
    $api = isset($company_settings['api_key']) ? $company_settings['api_key'] :'';
@endphp
<h5 class="h4 d-inline-block font-weight-400 mb-4">{{ __('Items') }}</h5>
<div class="card repeater" @if ($acction == 'edit') data-value='{!! json_encode($invoice->items) !!}' @endif>
    <div class="item-section py-4">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-12 d-flex align-items-center justify-content-md-end px-5">
                <a href="#" data-repeater-create="" class="btn btn-primary mr-2" data-toggle="modal"
                    data-target="#add-bank">
                    <i class="ti ti-plus"></i> {{ __('Add item') }}
                </a>
            </div>
        </div>
    </div>

    <div class="card-body table-border-style mt-2">
        <div class="table-responsive">
            <table class="table  mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                <thead>
                    <tr>
                        {{-- <th>{{ __('Item Type') }}</th> --}}
                        <th>{{ __('Vehicle') }}</th>
                        <th>{{ __('Start Location') }}</th>
                        <th>{{ __('End Location') }}</th>
                        <th>{{ __('Trip Type') }}</th>
                        <th>{{ __('Rate') }}</th>
                        <th>{{ __('Distance') }}</th>
                        <th class="text-end">{{ __('Amount') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="ui-sortable" data-repeater-item>
                   <div>
                    <tr>
                        {{ Form::hidden('id', null, ['class' => 'form-control id']) }}
                        {{ Form::hidden('product_type', 'product', ['class' => 'form-control product_type']) }}

                        <td width="25%" class="form-group pt-0 ps-4 product_div">
                            <select name="product_id" class="form-control product_id item  js-searchBox"
                                 required>
                                @foreach ($fleetProducts as $key => $fleetProduct)
                                    <option value="{{ $key }}">{{ $fleetProduct }}</option>
                                @endforeach
                            </select>
                            @if (empty($fleet_products_count))
                                <div class=" text-xs">{{ __('Please create Product first.') }}
                                    <a
                                        href="{{ route('vehicle.index') }}"><b>{{ __('Add Vehicle') }}</b></a>
                                </div>
                            @endif
                        </td>

                        <td>
                            <div class="form-group location-input input-group search-form mb-0" style="width: 160px">
                                {{ Form::text('start_location', null, ['class' => 'form-control start_location', 'required' => 'required','id' =>'ship-address','placeholder' => __('Start Location')]) }}
                            </div>
                        </td>
                        <td>
                            <div class="form-group location-input input-group search-form mb-0" style="width: 160px">
                                {{ Form::text('end_location', null, ['class' => 'form-control end_location', 'required' => 'required','id' =>'ship-addresses','placeholder' => __('End Location')]) }}
                            </div>
                        </td>
                        <td>
                            <div class="form-group location-input input-group search-form mb-0" style="width: 160px">
                                {{ Form::select('trip_type', ['Single Trip' => 'Single Trip', 'Round Trip' => 'Round Trip'], null, ['class' => 'form-control trip_type', 'required' => 'required', 'placeholder' => 'Select Trip Type']) }}
                            </div>
                        </td>
                        <td>
                            <div class="form-group price-input input-group search-form mb-0" style="width: 160px">
                                {{ Form::number('rate', '', ['class' => 'form-control price', 'required' => 'required', 'placeholder' => __('Price'), 'required' => 'required']) }}
                                <span class="input-group-text bg-transparent">{{ isset($company_settings['defult_currancy_symbol']) ? $company_settings['defult_currancy_symbol'] : '' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="form-group location-input input-group search-form mb-0" style="width: 160px">
                                {{ Form::number('distance',null, ['class' => 'form-control distance', 'required' => 'required', 'placeholder' => __('Distance'), 'required' => 'required']) }}
                            </div>
                        </td>
                        <input type="hidden" name="quantity" class="quantity" value="1">
                        <input type="hidden" name="discount" class="discount" value="0">
                        {{ Form::hidden('tax', '', ['class' => 'form-control tax text-dark']) }}
                        {{ Form::hidden('itemTaxPrice', '', ['class' => 'form-control itemTaxPrice']) }}
                        {{ Form::hidden('itemTaxRate', '', ['class' => 'form-control itemTaxRate']) }}

                        <td class="text-end amount">{{ __('0.00') }}</td>
                        <td>
                            <div class="action-btn ms-2 float-end mb-3" data-repeater-delete>
                                <a href="#!"
                                    class="mx-3 btn btn-sm d-inline-flex align-items-center m-2 p-2 bg-danger">
                                      <i class="ti ti-trash text-white" data-bs-toggle="tooltip"
                                      data-bs-original-title="{{ __('Delete') }}" ></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="form-group" style="width: 250px">
                            {{ Form::date('start_date',null, ['class' => 'form-control start_date', 'required' => 'required', 'placeholder' => __('Start date'), 'required' => 'required']) }}
                        </td>
                        <td class="form-group" style="width: 160px">
                            {{ Form::date('end_date',null, ['class' => 'form-control end_date', 'required' => 'required', 'placeholder' => __('End date'), 'required' => 'required']) }}
                        </td>

                        <td colspan="3" class="form-group">
                            {{ Form::textarea('description', '', ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('Description')]) }}
                        </td>
                        <td></td>
                    </tr>
                   </div>
                </tbody>
                <tfoot>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td></td>
                        <td><strong>{{ __('Sub Total') }}
                                ({{ isset($company_settings['defult_currancy_symbol']) ? $company_settings['defult_currancy_symbol'] : '' }})</strong>
                        </td>
                        <td class="text-end subTotal">{{ __('0.00') }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td></td>
                        <td><strong>{{ __('Discount') }}
                                ({{ isset($company_settings['defult_currancy_symbol']) ? $company_settings['defult_currancy_symbol'] : '' }})</strong>
                        </td>
                        <td class="text-end totalDiscount">{{ __('0.00') }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td class="blue-text"><strong>{{ __('Total Amount') }}
                                ({{ isset($company_settings['defult_currancy_symbol']) ? $company_settings['defult_currancy_symbol'] : '' }})</strong>
                        </td>
                        <td class="text-end totalAmount blue-text">{{ __('0.00') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if(isset($company_settings['is_enable']) == 'on' && !empty($api))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $api }}&callback=initAutocomplete&libraries=places&v=weekly" defer></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("start-address").focus();
        });
    </script>

    <script type="text/javascript">
        function addaddress(address) {
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

        $(document).on("click", ".delete_address", function() {
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
                componentRestrictions: {
                    country: ["us", "ca", "in"]
                },
                fields: ["address_components", "geometry"],
                types: ["address"],
            });
            // address1Field.focus();

            autocomplete = new google.maps.places.Autocomplete(address1Fields, {
                componentRestrictions: {
                    country: ["us", "ca", "in"]
                },
                fields: ["address_components", "geometry"],
                types: ["address"],
            });
            // address1Fields.focus();

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
            }
            location_data = address1Field.value;
            addaddress(location_data);
        }
        window.initAutocomplete = initAutocomplete;
    </script>
@endif
<script>
    var today = new Date();

    $("#due_date").flatpickr({
        // enableTime: true,
        dateFormat: "Y-m-d",
        // mode: "range",
        "locale": {
            "firstDayOfWeek": 7
        },
        time_24hr: true,
        minDate: today,
        onChange: function(selectedDates, dateStr, instance) {},
    });

    $("#start_date").flatpickr({
        // enableTime: true,
        dateFormat: "Y-m-d",
        // mode: "range",
        "locale": {
            "firstDayOfWeek": 7
        },
        time_24hr: true,
        minDate: today,
        onChange: function(selectedDates, dateStr, instance) {},
    });
</script>
