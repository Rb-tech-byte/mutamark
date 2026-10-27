<div class="modal-body">
    <div class="row">
        <div class="col-lg-12 mt-2">
            <table class="table modal-table">
                <tbody>
                    <tr>
                        <th>{{__('Property')}}</th>
                        <td>{{ $expense_tracking->property ? $expense_tracking->property->name : '-' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Amount')}}</th>
                        <td>{{ $expense_tracking->amount }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Category')}}</th>
                        <td>{{ $expense_tracking->category }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Expense Date')}}</th>
                        <td>{{ $expense_tracking->expense_date }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Description')}}</th>
                        <td style="white-space: break-spaces;">{{ $expense_tracking->description }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
