<?php

namespace Workdo\Bookings\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Workdo\Bookings\Entities\BookingsAppointment;

class BookingsAppointmentDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['appointment_id','duration','client_id','staff','service','stage_id'];

        $dataTable = (new EloquentDataTable($query))
        ->addIndexColumn()
        ->editColumn('appointment_id', function (BookingsAppointment $appointment) {

            $appointmentId = BookingsAppointment::appointmentNumberFormat($appointment->appointment_id);
            $url = route('booking-appointments.show', $appointment->id);

            if (\Laratrust::hasPermission('bookings appointments show')) {
                return '<a href="#" data-ajax-popup="true" data-url="' . $url . '" data-size="lg" data-title="' . __('Show Appointments') . '" class="btn btn-outline-primary">' . $appointmentId . '</a>';
            }

            return '<a href="#" class="btn btn-outline-primary">' . $appointmentId . '</a>';
        })

        ->editColumn('duration', function (BookingsAppointment $appointment) {
            $startTime = isset($appointment->start_time) ? $appointment->start_time : '-';
            $endTime = isset($appointment->end_time) ? $appointment->end_time : '-';
            return "{$startTime} - {$endTime}";
        })

        ->editColumn('client_id', function (BookingsAppointment $appointment) {
            return isset($appointment->customerData) ? (isset($appointment->customerData->client) ? $appointment->customerData->client->name : $appointment->customerData->name ) : '-';
        })

        ->editColumn('staff', function (BookingsAppointment $appointment) {
            return isset($appointment->appointment_staff->name) ? $appointment->appointment_staff->name : '-';
        })
        ->filterColumn('staff', function ($query, $keyword) {
            $query->whereHas('appointment_staff', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
            });
        })
        ->editColumn('service', function (BookingsAppointment $appointment) {
            return isset($appointment->appointment_service->name) ? $appointment->appointment_service->name : '-';
        })
        ->filterColumn('service', function ($query, $keyword) {
            $query->whereHas('appointment_service', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
            });
        })
        ->filterColumn('stage_id', function ($query, $keyword) {
            if (stripos('Pending', $keyword) !== false) {
                $query->where('stage_id', 0);
            }
            elseif (stripos('Scheduled', $keyword) !== false) {
                $query->orWhere('stage_id', 1);
            }
            elseif (stripos('Confirmed', $keyword) !== false) {
                $query->orWhere('stage_id', 2);
            }
            elseif (stripos('Cancelled', $keyword) !== false) {
                $query->orWhere('stage_id', 3);
            }
            elseif (stripos('Completed', $keyword) !== false) {
                $query->orWhere('stage_id', 4);
            }
        })
        ->addColumn('stage_id', function ($appointment) {

            $status = [
                0 => 'bg-secondary',
                1 => 'bg-info',
                2 => 'bg-warning',
                3 => 'bg-danger',
                4 => 'bg-primary',
            ];
            $class = isset($status[$appointment->stage_id]) ? $status[$appointment->stage_id] : '';
            $badge = '<span class="badge ' . $class . ' p-2 px-3">' . BookingsAppointment::$status[$appointment->stage_id] . '</span>';
            return '<a href="#" class="d-inline-flex align-items-center"
                    data-url="' . route('bookings.appointment.status.change', $appointment->id) . '"
                    class="dropdown-item" data-ajax-popup="true" data-size="md"
                    data-title="' . __('Update Status') . '" data-bs-toggle="tooltip"
                    data-bs-original-title="' . __('Update Status') . '">' . $badge . '</a>';
            });



        if (\Laratrust::hasPermission('bookings appointments show') ||
            \Laratrust::hasPermission('bookings appointments edit') ||
            \Laratrust::hasPermission('bookings appointments delete')) {

            $dataTable->addColumn('action', function ($appointment) {
                return view('bookings::Appointments.action', compact('appointment'));
            });
            $rowColumn[] = 'action';
        }

        return $dataTable->rawColumns($rowColumn);

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(BookingsAppointment $model,Request $request)
    {
        $appointments = BookingsAppointment::where('workspace',getActiveWorkSpace())->where('created_by',creatorId());
        return $appointments;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('bookingsappointment-table')
            ->columns($this->getColumns())
            ->orderBy(0)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => "_MENU_" . __('Entries Per Page'),
                "searchPlaceholder" => __('Search...'),
                "search" => "",
                "info" => __('Showing _START_ to _END_ of _TOTAL_ entries')
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
            }');

        $exportButtonConfig = [
            'extend' => 'collection',
            'className' => 'btn btn-light-secondary dropdown-toggle',
            'text' => '<i class="ti ti-download me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Export"></i>',
            'buttons' => [
                [
                    'extend' => 'print',
                    'text' => '<i class="fas fa-print me-2"></i> ' . __('Print'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv me-2"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel me-2"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
            ],
        ];

        $buttonsConfig = array_merge([
            $exportButtonConfig,
            [
                'extend' => 'reset',
                'className' => 'btn btn-light-danger',
            ],
            [
                'extend' => 'reload',
                'className' => 'btn btn-light-warning',
            ],
        ]);

        $dataTable->parameters([
            "dom" =>  "
        <'dataTable-top'<'dataTable-dropdown page-dropdown'l><'dataTable-botton table-btn dataTable-search tb-search  d-flex justify-content-end gap-2'Bf>>
        <'dataTable-container'<'col-sm-12'tr>>
        <'dataTable-bottom row'<'col-5'i><'col-7'p>>",
            'buttons' => $buttonsConfig,
            "drawCallback" => 'function( settings ) {
                var tooltipTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=tooltip]")
                  );
                  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                  });
                  var popoverTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=popover]")
                  );
                  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                  });
                  var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                  var toastList = toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl);
                  });
            }'
        ]);

        $dataTable->language([
            'buttons' => [
                'create' => __('Create'),
                'export' => __('Export'),
                'print' => __('Print'),
                'reset' => __('Reset'),
                'reload' => __('Reload'),
                'excel' => __('Excel'),
                'csv' => __('CSV'),
            ]
        ]);

        return $dataTable;
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $column = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('no')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('appointment_id')->title(__('Appointment')),
            Column::make('date')->title(__('Date')),
            Column::make('duration')->title(__('Duration'))->searchable(false)->orderable(false),
            Column::make('client_id')->title(__('Customer')),
            Column::make('staff')->title(__('Staff')),
            Column::make('service')->title(__('Service')),
            Column::make('payment')->title(__('Payment')),
            Column::make('stage_id')->title(__('Status')),
        ];

        if (\Laratrust::hasPermission('bookings appointments show') ||
            \Laratrust::hasPermission('bookings appointments edit') ||
            \Laratrust::hasPermission('bookings appointments delete'))
        {
            $action = [
                Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)

            ];

            $column = array_merge($column,$action);
        }
        return $column;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'BookingsAppointment_' . date('YmdHis');
    }
}
