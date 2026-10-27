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
use Workdo\Fleet\Entities\Maintenance;

class MaintenanceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['vehicle_name','maintenance_type'];

        $dataTable = (new EloquentDataTable($query))
        ->addIndexColumn()
        ->editColumn('vehicle_name', function (Maintenance $Maintenance) {
            return isset($Maintenance->VehicleName) ? $Maintenance->VehicleName->name : '';
        })
        ->filterColumn('vehicle_name', function ($query, $keyword) {
            $query->whereHas('VehicleName', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
            });
        })
        ->editColumn('maintenance_type', function (Maintenance $Maintenance) {
            return isset($Maintenance->MaintenanceType) ? $Maintenance->MaintenanceType->name : '';
        })
        ->filterColumn('maintenance_type', function ($query, $keyword) {
            $query->whereHas('MaintenanceType', function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%");
            });
        });
        if (\Laratrust::hasPermission('maintenance edit') ||
            \Laratrust::hasPermission('maintenance delete')) {

            $dataTable->addColumn('action', function ($Maintenance) {
                return view('fleet::maintenance.action', compact('Maintenance'));
            });
            $rowColumn[] = 'action';
        }

        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Maintenance $model,Request $request): QueryBuilder
    {
        if (Auth::user()->type == "company") {

            $query = Maintenance::where('created_by', '=', creatorId())->with('MaintenanceType','VehicleName')->where('workspace', getActiveWorkSpace());
            if (!empty($request->service_type)) {
                $query->where('service_type', 'like', '%' . $request->service_type . '%');
            }
            if (!empty($request->maintenance_type)) {
                $query->where('maintenance_type', '=', $request->maintenance_type);
            }
            if (!empty($request->service_name)) {
                $query->where('service_name', 'like', '%' . $request->service_name . '%');
            }
            if (!empty($request->priority)) {
                $query->where('priority', '=', $request->priority);
            }

        } else {

            $query = Maintenance::where('created_by', creatorId())->with('MaintenanceType','VehicleName')->where('service_for', \Auth::user()->id)->where('workspace', getActiveWorkSpace());
            if (!empty($request->service_type)) {
                    $query->where('service_type', 'like', '%' . $request->service_type . '%');
                }
                if (!empty($request->maintenance_type)) {
                    $query->where('maintenance_type', '=', $request->maintenance_type);
                }
                if (!empty($request->service_name)) {
                    $query->where('service_name', 'like', '%' . $request->service_name . '%');
                }
                if (!empty($request->priority)) {
                    $query->where('priority', '=', $request->priority);
                }
        }
       return $query;
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
                var service_type = $("input[name=service_type]").val();
                d.service_type = service_type

                var service_name = $("input[name=service_name]").val();
                d.service_name = service_name

                var maintenance_type = $("select[name=maintenance_type]").val();
                d.maintenance_type = maintenance_type

                var priority = $("select[name=priority]").val();
                d.priority = priority
                }',
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

                    if (!$("input[name=service_type]").val() && !$("input[name=service_name]").val() && !$("select[name=maintenance_type]").val() && !$("select[name=priority]").val()) {
                        toastrs("Error!", "Please select Atleast One Filter ", "error");
                        return;
                    }

                    $("#transfers-table").DataTable().draw();
                });

                $("body").on("click", "#clearfilter", function() {
                    $("input[name=service_type]").val("")
                    $("input[name=service_name]").val("")
                    $("select[name=maintenance_type]").val("")
                    $("select[name=priority]").val("")
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
            Column::make('service_type')->title(__('Service Type')),
            Column::make('vehicle_name')->title(__('Vehicle Name')),
            Column::make('maintenance_type')->title(__('Maintenance Type')),
            Column::make('service_name')->title(__('Maintenance Service Name')),
            Column::make('maintenance_date')->title(__('Maintenance Date')),
            Column::make('priority')->title(__('Priority')),

        ];

        if (\Laratrust::hasPermission('maintenance edit') ||
            \Laratrust::hasPermission('maintenance delete'))
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
        return 'Maintenances_' . date('YmdHis');
    }
}
