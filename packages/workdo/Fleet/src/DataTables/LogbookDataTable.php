<?php

namespace Workdo\Fleet\DataTables;

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
use Workdo\Fleet\Entities\Logbook;

class LogbookDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['driver_name', 'vehicle_name','rate','total_price'];

        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('driver_name', function (Logbook $logbook) {
                return isset($logbook->driver_name) ? (isset($logbook->driver->client) ? $logbook->driver->client->name : $logbook->driver->name ) : '-';
            })
            ->filterColumn('driver_name', function ($query, $keyword) {
                $query->whereHas('driver', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%")
                      ->orWhereHas('client', function ($qc) use ($keyword) {
                          $qc->where('name', 'like', "%$keyword%");
                      });
                });
            })
            ->editColumn('vehicle_name', function (Logbook $logbook) {
                return isset($logbook->VehicleType) ? $logbook->VehicleType->name : '';
            })
            ->filterColumn('vehicle_name', function ($query, $keyword) {
                $query->whereHas('VehicleType', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            })
            ->editColumn('total_price', function (Logbook $logbook) {
                return currency_format_with_sym($logbook->total_price);
            })
            ->editColumn('rate', function (Logbook $logbook) {
                return isset($logbook->item_rate->name) ? $logbook->item_rate->name : '-';
            })
            ->filterColumn('rate', function ($query, $keyword) {
                $query->whereHas('item_rate', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%$keyword%");
                });
            });

            if (\Laratrust::hasPermission('vehicle show') ||
            \Laratrust::hasPermission('vehicle edit') ||
            \Laratrust::hasPermission('vehicle delete')) {

            $dataTable->addColumn('action', function ($logbook) {
                return view('fleet::logbook.action', compact('logbook'));
            });
            $rowColumn[] = 'action';
        }

        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Logbook $model,Request $request): QueryBuilder
    {
        $logbook = Logbook::where('workspace', '=', getActiveWorkSpace())->where('created_by', creatorId());

        if (($request->driver_name) && !empty($request->driver_name)) {
            $logbook =  $logbook->where('driver_name', $request->driver_name);
        }

        if ($request->vehicle_name && !empty($request->vehicle_name)) {
            $logbook =  $logbook->where('vehicle_name', $request->vehicle_name);
        }

        if ($request->start_date && !empty($request->start_date)) {
            $logbook->whereDate('created_at', '=', $request->input('start_date'));
        }
        return $logbook;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
        ->setTableId('transfers-table')
        ->columns($this->getColumns())
        ->ajax([
            'data' => 'function(d) {

                var driver_name = $("select[name=driver_name]").val();
                d.driver_name = driver_name

                var vehicle_name = $("select[name=vehicle_name]").val();
                d.vehicle_name = vehicle_name

                var start_date = $("input[name=start_date]").val();
                d.start_date = start_date
                }'
                ,
                ])
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
                $("body").on("click", "#applyfilter", function() {

                    if (!$("select[name=driver_name]").val() && !$("select[name=vehicle_name]").val() && !$("input[name=start_date]").val()) {
                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                        return;
                    }

                    $("#transfers-table").DataTable().draw();
                });

                $("body").on("click", "#clearfilter", function() {
                    $("select[name=driver_name]").val("")
                    $("select[name=vehicle_name]").val("")
                    $("input[name=start_date]").val("")
                    $("#transfers-table").DataTable().draw();
                });

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
            Column::make('id')->searchable(false)->visible(false)->printable(false)->exportable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('driver_name')->title(__('Driver Name')),
            Column::make('vehicle_name')->title(__('Vehicle name')),
            Column::make('start_date')->title(__('Date')),
            Column::make('rate')->title(__('Rate')),
            Column::make('total_price')->title(__('Total Price')),
            Column::make('start_odometer')->title(__('Start Odometer'))->searchable(false),
            Column::make('end_odometer')->title(__('End Odometer'))->searchable(false),
            Column::make('total_distance')->title(__('Total distance'))->searchable(false),
        ];

        if (\Laratrust::hasPermission('fleet logbook show') ||
            \Laratrust::hasPermission('fleet logbook edit') ||
            \Laratrust::hasPermission('fleet logbook delete'))
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
        return 'Logbooks_' . date('YmdHis');
    }
}
