<?php

namespace Workdo\Fleet\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fuel extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_name',
        'vehicle_name',
        'fill_date',
        'fuel_type',
        'quantity',
        'cost',
        'total_cost',
        'odometer_reading',
        'notes',
        'workspace',
        'created_by',
    ];

    public function driver()
    {
        return $this->hasOne(Driver::class, 'id', 'driver_name');
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_name');
    }

    public function FuelType()
    {
        return $this->hasOne(FuelType::class, 'id', 'fuel_type');
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

            $fuelTypes = Fuel::where('created_by', creatorId())
                ->where('workspace', getActiveWorkSpace())
                ->whereRaw("YEAR(fill_date) = $year")
                ->whereRaw("MONTH(fill_date) = $month");

            if(isset($request->vehicle)){
                $fuelTypes = $fuelTypes->where('vehicle_name',$request->vehicle);
            }
            $fuelTypes = $fuelTypes->get();

            $totalGallons = 0;
            $totalFallon = 0;
            $totalCost = 0;

            foreach ($fuelTypes as $fuelType) {
                $totalGallons += $fuelType->quantity;
                $totalFallon += $fuelType->cost;
                $totalCost += $fuelType->total_cost;
            }

            $totalGallonsArr[] = $totalGallons;
            $totalFallonArr[] = $totalFallon;
            $totalCostArr[] = $totalCost;
        }

        return [
            'month' => $monthNames,
            'gallons' => $totalGallonsArr,
            'fallon' => $totalFallonArr,
            'cost' => $totalCostArr,
        ];
    }

}
