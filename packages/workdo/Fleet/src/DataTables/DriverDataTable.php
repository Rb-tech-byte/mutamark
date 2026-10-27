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
use Workdo\Fleet\Entities\Driver;
use App\Models\User;

class DriverDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['name','license_name'];

        $dataTable = (new EloquentDataTable($query))
        ->addIndexColumn()
        ->editColumn('name', function (User $driver) {
            $driver_name = User::find($driver->client_id);
            return $driver_name->name ?? $driver->name ?? '-';
        })
        ->editColumn('license_name', function ($query) {
            return $query->license_name ?? '-';
        });

        if (\Laratrust::hasPermission('driver show') ||
            \Laratrust::hasPermission('driver edit') ||
            \Laratrust::hasPermission('driver delete')) {

            $dataTable->addColumn('action', function (User $driver) {
                return view('fleet::driver.action', compact('driver'));
            });
            $rowColumn[] = 'action';
        }

        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model, Request $request)
    {
        return $model->where('users.workspace_id', getActiveWorkSpace())
            ->whereIn('users.type', ['staff', 'contractor'])
            ->leftJoin('drivers', 'users.id', '=', 'drivers.user_id')
            ->leftJoin('driver_permit_types', 'driver_permit_types.id', '=', 'drivers.lincese_type')
            ->select('users.*', 'users.id as ID', 'drivers.*', 'users.name as name', 'users.email as email', 'drivers.id as id', 'driver_permit_types.name as license_name','drivers.lincese_number as lincese_number','drivers.Working_time as Working_time','drivers.expiry_date as expiry_date');

    }



    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('driver-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
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
            Column::make('id')->name('users.id')->searchable(false)->visible(false)->printable(false)->exportable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('name')->title(__('Name')),
            Column::make('email')->title(__('Email')),
            Column::make('lincese_number')->title(__('Licence Number'))->name('drivers.lincese_number'),
            Column::make('license_name')->title(__('Licence Type'))->name('drivers.lincese_type'),
            Column::make('Working_time')->title(__('Working Hour'))->name('drivers.Working_time'),
            Column::make('expiry_date')->title(__('Licence Expire Date'))->name('drivers.expiry_date'),
            Column::make('join_date')->title(__('Join Date'))->searchable(false)
        ];
            if (\Laratrust::hasPermission('driver edit') ||
            \Laratrust::hasPermission('driver delete') ||
            \Laratrust::hasPermission('driver show'))
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
        return 'Driver_' . date('YmdHis');
    }
}
