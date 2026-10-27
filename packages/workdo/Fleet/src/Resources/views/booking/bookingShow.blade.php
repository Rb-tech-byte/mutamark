<div class="modal-body">
    <table class="table modal-table">
        <tbody>
            <tr>
                <th class="border-0">{{__('Customer Name')}}</th>
                <td class="text-wrap border-0">{{ !empty($booking->BookingUser) ? $booking->BookingUser->name : '' }}</td>
            </tr>
            <tr>
                <th>{{__('Vehicle Name')}}</th>
                <td
                >{{!empty($booking->vehicle) ? $booking->vehicle->name : '' }}</td>
            </tr>
            <tr>
                <th>{{__('Trip Type')}}</th>
                <td class="text-wrap">{{ !empty($booking->trip_type) ? $booking->trip_type : ''}}</td>
            </tr>
            <tr>
                <th>{{__('Start Location')}}</th>
                <td class="text-wrap">{{ !empty($booking->start_address) ? $booking->start_address : ''}}</td>
            </tr>
            <tr>
                <th>{{__('End Location')}}</th>
                <td class="text-wrap">{{!empty($booking->end_address) ? $booking->end_address : ''}}</td>
            </tr>
            <tr>
                <th>{{__('Description')}}</th>
                <td class="text-wrap">{{ !empty($booking->notes) ? $booking->notes : ''}}</td>
            </tr>
        </tbody>
    </table>
</div>
