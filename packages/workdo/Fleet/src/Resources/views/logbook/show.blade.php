<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <table class="table modal-table">
                <tbody> 
                    <tr>
                        <th>{{__('Driver name')}}</th>
                        <td>{{ !empty($logbook->driver_name) ? (isset($logbook->driver->client) ? $logbook->driver->client->name : $logbook->driver->name ) : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Vehicle Name')}}</th>
                        <td>{{ !empty($logbook->VehicleType) ? $logbook->VehicleType->name : '' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Start Date')}}</th>
                        <td>{{ !empty($logbook->start_date) ? $logbook->start_date :'-'}}</td>
                    </tr>
                    <tr>
                        <th >{{__('End Date')}}</th>
                        <td>{{ !empty($logbook->end_date) ? $logbook->end_date:'-'}}</td>
                    </tr>
                    <tr>
                        <th>{{__('Start Odometer')}}</th>
                        <td>{{ !empty($logbook->start_odometer) ? $logbook->start_odometer :'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('End Odometer')}}</th>
                        <td>{{ !empty($logbook->end_odometer) ? $logbook->end_odometer :'-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Rate')}}</th>
                        <td> {{ !empty($logbook->item_rate->name) ? $logbook->item_rate->name : '-' }} </td>
                    </tr>
                    <tr>
                        <th>{{__('Total Distance')}}</th>
                        <td> {{ !empty($logbook->total_distance) ? $logbook->total_distance : '-' }} </td>
                    </tr>
                    <tr>
                        <th>{{__('Total Price')}}</th>
                        <td> {{ !empty(currency_format_with_sym($logbook->total_price)) ? currency_format_with_sym($logbook->total_price) : '-' }} </td>
                    </tr>
                    <tr>
                        <th>{{__('Note')}}</th>
                        <td> {{ !empty($logbook->notes) ? $logbook->notes : '-' }} </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

