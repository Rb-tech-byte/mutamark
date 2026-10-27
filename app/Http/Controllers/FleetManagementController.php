<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Workdo\Fleet\Entities\Vehicle;
use Workdo\Fleet\Entities\Driver;
use Workdo\Fleet\Entities\Booking;
use Workdo\Fleet\Entities\Fuel;
use Workdo\Fleet\Entities\Maintenance;
use Workdo\Fleet\Entities\VehicleType;
use Workdo\Fleet\Entities\FuelType;
use Workdo\Fleet\Entities\MaintenanceType;

class FleetManagementController extends Controller
{
    protected function getCreatorId()
    {
        return Auth::check() ? (creatorId() ?: 2) : 2;
    }

    protected function getWorkspace()
    {
        return function_exists('getActiveWorkSpace') ? getActiveWorkSpace() : 1;
    }

    public function index(Request $request)
    {
        $creatorId = $this->getCreatorId();
        $workspace = $this->getWorkspace();

        $vehicles = Vehicle::with(['VehicleType', 'FuelType', 'driver'])
            ->where('created_by', $creatorId)
            ->latest()
            ->get();

        $drivers = Driver::where('created_by', $creatorId)
            ->latest()
            ->get();

        $bookings = Booking::with(['vehicle', 'driver'])
            ->where('created_by', $creatorId)
            ->latest()
            ->get();

        $fuels = Fuel::with(['vehicle', 'driver', 'FuelType'])
            ->where('created_by', $creatorId)
            ->latest()
            ->get();

        $maintenances = Maintenance::with(['VehicleName', 'MaintenanceType'])
            ->where('created_by', $creatorId)
            ->latest()
            ->get();

        $vehicleTypes = VehicleType::where('created_by', $creatorId)->get();
        $fuelTypes = FuelType::where('created_by', $creatorId)->get();
        $maintenanceTypes = MaintenanceType::where('created_by', $creatorId)->get();

        $stats = [
            'total_vehicles' => $vehicles->count(),
            'active_dispatches' => $bookings->whereIn('status', ['Approved', 'On Trip', 'Active', 'In Progress'])->count(),
            'total_fuel_cost' => (float) $fuels->sum('total_cost'),
            'total_maintenance_cost' => (float) $maintenances->sum('total_cost'),
        ];

        return Inertia::render('Fleet/Index', [
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'bookings' => $bookings,
            'fuels' => $fuels,
            'maintenances' => $maintenances,
            'vehicleTypes' => $vehicleTypes,
            'fuelTypes' => $fuelTypes,
            'maintenanceTypes' => $maintenanceTypes,
            'stats' => $stats,
        ]);
    }

    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'lincense_plate' => 'required|string|max:50',
            'vehicle_type' => 'nullable|integer',
            'fuel_type' => 'nullable|integer',
            'driver_name' => 'nullable|integer',
            'vehical_id_num' => 'nullable|string|max:100',
            'model_year' => 'nullable|string|max:10',
            'seat_capacity' => 'nullable|integer',
            'status' => 'nullable|string|max:50',
        ]);

        $creatorId = $this->getCreatorId();
        $workspace = $this->getWorkspace();

        $vehicle = new Vehicle();
        $vehicle->fill($validated);
        $vehicle->status = $validated['status'] ?? 'Available';
        $vehicle->created_by = $creatorId;
        $vehicle->workspace = $workspace;
        $vehicle->save();

        return back()->with('success', __('Vehicle added to fleet successfully.'));
    }

    public function storeDriver(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:191',
            'lincese_number' => 'required|string|max:100',
            'lincese_type' => 'nullable|string|max:50',
            'expiry_date' => 'nullable|date',
            'join_date' => 'nullable|date',
            'address' => 'nullable|string|max:255',
        ]);

        $creatorId = $this->getCreatorId();
        $workspace = $this->getWorkspace();

        $driver = new Driver();
        $driver->fill($validated);
        $driver->user_id = $creatorId;
        $driver->driver_status = 'Active';
        $driver->created_by = $creatorId;
        $driver->workspace = $workspace;
        $driver->save();

        return back()->with('success', __('Driver registered successfully.'));
    }

    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:191',
            'vehicle_name' => 'required|integer',
            'driver_name' => 'nullable|integer',
            'trip_type' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'start_address' => 'required|string|max:255',
            'end_address' => 'required|string|max:255',
            'total_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $creatorId = $this->getCreatorId();
        $workspace = $this->getWorkspace();

        $booking = new Booking();
        $booking->fill($validated);
        $booking->status = 'Approved';
        $booking->created_by = $creatorId;
        $booking->workspace = $workspace;
        $booking->save();

        return back()->with('success', __('Transport booking & dispatch created successfully.'));
    }

    public function storeFuel(Request $request)
    {
        $validated = $request->validate([
            'vehicle_name' => 'required|integer',
            'driver_name' => 'nullable|integer',
            'fill_date' => 'required|date',
            'fuel_type' => 'nullable|integer',
            'quantity' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'odometer_reading' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $creatorId = $this->getCreatorId();
        $workspace = $this->getWorkspace();

        $fuel = new Fuel();
        $fuel->fill($validated);
        $fuel->total_cost = $validated['quantity'] * $validated['cost'];
        $fuel->created_by = $creatorId;
        $fuel->workspace = $workspace;
        $fuel->save();

        return back()->with('success', __('Fuel consumption recorded successfully.'));
    }

    public function storeMaintenance(Request $request)
    {
        $validated = $request->validate([
            'vehicle_name' => 'required|integer',
            'service_type' => 'required|string|max:100',
            'service_name' => 'nullable|string|max:191',
            'maintenance_date' => 'required|date',
            'priority' => 'required|string|max:50',
            'total_cost' => 'required|numeric|min:0',
            'charge' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $creatorId = $this->getCreatorId();
        $workspace = $this->getWorkspace();

        $maintenance = new Maintenance();
        $maintenance->fill($validated);
        $maintenance->created_by = $creatorId;
        $maintenance->workspace = $workspace;
        $maintenance->save();

        return back()->with('success', __('Vehicle maintenance scheduled successfully.'));
    }

    public function updateBookingStatus(Request $request, $id)
    {
        $booking = Booking::where('created_by', $this->getCreatorId())->findOrFail($id);
        $status = $request->input('status', 'Completed');
        $booking->status = $status;
        $booking->save();

        return back()->with('success', __('Dispatch status updated to ' . $status));
    }
}
