<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 mt-2">
            <table class="table modal-table mt-3">
                @php
                    $settings = getCompanyAllSetting();
                @endphp
                <tbody>
                    <tr>
                        <th>{{__('Date')}}</th>
                        <td>{{ !empty($appointment->date) ? $appointment->date:'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Service')}}</th>
                        <td> {{ isset($appointment->appointment_service->name) ? $appointment->appointment_service->name : '-'}} </td>
                    </tr>
                    <tr>
                        <th>{{__('Package')}}</th>
                        <td> {{ isset($appointment->appointment_package->name) ? $appointment->appointment_package->name : '-'}} </td>
                    </tr>
                    <tr>
                        <th>{{__('Customer')}}</th>
                        <td> {{ isset($appointment->customerData) ? (isset($appointment->customerData->client) ? $appointment->customerData->client->name : $appointment->customerData->name ) : '-' }} </td>
                    </tr>
                    <tr>
                        <th >{{__('Staff')}}</th>
                        <td> {{ isset($appointment->appointment_staff->name) ? $appointment->appointment_staff->name : '-'}} </td>
                    </tr>
                    <tr>
                        <th >{{__('Start Time')}}</th>
                        <td> {{ isset($appointment->start_time) ? $appointment->start_time : '-'}} </td>
                    </tr>
                    <tr>
                        <th >{{__('End Time')}}</th>
                        <td> {{ isset($appointment->end_time) ? $appointment->end_time : '-'}} </td>
                    </tr>
                    <tr>
                        <th >{{__('Your Country')}}</th>
                        <td> {{ isset($appointment->your_country) ? $appointment->your_country : '-'}} </td>
                    </tr>
                    <tr>
                        <th >{{__('Your State')}}</th>
                        <td> {{ isset($appointment->your_state) ? $appointment->your_state : '-'}} </td>
                    </tr>
                    <tr>
                        <th >{{__('Your City')}}</th>
                        <td> {{ isset($appointment->your_city) ? $appointment->your_city : '-'}} </td>
                    </tr>
                    <tr>
                        <th >{{__('Your Address')}}</th>
                        <td> {{ isset($appointment->your_address) ? $appointment->your_address : '-'}} </td>
                    </tr>
                    <tr>
                        <th >{{__('Your Zip Code')}}</th>
                        <td> {{ isset($appointment->your_zip_code) ? $appointment->your_zip_code : '-'}} </td>
                    </tr>
                    <tr>
                        <th >{{__('Our Country')}}</th>
                        <td> {{ isset($settings['bookings_our_country']) ? $settings['bookings_our_country'] :'-' }} </td>
                    </tr>
                    <tr>
                        <th >{{__('Our State')}}</th>
                        <td> {{ isset($settings['bookings_our_state']) ? $settings['bookings_our_state'] :'-' }} </td>
                    </tr>

                    <tr>
                        <th >{{__('Our City')}}</th>
                        <td> {{ isset($settings['bookings_our_city']) ? $settings['bookings_our_city'] :'-' }} </td>
                    </tr>

                    <tr>
                        <th >{{__('Our Zip Code')}}</th>
                        <td> {{ isset($settings['bookings_our_zip_code']) ? $settings['bookings_our_zip_code'] :'-' }} </td>
                    </tr>
                    <tr>
                        <th>{{__('Time')}}</th>
                        <td> {{ isset($appointment->start_time) ? $appointment->start_time : '-'}} - {{isset ($appointment->end_time) ? $appointment->end_time : '-'}} </td>
                    </tr>

                    <tr>
                        <th>{{__('Payment')}}</th>
                        <td> {{ isset($appointment->payment) ? $appointment->payment : '-'}} </td>
                    </tr>
                    <tr>
                        <th>{{__('Status')}}</th>
                        <td>
                            @if($appointment->stage_id == '0')
                                <span class="badge bg-secondary p-2 px-3">{{__('Pending')}}</span>
                            @elseif($appointment->stage_id == '1')
                                <span class="badge bg-info p-2 px-3">{{__('Scheduled')}}</span>
                            @elseif($appointment->stage_id == '2')
                                <span class="badge bg-warning p-2 px-3">{{__('Confirmed')}}</span>
                            @elseif($appointment->stage_id == '3')
                                <span class="badge bg-danger p-2 px-3">{{__('Cancelled')}}</span>
                            @elseif($appointment->stage_id == '4')
                                <span class="badge bg-primary p-2 px-3">{{__('Completed')}}</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
