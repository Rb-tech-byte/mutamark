import React, { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { formatCurrency, formatDate } from '@/utils/helpers';
import { 
    Truck, 
    Car, 
    Fuel, 
    Wrench, 
    UserCheck, 
    MapPin, 
    Calendar, 
    Plus, 
    Search, 
    CheckCircle2, 
    Clock, 
    AlertTriangle,
    Gauge,
    Navigation,
    FileText
} from 'lucide-react';

interface FleetProps {
    vehicles: any[];
    drivers: any[];
    bookings: any[];
    fuels: any[];
    maintenances: any[];
    vehicleTypes: any[];
    fuelTypes: any[];
    maintenanceTypes: any[];
    stats: {
        total_vehicles: number;
        active_dispatches: number;
        total_fuel_cost: number;
        total_maintenance_cost: number;
    };
}

export default function FleetIndex({
    vehicles = [],
    drivers = [],
    bookings = [],
    fuels = [],
    maintenances = [],
    vehicleTypes = [],
    fuelTypes = [],
    maintenanceTypes = [],
    stats = {
        total_vehicles: 0,
        active_dispatches: 0,
        total_fuel_cost: 0,
        total_maintenance_cost: 0,
    }
}: FleetProps) {
    const { t } = useTranslation();
    const [searchTerm, setSearchTerm] = useState('');
    const [activeTab, setActiveTab] = useState('vehicles');

    // Dialog states
    const [vehicleModal, setVehicleModal] = useState(false);
    const [driverModal, setDriverModal] = useState(false);
    const [bookingModal, setBookingModal] = useState(false);
    const [fuelModal, setFuelModal] = useState(false);
    const [maintenanceModal, setMaintenanceModal] = useState(false);

    // Form handlers
    const vehicleForm = useForm({
        name: '',
        lincense_plate: '',
        vehicle_type: '',
        fuel_type: '',
        driver_name: '',
        model_year: new Date().getFullYear().toString(),
        seat_capacity: 4,
        status: 'Available',
    });

    const driverForm = useForm({
        name: '',
        phone: '',
        email: '',
        lincese_number: '',
        lincese_type: 'Class C',
        expiry_date: '',
        join_date: new Date().toISOString().split('T')[0],
        address: 'Dar es Salaam, Tanzania',
    });

    const bookingForm = useForm({
        customer_name: '',
        vehicle_name: '',
        driver_name: '',
        trip_type: 'Regional Freight',
        start_date: new Date().toISOString().split('T')[0],
        end_date: '',
        start_address: 'Dar es Salaam Depot',
        end_address: 'Dodoma Regional Hub',
        total_price: '',
        notes: '',
    });

    const fuelForm = useForm({
        vehicle_name: '',
        driver_name: '',
        fill_date: new Date().toISOString().split('T')[0],
        fuel_type: '',
        quantity: '',
        cost: '3100', // Standard TZS per Liter
        odometer_reading: '',
        notes: '',
    });

    const maintenanceForm = useForm({
        vehicle_name: '',
        service_type: 'Routine Inspection & Service',
        service_name: 'Toyota Authorized Service Center',
        maintenance_date: new Date().toISOString().split('T')[0],
        priority: 'Normal',
        total_cost: '',
        notes: '',
    });

    const handleCreateVehicle = (e: React.FormEvent) => {
        e.preventDefault();
        vehicleForm.post(route('fleet.vehicles.store'), {
            onSuccess: () => {
                setVehicleModal(false);
                vehicleForm.reset();
            }
        });
    };

    const handleCreateDriver = (e: React.FormEvent) => {
        e.preventDefault();
        driverForm.post(route('fleet.drivers.store'), {
            onSuccess: () => {
                setDriverModal(false);
                driverForm.reset();
            }
        });
    };

    const handleCreateBooking = (e: React.FormEvent) => {
        e.preventDefault();
        bookingForm.post(route('fleet.bookings.store'), {
            onSuccess: () => {
                setBookingModal(false);
                bookingForm.reset();
            }
        });
    };

    const handleCreateFuel = (e: React.FormEvent) => {
        e.preventDefault();
        fuelForm.post(route('fleet.fuels.store'), {
            onSuccess: () => {
                setFuelModal(false);
                fuelForm.reset();
            }
        });
    };

    const handleCreateMaintenance = (e: React.FormEvent) => {
        e.preventDefault();
        maintenanceForm.post(route('fleet.maintenances.store'), {
            onSuccess: () => {
                setMaintenanceModal(false);
                maintenanceForm.reset();
            }
        });
    };

    const handleCompleteBooking = (id: number) => {
        router.patch(route('fleet.bookings.status', id), { status: 'Completed' });
    };

    const filteredVehicles = vehicles.filter(v => 
        v.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        v.lincense_plate?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                { label: t('Operations'), url: '#' },
                { label: t('Fleet & Transport Dispatch') }
            ]}
            pageTitle={t('Fleet & Transport Dispatch Management')}
        >
            <Head title={t('Fleet & Transport Dispatch')} />

            <div className="space-y-6">
                {/* Header Summary Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card className="border-l-4 border-l-blue-600 shadow-sm">
                        <CardContent className="p-5 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Total Vehicles')}</p>
                                <h3 className="text-2xl font-bold mt-1 text-foreground">{stats.total_vehicles}</h3>
                                <p className="text-xs text-muted-foreground mt-0.5">{t('Commercial fleet assets')}</p>
                            </div>
                            <div className="p-3 bg-blue-50 dark:bg-blue-950/40 text-blue-600 rounded-xl">
                                <Truck className="h-6 w-6" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-emerald-600 shadow-sm">
                        <CardContent className="p-5 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Active Dispatches')}</p>
                                <h3 className="text-2xl font-bold mt-1 text-emerald-600">{stats.active_dispatches}</h3>
                                <p className="text-xs text-muted-foreground mt-0.5">{t('Vehicles currently in transit')}</p>
                            </div>
                            <div className="p-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 rounded-xl">
                                <Navigation className="h-6 w-6" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-amber-600 shadow-sm">
                        <CardContent className="p-5 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Total Fuel Cost')}</p>
                                <h3 className="text-xl font-bold mt-1 text-foreground">{formatCurrency(stats.total_fuel_cost)}</h3>
                                <p className="text-xs text-muted-foreground mt-0.5">{t('Diesel & petrol consumption')}</p>
                            </div>
                            <div className="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-600 rounded-xl">
                                <Fuel className="h-6 w-6" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="border-l-4 border-l-purple-600 shadow-sm">
                        <CardContent className="p-5 flex items-center justify-between">
                            <div>
                                <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{t('Maintenance Cost')}</p>
                                <h3 className="text-xl font-bold mt-1 text-foreground">{formatCurrency(stats.total_maintenance_cost)}</h3>
                                <p className="text-xs text-muted-foreground mt-0.5">{t('Scheduled services & repairs')}</p>
                            </div>
                            <div className="p-3 bg-purple-50 dark:bg-purple-950/40 text-purple-600 rounded-xl">
                                <Wrench className="h-6 w-6" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Tabbed Operations Hub */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-4">
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-card p-3 rounded-xl border">
                        <TabsList className="grid grid-cols-2 sm:grid-cols-5 w-full sm:w-auto h-auto p-1 bg-muted/60">
                            <TabsTrigger value="vehicles" className="text-xs py-2">
                                <Truck className="h-3.5 w-3.5 mr-1.5" />
                                {t('Vehicles')} ({vehicles.length})
                            </TabsTrigger>
                            <TabsTrigger value="drivers" className="text-xs py-2">
                                <UserCheck className="h-3.5 w-3.5 mr-1.5" />
                                {t('Drivers')} ({drivers.length})
                            </TabsTrigger>
                            <TabsTrigger value="bookings" className="text-xs py-2">
                                <Navigation className="h-3.5 w-3.5 mr-1.5" />
                                {t('Trip Dispatch')} ({bookings.length})
                            </TabsTrigger>
                            <TabsTrigger value="fuels" className="text-xs py-2">
                                <Fuel className="h-3.5 w-3.5 mr-1.5" />
                                {t('Fuel Logs')} ({fuels.length})
                            </TabsTrigger>
                            <TabsTrigger value="maintenance" className="text-xs py-2">
                                <Wrench className="h-3.5 w-3.5 mr-1.5" />
                                {t('Maintenance')} ({maintenances.length})
                            </TabsTrigger>
                        </TabsList>

                        <div className="flex items-center gap-2 w-full sm:w-auto">
                            {activeTab === 'vehicles' && (
                                <Button size="sm" onClick={() => setVehicleModal(true)} className="gap-1.5">
                                    <Plus className="h-4 w-4" />
                                    {t('Add Vehicle')}
                                </Button>
                            )}
                            {activeTab === 'drivers' && (
                                <Button size="sm" onClick={() => setDriverModal(true)} className="gap-1.5">
                                    <Plus className="h-4 w-4" />
                                    {t('Register Driver')}
                                </Button>
                            )}
                            {activeTab === 'bookings' && (
                                <Button size="sm" onClick={() => setBookingModal(true)} className="gap-1.5 bg-emerald-600 hover:bg-emerald-700">
                                    <Plus className="h-4 w-4" />
                                    {t('Dispatch Trip')}
                                </Button>
                            )}
                            {activeTab === 'fuels' && (
                                <Button size="sm" onClick={() => setFuelModal(true)} className="gap-1.5 bg-amber-600 hover:bg-amber-700">
                                    <Plus className="h-4 w-4" />
                                    {t('Log Fuel')}
                                </Button>
                            )}
                            {activeTab === 'maintenance' && (
                                <Button size="sm" onClick={() => setMaintenanceModal(true)} className="gap-1.5 bg-purple-600 hover:bg-purple-700">
                                    <Plus className="h-4 w-4" />
                                    {t('Schedule Service')}
                                </Button>
                            )}
                        </div>
                    </div>

                    {/* Tab 1: Vehicles */}
                    <TabsContent value="vehicles">
                        <Card>
                            <CardHeader className="p-4 border-b flex flex-row items-center justify-between">
                                <CardTitle className="text-base font-semibold">{t('Fleet Vehicles & Heavy Assets')}</CardTitle>
                                <div className="relative w-64">
                                    <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        placeholder={t('Search vehicle or plate...')}
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-8 h-9 text-xs"
                                    />
                                </div>
                            </CardHeader>
                            <CardContent className="p-0 overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="bg-muted/40 text-muted-foreground text-xs uppercase border-b">
                                        <tr>
                                            <th className="px-4 py-3">{t('Plate Number')}</th>
                                            <th className="px-4 py-3">{t('Make & Model')}</th>
                                            <th className="px-4 py-3">{t('Type')}</th>
                                            <th className="px-4 py-3">{t('Fuel Type')}</th>
                                            <th className="px-4 py-3">{t('Assigned Driver')}</th>
                                            <th className="px-4 py-3 text-center">{t('Status')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {filteredVehicles.map((v) => (
                                            <tr key={v.id} className="hover:bg-muted/20">
                                                <td className="px-4 py-3 font-mono font-bold text-primary">
                                                    {v.lincense_plate}
                                                </td>
                                                <td className="px-4 py-3 font-medium">{v.name}</td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {v.vehicle_type?.name || v.vehicle_type || t('Commercial Truck')}
                                                </td>
                                                <td className="px-4 py-3 text-muted-foreground">
                                                    {v.fuel_type?.name || v.fuel_type || 'Diesel'}
                                                </td>
                                                <td className="px-4 py-3">
                                                    {v.driver?.name || t('Pool Vehicle / Unassigned')}
                                                </td>
                                                <td className="px-4 py-3 text-center">
                                                    <Badge variant={v.status === 'Available' ? 'secondary' : 'default'} className={
                                                        v.status === 'Available' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400' :
                                                        v.status === 'On Trip' ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-400' :
                                                        'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400'
                                                    }>
                                                        {v.status || 'Available'}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        ))}
                                        {filteredVehicles.length === 0 && (
                                            <tr>
                                                <td colSpan={6} className="text-center py-8 text-muted-foreground">
                                                    {t('No vehicles found matching criteria.')}
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 2: Drivers */}
                    <TabsContent value="drivers">
                        <Card>
                            <CardHeader className="p-4 border-b">
                                <CardTitle className="text-base font-semibold">{t('Transport & Logistics Drivers')}</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0 overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="bg-muted/40 text-muted-foreground text-xs uppercase border-b">
                                        <tr>
                                            <th className="px-4 py-3">{t('Driver Name')}</th>
                                            <th className="px-4 py-3">{t('Phone Number')}</th>
                                            <th className="px-4 py-3">{t('Permit Number')}</th>
                                            <th className="px-4 py-3">{t('Class')}</th>
                                            <th className="px-4 py-3">{t('Permit Expiry')}</th>
                                            <th className="px-4 py-3 text-center">{t('Status')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {drivers.map((d) => (
                                            <tr key={d.id} className="hover:bg-muted/20">
                                                <td className="px-4 py-3 font-medium">{d.name}</td>
                                                <td className="px-4 py-3 font-mono">{d.phone}</td>
                                                <td className="px-4 py-3 font-mono text-muted-foreground">{d.lincese_number}</td>
                                                <td className="px-4 py-3">{d.lincese_type || 'Class C/E'}</td>
                                                <td className="px-4 py-3 text-muted-foreground">{d.expiry_date ? formatDate(d.expiry_date) : '-'}</td>
                                                <td className="px-4 py-3 text-center">
                                                    <Badge className="bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                                                        {d.driver_status || 'Active'}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 3: Trip Bookings */}
                    <TabsContent value="bookings">
                        <Card>
                            <CardHeader className="p-4 border-b">
                                <CardTitle className="text-base font-semibold">{t('Dispatch Log & Commercial Deliveries')}</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0 overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="bg-muted/40 text-muted-foreground text-xs uppercase border-b">
                                        <tr>
                                            <th className="px-4 py-3">{t('Route (Origin -> Destination)')}</th>
                                            <th className="px-4 py-3">{t('Vehicle')}</th>
                                            <th className="px-4 py-3">{t('Driver')}</th>
                                            <th className="px-4 py-3">{t('Trip Type')}</th>
                                            <th className="px-4 py-3">{t('Start Date')}</th>
                                            <th className="px-4 py-3 text-right">{t('Value (TZS)')}</th>
                                            <th className="px-4 py-3 text-center">{t('Status')}</th>
                                            <th className="px-4 py-3 text-right">{t('Action')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {bookings.map((b) => (
                                            <tr key={b.id} className="hover:bg-muted/20">
                                                <td className="px-4 py-3">
                                                    <div className="font-medium text-foreground">{b.start_address} → {b.end_address}</div>
                                                    {b.customer_name && <div className="text-xs text-muted-foreground">{b.customer_name}</div>}
                                                </td>
                                                <td className="px-4 py-3 font-mono font-medium">
                                                    {b.vehicle?.lincense_plate || b.vehicle?.name || '-'}
                                                </td>
                                                <td className="px-4 py-3">{b.driver?.name || '-'}</td>
                                                <td className="px-4 py-3 text-xs text-muted-foreground">{b.trip_type}</td>
                                                <td className="px-4 py-3 text-muted-foreground">{formatDate(b.start_date)}</td>
                                                <td className="px-4 py-3 text-right font-bold text-foreground">
                                                    {formatCurrency(b.total_price || 0)}
                                                </td>
                                                <td className="px-4 py-3 text-center">
                                                    <Badge variant={b.status === 'Completed' ? 'secondary' : 'default'} className={
                                                        b.status === 'Completed' ? 'bg-slate-100 text-slate-700' :
                                                        b.status === 'On Trip' ? 'bg-blue-100 text-blue-800' :
                                                        'bg-emerald-100 text-emerald-800'
                                                    }>
                                                        {b.status || 'Approved'}
                                                    </Badge>
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    {b.status !== 'Completed' && (
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            className="text-xs h-7"
                                                            onClick={() => handleCompleteBooking(b.id)}
                                                        >
                                                            <CheckCircle2 className="h-3 w-3 mr-1 text-emerald-600" />
                                                            {t('Complete')}
                                                        </Button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 4: Fuel Logs */}
                    <TabsContent value="fuels">
                        <Card>
                            <CardHeader className="p-4 border-b">
                                <CardTitle className="text-base font-semibold">{t('Fleet Fuel Consumption Logs')}</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0 overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="bg-muted/40 text-muted-foreground text-xs uppercase border-b">
                                        <tr>
                                            <th className="px-4 py-3">{t('Fill Date')}</th>
                                            <th className="px-4 py-3">{t('Vehicle Plate')}</th>
                                            <th className="px-4 py-3">{t('Driver')}</th>
                                            <th className="px-4 py-3 text-right">{t('Quantity (L)')}</th>
                                            <th className="px-4 py-3 text-right">{t('Rate / L (TZS)')}</th>
                                            <th className="px-4 py-3 text-right">{t('Total Cost (TZS)')}</th>
                                            <th className="px-4 py-3 text-right">{t('Odometer (KM)')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {fuels.map((f) => (
                                            <tr key={f.id} className="hover:bg-muted/20">
                                                <td className="px-4 py-3 text-muted-foreground">{formatDate(f.fill_date)}</td>
                                                <td className="px-4 py-3 font-mono font-bold text-primary">
                                                    {f.vehicle?.lincense_plate || f.vehicle?.name || '-'}
                                                </td>
                                                <td className="px-4 py-3">{f.driver?.name || '-'}</td>
                                                <td className="px-4 py-3 text-right font-medium">{f.quantity} L</td>
                                                <td className="px-4 py-3 text-right text-muted-foreground">{formatCurrency(f.cost || 0)}</td>
                                                <td className="px-4 py-3 text-right font-bold text-amber-600">
                                                    {formatCurrency(f.total_cost || 0)}
                                                </td>
                                                <td className="px-4 py-3 text-right font-mono text-muted-foreground">
                                                    {f.odometer_reading ? `${f.odometer_reading} km` : '-'}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    {/* Tab 5: Maintenance */}
                    <TabsContent value="maintenance">
                        <Card>
                            <CardHeader className="p-4 border-b">
                                <CardTitle className="text-base font-semibold">{t('Vehicle Maintenance & Service Records')}</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0 overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="bg-muted/40 text-muted-foreground text-xs uppercase border-b">
                                        <tr>
                                            <th className="px-4 py-3">{t('Service Date')}</th>
                                            <th className="px-4 py-3">{t('Vehicle Plate')}</th>
                                            <th className="px-4 py-3">{t('Service Description')}</th>
                                            <th className="px-4 py-3">{t('Service Center')}</th>
                                            <th className="px-4 py-3 text-center">{t('Priority')}</th>
                                            <th className="px-4 py-3 text-right">{t('Total Cost (TZS)')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y">
                                        {maintenances.map((m) => (
                                            <tr key={m.id} className="hover:bg-muted/20">
                                                <td className="px-4 py-3 text-muted-foreground">{formatDate(m.maintenance_date)}</td>
                                                <td className="px-4 py-3 font-mono font-bold text-primary">
                                                    {m.vehicle_name?.lincense_plate || m.vehicle_name?.name || '-'}
                                                </td>
                                                <td className="px-4 py-3 font-medium">{m.service_type}</td>
                                                <td className="px-4 py-3 text-muted-foreground">{m.service_name || '-'}</td>
                                                <td className="px-4 py-3 text-center">
                                                    <Badge variant={m.priority === 'High' ? 'destructive' : 'secondary'}>
                                                        {m.priority || 'Normal'}
                                                    </Badge>
                                                </td>
                                                <td className="px-4 py-3 text-right font-bold text-purple-600">
                                                    {formatCurrency(m.total_cost || 0)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </TabsContent>
                </Tabs>
            </div>

            {/* Modal: New Vehicle */}
            <Dialog open={vehicleModal} onOpenChange={setVehicleModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Add New Fleet Vehicle')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreateVehicle} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Vehicle Make & Model')}</label>
                            <Input
                                value={vehicleForm.data.name}
                                onChange={(e) => vehicleForm.setData('name', e.target.value)}
                                placeholder="Toyota Hilux 4x4 D-Cab"
                                required
                            />
                        </div>
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Vehicle Registration Plate')}</label>
                            <Input
                                value={vehicleForm.data.lincense_plate}
                                onChange={(e) => vehicleForm.setData('lincense_plate', e.target.value)}
                                placeholder="T 482 DXK"
                                required
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Model Year')}</label>
                                <Input
                                    value={vehicleForm.data.model_year}
                                    onChange={(e) => vehicleForm.setData('model_year', e.target.value)}
                                />
                            </div>
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Seat / Capacity')}</label>
                                <Input
                                    type="number"
                                    value={vehicleForm.data.seat_capacity}
                                    onChange={(e) => vehicleForm.setData('seat_capacity', parseInt(e.target.value))}
                                />
                            </div>
                        </div>
                        <div className="flex justify-end gap-2 pt-2 border-t">
                            <Button type="button" variant="outline" onClick={() => setVehicleModal(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={vehicleForm.processing}>{t('Save Vehicle')}</Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Modal: New Driver */}
            <Dialog open={driverModal} onOpenChange={setDriverModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Register Commercial Driver')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreateDriver} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Driver Full Name')}</label>
                            <Input
                                value={driverForm.data.name}
                                onChange={(e) => driverForm.setData('name', e.target.value)}
                                placeholder="Juma Said Mwinyi"
                                required
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Phone Number')}</label>
                                <Input
                                    value={driverForm.data.phone}
                                    onChange={(e) => driverForm.setData('phone', e.target.value)}
                                    placeholder="+255 754 112 334"
                                    required
                                />
                            </div>
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Permit Number')}</label>
                                <Input
                                    value={driverForm.data.lincese_number}
                                    onChange={(e) => driverForm.setData('lincese_number', e.target.value)}
                                    placeholder="TZ-DL-889412"
                                    required
                                />
                            </div>
                        </div>
                        <div className="flex justify-end gap-2 pt-2 border-t">
                            <Button type="button" variant="outline" onClick={() => setDriverModal(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={driverForm.processing}>{t('Register Driver')}</Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Modal: Dispatch Trip */}
            <Dialog open={bookingModal} onOpenChange={setBookingModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Dispatch Trip & Cargo Delivery')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreateBooking} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Vehicle')}</label>
                            <Select 
                                value={bookingForm.data.vehicle_name} 
                                onValueChange={(val) => bookingForm.setData('vehicle_name', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('Select Vehicle')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {vehicles.map(v => (
                                        <SelectItem key={v.id} value={v.id.toString()}>
                                            {v.lincense_plate} - {v.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Driver')}</label>
                            <Select 
                                value={bookingForm.data.driver_name} 
                                onValueChange={(val) => bookingForm.setData('driver_name', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('Select Driver')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {drivers.map(d => (
                                        <SelectItem key={d.id} value={d.id.toString()}>
                                            {d.name} ({d.phone})
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Origin')}</label>
                                <Input
                                    value={bookingForm.data.start_address}
                                    onChange={(e) => bookingForm.setData('start_address', e.target.value)}
                                    required
                                />
                            </div>
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Destination')}</label>
                                <Input
                                    value={bookingForm.data.end_address}
                                    onChange={(e) => bookingForm.setData('end_address', e.target.value)}
                                    required
                                />
                            </div>
                        </div>
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Trip Value / Revenue (TZS)')}</label>
                            <Input
                                type="number"
                                min="0"
                                value={bookingForm.data.total_price}
                                onChange={(e) => bookingForm.setData('total_price', e.target.value)}
                                placeholder="1250000"
                                required
                            />
                        </div>
                        <div className="flex justify-end gap-2 pt-2 border-t">
                            <Button type="button" variant="outline" onClick={() => setBookingModal(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={bookingForm.processing} className="bg-emerald-600 hover:bg-emerald-700">{t('Dispatch Trip')}</Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Modal: Fuel */}
            <Dialog open={fuelModal} onOpenChange={setFuelModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Log Vehicle Fuel Fill-up')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreateFuel} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Vehicle')}</label>
                            <Select 
                                value={fuelForm.data.vehicle_name} 
                                onValueChange={(val) => fuelForm.setData('vehicle_name', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('Select Vehicle')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {vehicles.map(v => (
                                        <SelectItem key={v.id} value={v.id.toString()}>
                                            {v.lincense_plate} - {v.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Quantity (Liters)')}</label>
                                <Input
                                    type="number"
                                    min="1"
                                    value={fuelForm.data.quantity}
                                    onChange={(e) => fuelForm.setData('quantity', e.target.value)}
                                    placeholder="80"
                                    required
                                />
                            </div>
                            <div>
                                <label className="text-xs font-semibold block mb-1">{t('Cost / Liter (TZS)')}</label>
                                <Input
                                    type="number"
                                    value={fuelForm.data.cost}
                                    onChange={(e) => fuelForm.setData('cost', e.target.value)}
                                    placeholder="3100"
                                    required
                                />
                            </div>
                        </div>
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Current Odometer (KM)')}</label>
                            <Input
                                type="number"
                                value={fuelForm.data.odometer_reading}
                                onChange={(e) => fuelForm.setData('odometer_reading', e.target.value)}
                                placeholder="124800"
                            />
                        </div>
                        <div className="flex justify-end gap-2 pt-2 border-t">
                            <Button type="button" variant="outline" onClick={() => setFuelModal(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={fuelForm.processing} className="bg-amber-600 hover:bg-amber-700">{t('Save Fuel Log')}</Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Modal: Maintenance */}
            <Dialog open={maintenanceModal} onOpenChange={setMaintenanceModal}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('Schedule Vehicle Maintenance')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={handleCreateMaintenance} className="space-y-4 pt-2">
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Vehicle')}</label>
                            <Select 
                                value={maintenanceForm.data.vehicle_name} 
                                onValueChange={(val) => maintenanceForm.setData('vehicle_name', val)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder={t('Select Vehicle')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {vehicles.map(v => (
                                        <SelectItem key={v.id} value={v.id.toString()}>
                                            {v.lincense_plate} - {v.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Service Type')}</label>
                            <Input
                                value={maintenanceForm.data.service_type}
                                onChange={(e) => maintenanceForm.setData('service_type', e.target.value)}
                                placeholder="Brake Pads & Engine Oil Service"
                                required
                            />
                        </div>
                        <div>
                            <label className="text-xs font-semibold block mb-1">{t('Total Cost (TZS)')}</label>
                            <Input
                                type="number"
                                min="0"
                                value={maintenanceForm.data.total_cost}
                                onChange={(e) => maintenanceForm.setData('total_cost', e.target.value)}
                                placeholder="480000"
                                required
                            />
                        </div>
                        <div className="flex justify-end gap-2 pt-2 border-t">
                            <Button type="button" variant="outline" onClick={() => setMaintenanceModal(false)}>{t('Cancel')}</Button>
                            <Button type="submit" disabled={maintenanceForm.processing} className="bg-purple-600 hover:bg-purple-700">{t('Schedule Service')}</Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
