<?php

namespace Workdo\Fleet\Entities;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type',
        'service_for',
        'vehicle_name',
        'maintenance_type',
        'service_name',
        'charge',
        'charge_bear_by',
        'maintenance_date',
        'priority',
        'total_cost',
        'notes',
        'workspace',
        'created_by',

    ];


    public function Employee()
    {
        return $this->hasOne(User::class, 'id', 'service_for');
    }

    public function VehicleName()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_name');
    }

    public function MaintenanceType()
    {
        return $this->hasOne(MaintenanceType::class, 'id', 'maintenance_type');
    }

    public static function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if($arrParam['duration'])
        {
            if($arrParam['duration'] == 'week')
            {
                $previous_week = strtotime("-1 week +1 day");
                for($i = 0; $i < 7 -1; $i++)
                {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }


        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];

        $arrDuration = array_reverse($arrDuration);

        foreach($arrDuration as $date => $label)
        {
            $data               = Maintenance::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = __($label);
            $arrTask['data'][]  = $data->total;
        }

        return $arrTask;
    }

    public static function getincExpBarChartData($request = null)
    {
        $monthNames = [
            __('January'), __('February'), __('March'), __('April'), __('May'), __('June'),
            __('July'), __('August'), __('September'), __('October'), __('November'), __('December')
        ];

        $totalChargeArr = [];
        $totalChargeBearArr = [];
        $totalCostArr = [];

        for ($i = 1; $i <= 12; $i++) {

            $year = now()->year;
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);

            $maintenances = Maintenance::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())
                ->whereRaw("YEAR(maintenance_date) = $year")
                ->whereRaw("MONTH(maintenance_date) = $month");

            if(isset($request->vehicle)){
                $maintenances = $maintenances->where('vehicle_name',$request->vehicle);
            }
            $maintenances = $maintenances->get();

            $totalCharge = 0;
            $totalChargeBear = 0;
            $totalCost = 0;

            foreach ($maintenances as $maintenance) {
                $totalCharge += $maintenance->charge;
                $totalChargeBear += $maintenance->charge_bear_by;
                $totalCost += $maintenance->total_cost;
            }

            $totalChargeArr[] = $totalCharge;
            $totalChargeBearArr[] = $totalChargeBear;
            $totalCostArr[] = $totalCost;
        }

        return [
            'month' => $monthNames,
            'charge' => $totalChargeArr,
            'chargebear' => $totalChargeBearArr,
            'cost' => $totalCostArr,
        ];
    }
}
