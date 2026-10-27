import React, { useState, useMemo } from 'react';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import { Head, useForm, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
    Truck,
    PackageCheck,
    ClipboardList,
    MapPin,
    UserCheck,
    QrCode,
    Printer,
    Clock,
    Plus,
    Search,
    FileText,
    CheckCircle2,
    XCircle,
    AlertTriangle,
    Calendar,
    Building2,
    MoreVertical,
    Trash2,
    Edit3,
    Send,
    ShieldCheck,
    Barcode,
    Phone,
    Box,
    Hash,
} from 'lucide-react';

interface DeliveryNoteItem {
    id?: number;
    product_id?: number | null;
    product_name: string;
    sku?: string;
    quantity_ordered: number;
    quantity_shipped: number;
    quantity_received?: number;
    unit?: string;
    batch_number?: string;
    serial_number?: string;
    notes?: string;
    product?: {
        id: number;
        name: string;
        sku: string;
    };
}

interface DeliveryNote {
    id: number;
    delivery_number: string;
    delivery_date: string;
    estimated_delivery_date?: string;
    actual_delivery_date?: string;
    invoice_id?: number;
    customer_id: number;
    warehouse_id: number;
    vehicle_id?: number;
    driver_id?: number;
    driver_name?: string;
    driver_phone?: string;
    vehicle_plate?: string;
    shipping_address?: string;
    destination_city?: string;
    contact_person?: string;
    contact_phone?: string;
    status: 'draft' | 'scheduled' | 'dispatched' | 'in_transit' | 'delivered' | 'returned' | 'cancelled';
    status_label: string;
    tracking_number?: string;
    total_packages: number;
    total_weight_kg: number;
    notes?: string;
    proof_of_delivery_recipient?: string;
    proof_of_delivery_signature?: string;
    proof_of_delivery_notes?: string;
    delivery_verified_at?: string;
    verification_hash?: string;
    stock_deducted: boolean;
    total_units_shipped: number;
    items: DeliveryNoteItem[];
    customer?: {
        id: number;
        name: string;
        email?: string;
        mobile_no?: string;
    };
    warehouse?: {
        id: number;
        name: string;
        address?: string;
        city?: string;
    };
    vehicle?: {
        id: number;
        name: string;
        plate_number?: string;
    };
    driver?: {
        id: number;
        name: string;
        phone?: string;
    };
    invoice?: {
        id: number;
        invoice_number: string;
        total_amount: number;
    };
}

interface Props {
    delivery_notes: DeliveryNote[];
    kpi: {
        total_deliveries: number;
        in_transit_deliveries: number;
        delivered_count: number;
        total_units_dispatched: number;
    };
    filters: {
        status?: string;
        warehouse_id?: string;
        search?: string;
    };
    warehouses: Array<{ id: number; name: string; address?: string; city?: string }>;
    customers: Array<{ id: number; name: string; email?: string; mobile_no?: string }>;
    invoices: Array<{
        id: number;
        invoice_number: string;
        customer_id: number;
        warehouse_id: number;
        total_amount: number;
        items: Array<{
            product_id: number;
            product_name?: string;
            quantity: number;
            product?: { name: string; sku?: string };
        }>;
    }>;
    vehicles: Array<{ id: number; name: string; plate_number?: string; registration_number?: string }>;
    drivers: Array<{ id: number; name: string; phone?: string }>;
    products: Array<{ id: number; name: string; sku?: string; sale_price: number; quantity: number; unit?: string }>;
}

export default function DeliveryNotesIndex({
    delivery_notes,
    kpi,
    filters,
    warehouses,
    customers,
    invoices,
    vehicles,
    drivers,
    products,
}: Props) {
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [selectedWarehouse, setSelectedWarehouse] = useState(filters.warehouse_id || 'all');
    const [selectedStatusTab, setSelectedStatusTab] = useState('all');

    // Modals
    const [isCreateOpen, setIsCreateOpen] = useState(false);
    const [isEditOpen, setIsEditOpen] = useState(false);
    const [isDispatchOpen, setIsDispatchOpen] = useState(false);
    const [isPodOpen, setIsPodOpen] = useState(false);
    const [isSlipOpen, setIsSlipOpen] = useState(false);
    const [isDeleteOpen, setIsDeleteOpen] = useState(false);

    const [activeNote, setActiveNote] = useState<DeliveryNote | null>(null);

    // Create Form
    const createForm = useForm({
        delivery_date: new Date().toISOString().split('T')[0],
        estimated_delivery_date: '',
        customer_id: customers[0]?.id ? String(customers[0].id) : '',
        warehouse_id: warehouses[0]?.id ? String(warehouses[0].id) : '',
        invoice_id: '',
        vehicle_id: '',
        driver_id: '',
        driver_name: '',
        driver_phone: '',
        vehicle_plate: '',
        shipping_address: '',
        destination_city: 'Dar es Salaam',
        contact_person: '',
        contact_phone: '',
        total_packages: 1,
        total_weight_kg: 0,
        notes: '',
        items: [
            {
                product_id: products[0]?.id || 0,
                product_name: products[0]?.name || '',
                sku: products[0]?.sku || '',
                quantity_ordered: 1,
                quantity_shipped: 1,
                unit: 'units',
                batch_number: '',
            },
        ],
    });

    // Edit Form
    const editForm = useForm({
        delivery_date: '',
        estimated_delivery_date: '',
        customer_id: '',
        warehouse_id: '',
        invoice_id: '',
        vehicle_id: '',
        driver_id: '',
        driver_name: '',
        driver_phone: '',
        vehicle_plate: '',
        shipping_address: '',
        destination_city: '',
        contact_person: '',
        contact_phone: '',
        total_packages: 1,
        total_weight_kg: 0,
        notes: '',
        items: [] as any[],
    });

    // POD Form
    const podForm = useForm({
        recipient_name: '',
        notes: '',
        actual_delivery_date: new Date().toISOString().split('T')[0],
        signature_token: '',
    });

    // Filtered Notes
    const filteredNotes = useMemo(() => {
        return delivery_notes.filter((note) => {
            const matchesSearch =
                searchTerm === '' ||
                note.delivery_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
                (note.tracking_number && note.tracking_number.toLowerCase().includes(searchTerm.toLowerCase())) ||
                (note.customer?.name && note.customer.name.toLowerCase().includes(searchTerm.toLowerCase())) ||
                (note.destination_city && note.destination_city.toLowerCase().includes(searchTerm.toLowerCase())) ||
                (note.contact_person && note.contact_person.toLowerCase().includes(searchTerm.toLowerCase()));

            const matchesWarehouse =
                selectedWarehouse === 'all' || String(note.warehouse_id) === selectedWarehouse;

            let matchesTab = true;
            if (selectedStatusTab === 'in_transit') {
                matchesTab = ['scheduled', 'dispatched', 'in_transit'].includes(note.status);
            } else if (selectedStatusTab === 'delivered') {
                matchesTab = note.status === 'delivered';
            } else if (selectedStatusTab === 'draft') {
                matchesTab = note.status === 'draft';
            }

            return matchesSearch && matchesWarehouse && matchesTab;
        });
    }, [delivery_notes, searchTerm, selectedWarehouse, selectedStatusTab]);

    // Populate create form from invoice
    const handleInvoiceSelect = (invoiceId: string) => {
        createForm.setData('invoice_id', invoiceId);
        if (!invoiceId) return;

        const inv = invoices.find((i) => String(i.id) === invoiceId);
        if (inv) {
            createForm.setData((prev) => ({
                ...prev,
                invoice_id: invoiceId,
                customer_id: String(inv.customer_id),
                warehouse_id: String(inv.warehouse_id || prev.warehouse_id),
                items:
                    inv.items && inv.items.length > 0
                        ? inv.items.map((it) => ({
                              product_id: it.product_id,
                              product_name: it.product?.name || it.product_name || 'Item',
                              sku: it.product?.sku || '',
                              quantity_ordered: it.quantity,
                              quantity_shipped: it.quantity,
                              unit: 'units',
                              batch_number: '',
                          }))
                        : prev.items,
            }));
        }
    };

    // Auto-fill vehicle plate and driver phone
    const handleVehicleSelect = (vehicleId: string, formInstance: any) => {
        formInstance.setData('vehicle_id', vehicleId);
        const v = vehicles.find((item) => String(item.id) === vehicleId);
        if (v) {
            formInstance.setData('vehicle_plate', v.plate_number || v.name);
        }
    };

    const handleDriverSelect = (driverId: string, formInstance: any) => {
        formInstance.setData('driver_id', driverId);
        const d = drivers.find((item) => String(item.id) === driverId);
        if (d) {
            formInstance.setData('driver_name', d.name);
            if (d.phone) formInstance.setData('driver_phone', d.phone);
        }
    };

    // Add item line in create modal
    const handleAddItem = (formInstance: any) => {
        formInstance.setData('items', [
            ...formInstance.data.items,
            {
                product_id: products[0]?.id || 0,
                product_name: products[0]?.name || '',
                sku: products[0]?.sku || '',
                quantity_ordered: 1,
                quantity_shipped: 1,
                unit: 'units',
                batch_number: '',
            },
        ]);
    };

    const handleRemoveItem = (index: number, formInstance: any) => {
        if (formInstance.data.items.length <= 1) return;
        const newItems = [...formInstance.data.items];
        newItems.splice(index, 1);
        formInstance.setData('items', newItems);
    };

    const handleItemChange = (index: number, field: string, value: any, formInstance: any) => {
        const newItems = [...formInstance.data.items];
        newItems[index] = { ...newItems[index], [field]: value };

        if (field === 'product_id') {
            const p = products.find((prod) => prod.id === Number(value));
            if (p) {
                newItems[index].product_name = p.name;
                newItems[index].sku = p.sku || '';
                if (p.unit) newItems[index].unit = p.unit;
            }
        }

        formInstance.setData('items', newItems);
    };

    // Handlers
    const submitCreate = (e: React.FormEvent) => {
        e.preventDefault();
        createForm.post(route('sales.delivery-notes.store'), {
            onSuccess: () => {
                setIsCreateOpen(false);
                createForm.reset();
            },
        });
    };

    const openEditModal = (note: DeliveryNote) => {
        setActiveNote(note);
        editForm.setData({
            delivery_date: note.delivery_date,
            estimated_delivery_date: note.estimated_delivery_date || '',
            customer_id: String(note.customer_id),
            warehouse_id: String(note.warehouse_id),
            invoice_id: note.invoice_id ? String(note.invoice_id) : '',
            vehicle_id: note.vehicle_id ? String(note.vehicle_id) : '',
            driver_id: note.driver_id ? String(note.driver_id) : '',
            driver_name: note.driver_name || '',
            driver_phone: note.driver_phone || '',
            vehicle_plate: note.vehicle_plate || '',
            shipping_address: note.shipping_address || '',
            destination_city: note.destination_city || 'Dar es Salaam',
            contact_person: note.contact_person || '',
            contact_phone: note.contact_phone || '',
            total_packages: note.total_packages || 1,
            total_weight_kg: note.total_weight_kg || 0,
            notes: note.notes || '',
            items: note.items.map((i) => ({
                id: i.id,
                product_id: i.product_id || 0,
                product_name: i.product_name,
                sku: i.sku || '',
                quantity_ordered: i.quantity_ordered,
                quantity_shipped: i.quantity_shipped,
                unit: i.unit || 'units',
                batch_number: i.batch_number || '',
            })),
        });
        setIsEditOpen(true);
    };

    const submitEdit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeNote) return;
        editForm.put(route('sales.delivery-notes.update', activeNote.id), {
            onSuccess: () => {
                setIsEditOpen(false);
                setActiveNote(null);
            },
        });
    };

    const openDispatchModal = (note: DeliveryNote) => {
        setActiveNote(note);
        setIsDispatchOpen(true);
    };

    const submitDispatch = () => {
        if (!activeNote) return;
        router.post(
            route('sales.delivery-notes.dispatch', activeNote.id),
            {},
            {
                onSuccess: () => {
                    setIsDispatchOpen(false);
                    setActiveNote(null);
                },
            }
        );
    };

    const openPodModal = (note: DeliveryNote) => {
        setActiveNote(note);
        podForm.setData({
            recipient_name: note.contact_person || note.customer?.name || '',
            notes: 'Goods delivered in good condition and acknowledged.',
            actual_delivery_date: new Date().toISOString().split('T')[0],
            signature_token: 'POD-SIG-' + Math.random().toString(36).substring(2, 10).toUpperCase(),
        });
        setIsPodOpen(true);
    };

    const submitPod = (e: React.FormEvent) => {
        e.preventDefault();
        if (!activeNote) return;
        podForm.post(route('sales.delivery-notes.confirm-delivery', activeNote.id), {
            onSuccess: () => {
                setIsPodOpen(false);
                setActiveNote(null);
            },
        });
    };

    const handleCancelNote = (note: DeliveryNote) => {
        if (confirm(`Are you sure you want to cancel ${note.delivery_number}? If dispatched, inventory stock will be restored.`)) {
            router.post(route('sales.delivery-notes.cancel', note.id));
        }
    };

    const openDeleteModal = (note: DeliveryNote) => {
        setActiveNote(note);
        setIsDeleteOpen(true);
    };

    const submitDelete = () => {
        if (!activeNote) return;
        router.delete(route('sales.delivery-notes.destroy', activeNote.id), {
            onSuccess: () => {
                setIsDeleteOpen(false);
                setActiveNote(null);
            },
        });
    };

    const openSlipModal = (note: DeliveryNote) => {
        setActiveNote(note);
        setIsSlipOpen(true);
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'draft':
                return <Badge variant="outline" className="border-slate-400 text-slate-600 bg-slate-50 dark:bg-slate-900">Draft</Badge>;
            case 'scheduled':
                return <Badge className="bg-sky-500 hover:bg-sky-600 text-white">Scheduled</Badge>;
            case 'dispatched':
                return <Badge className="bg-amber-500 hover:bg-amber-600 text-white">Dispatched</Badge>;
            case 'in_transit':
                return <Badge className="bg-indigo-500 hover:bg-indigo-600 text-white">In Transit</Badge>;
            case 'delivered':
                return <Badge className="bg-emerald-600 hover:bg-emerald-700 text-white">Delivered (POD)</Badge>;
            case 'cancelled':
                return <Badge variant="destructive">Cancelled</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title="Delivery Notes & Dispatch Fulfillment" />

            <div className="space-y-6">
                {/* Header Banner */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 p-6 rounded-2xl shadow-xl text-white border border-emerald-800/40">
                    <div className="space-y-1">
                        <div className="flex items-center gap-3">
                            <div className="p-2.5 bg-emerald-500/20 rounded-xl border border-emerald-400/30">
                                <Truck className="w-7 h-7 text-emerald-400" />
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                                    Delivery Notes & Dispatch Fulfillment
                                    <Badge className="bg-emerald-500/20 text-emerald-300 border-emerald-400/30">
                                        SRS Module 9: Quote-to-Cash
                                    </Badge>
                                </h1>
                                <p className="text-sm text-slate-300">
                                    Warehouse Dispatch, Automated Stock Decrement, Fleet Vehicle Tracking & Digital Proof of Delivery (POD)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Button
                            onClick={() => setIsCreateOpen(true)}
                            className="bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-semibold shadow-lg shadow-emerald-500/20"
                        >
                            <Plus className="w-4 h-4 mr-2" />
                            New Delivery Note
                        </Button>
                    </div>
                </div>

                {/* KPI Metrics Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card className="border-border shadow-sm bg-card hover:border-emerald-500/40 transition-all">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total Deliveries
                            </CardTitle>
                            <div className="p-2 bg-blue-500/10 text-blue-500 rounded-lg">
                                <ClipboardList className="w-5 h-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{kpi.total_deliveries}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Generated delivery notes
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border shadow-sm bg-card hover:border-emerald-500/40 transition-all">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Active In-Transit / Dispatched
                            </CardTitle>
                            <div className="p-2 bg-amber-500/10 text-amber-500 rounded-lg">
                                <Truck className="w-5 h-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-amber-500">{kpi.in_transit_deliveries}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                On route with fleet trucks
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border shadow-sm bg-card hover:border-emerald-500/40 transition-all">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Delivered & POD Verified
                            </CardTitle>
                            <div className="p-2 bg-emerald-500/10 text-emerald-500 rounded-lg">
                                <PackageCheck className="w-5 h-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-emerald-500">{kpi.delivered_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Signed & cryptographically verified
                            </p>
                        </CardContent>
                    </Card>

                    <Card className="border-border shadow-sm bg-card hover:border-emerald-500/40 transition-all">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Total Units Dispatched
                            </CardTitle>
                            <div className="p-2 bg-purple-500/10 text-purple-500 rounded-lg">
                                <CheckCircle2 className="w-5 h-5" />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {kpi.total_units_dispatched.toLocaleString()}
                            </div>
                            <p className="text-xs text-muted-foreground mt-1">
                                Units fulfilled from warehouses
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Filter and Search Bar */}
                <Card className="border-border shadow-sm">
                    <CardContent className="p-4">
                        <div className="flex flex-col md:flex-row items-center justify-between gap-4">
                            <div className="flex items-center gap-3 w-full md:w-auto flex-1">
                                <div className="relative flex-1 max-w-sm">
                                    <Search className="w-4 h-4 absolute left-3 top-3 text-muted-foreground" />
                                    <Input
                                        placeholder="Search by DN#, consignee, tracking..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-9"
                                    />
                                </div>

                                <div className="w-48">
                                    <select
                                        value={selectedWarehouse}
                                        onChange={(e) => setSelectedWarehouse(e.target.value)}
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2"
                                    >
                                        <option value="all">All Warehouses</option>
                                        {warehouses.map((wh) => (
                                            <option key={wh.id} value={wh.id}>
                                                {wh.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <Tabs value={selectedStatusTab} onValueChange={setSelectedStatusTab} className="w-full md:w-auto">
                                <TabsList>
                                    <TabsTrigger value="all">All Notes</TabsTrigger>
                                    <TabsTrigger value="in_transit">Active In-Transit</TabsTrigger>
                                    <TabsTrigger value="delivered">Delivered (POD)</TabsTrigger>
                                    <TabsTrigger value="draft">Drafts</TabsTrigger>
                                </TabsList>
                            </Tabs>
                        </div>
                    </CardContent>
                </Card>

                {/* Delivery Notes Table */}
                <Card className="border-border shadow-sm">
                    <CardHeader className="px-6 py-4 border-b">
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle className="text-lg">Delivery Notes Ledger</CardTitle>
                                <CardDescription>
                                    Sequential delivery numbers complying with Appendix A (`DN-YYYY-#####`)
                                </CardDescription>
                            </div>
                            <span className="text-xs text-muted-foreground">
                                Showing {filteredNotes.length} of {delivery_notes.length} records
                            </span>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow className="bg-muted/40">
                                        <TableHead>Delivery Number</TableHead>
                                        <TableHead>Date & Destination</TableHead>
                                        <TableHead>Customer / Consignee</TableHead>
                                        <TableHead>Warehouse</TableHead>
                                        <TableHead>Vehicle & Driver</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">Units Shipped</TableHead>
                                        <TableHead className="text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filteredNotes.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={8} className="text-center py-8 text-muted-foreground">
                                                No delivery notes found matching criteria.
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        filteredNotes.map((note) => (
                                            <TableRow key={note.id} className="hover:bg-muted/30">
                                                <TableCell className="font-semibold text-primary">
                                                    <div className="flex items-center gap-2">
                                                        <FileText className="w-4 h-4 text-emerald-500" />
                                                        <span>{note.delivery_number}</span>
                                                    </div>
                                                    {note.tracking_number && (
                                                        <span className="text-xs text-muted-foreground font-mono block">
                                                            {note.tracking_number}
                                                        </span>
                                                    )}
                                                </TableCell>

                                                <TableCell>
                                                    <div className="text-sm font-medium">{note.delivery_date}</div>
                                                    <div className="text-xs text-muted-foreground flex items-center gap-1">
                                                        <MapPin className="w-3 h-3 text-red-500" />
                                                        {note.destination_city || 'Tanzania'}
                                                    </div>
                                                </TableCell>

                                                <TableCell>
                                                    <div className="text-sm font-medium">{note.customer?.name || 'Customer'}</div>
                                                    {note.contact_person && (
                                                        <div className="text-xs text-muted-foreground">
                                                            Attn: {note.contact_person}
                                                        </div>
                                                    )}
                                                </TableCell>

                                                <TableCell>
                                                    <div className="flex items-center gap-1 text-sm">
                                                        <Building2 className="w-3.5 h-3.5 text-muted-foreground" />
                                                        <span>{note.warehouse?.name || 'Main Warehouse'}</span>
                                                    </div>
                                                    {note.stock_deducted && (
                                                        <span className="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">
                                                            Stock Decremented
                                                        </span>
                                                    )}
                                                </TableCell>

                                                <TableCell>
                                                    {note.vehicle_plate ? (
                                                        <div className="text-sm font-mono">{note.vehicle_plate}</div>
                                                    ) : (
                                                        <span className="text-xs text-muted-foreground">No vehicle</span>
                                                    )}
                                                    {note.driver_name && (
                                                        <div className="text-xs text-muted-foreground">
                                                            {note.driver_name} {note.driver_phone && `(${note.driver_phone})`}
                                                        </div>
                                                    )}
                                                </TableCell>

                                                <TableCell>
                                                    {getStatusBadge(note.status)}
                                                </TableCell>

                                                <TableCell className="text-right font-medium">
                                                    <div>{note.total_units_shipped} {note.items[0]?.unit || 'units'}</div>
                                                    <span className="text-xs text-muted-foreground">
                                                        {note.items.length} line{note.items.length > 1 ? 's' : ''}
                                                    </span>
                                                </TableCell>

                                                <TableCell className="text-right">
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" size="sm" className="h-8 w-8 p-0">
                                                                <MoreVertical className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end" className="w-48">
                                                            <DropdownMenuItem onClick={() => openSlipModal(note)}>
                                                                <Printer className="w-4 h-4 mr-2 text-indigo-500" />
                                                                Print Delivery Slip
                                                            </DropdownMenuItem>

                                                            {['draft', 'scheduled'].includes(note.status) && (
                                                                <DropdownMenuItem onClick={() => openDispatchModal(note)}>
                                                                    <Send className="w-4 h-4 mr-2 text-amber-500" />
                                                                    Dispatch & Deduct Stock
                                                                </DropdownMenuItem>
                                                            )}

                                                            {['dispatched', 'in_transit'].includes(note.status) && (
                                                                <DropdownMenuItem onClick={() => openPodModal(note)}>
                                                                    <UserCheck className="w-4 h-4 mr-2 text-emerald-500" />
                                                                    Confirm POD
                                                                </DropdownMenuItem>
                                                            )}

                                                            {['draft', 'scheduled'].includes(note.status) && (
                                                                <DropdownMenuItem onClick={() => openEditModal(note)}>
                                                                    <Edit3 className="w-4 h-4 mr-2 text-blue-500" />
                                                                    Edit Note
                                                                </DropdownMenuItem>
                                                            )}

                                                            {['dispatched', 'in_transit'].includes(note.status) && (
                                                                <DropdownMenuItem
                                                                    onClick={() => handleCancelNote(note)}
                                                                    className="text-amber-600"
                                                                >
                                                                    <XCircle className="w-4 h-4 mr-2" />
                                                                    Cancel & Restore Stock
                                                                </DropdownMenuItem>
                                                            )}

                                                            <DropdownMenuSeparator />

                                                            <DropdownMenuItem
                                                                onClick={() => openDeleteModal(note)}
                                                                className="text-red-600"
                                                            >
                                                                <Trash2 className="w-4 h-4 mr-2" />
                                                                Delete
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>

                {/* MODAL 1: Create Delivery Note */}
                <Dialog open={isCreateOpen} onOpenChange={setIsCreateOpen}>
                    <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-xl">
                                <Truck className="w-5 h-5 text-emerald-500" />
                                Create Delivery Note
                            </DialogTitle>
                            <DialogDescription>
                                Dispatch goods to customer with automatic warehouse stock decrement and transport tracking.
                            </DialogDescription>
                        </DialogHeader>

                        <form onSubmit={submitCreate} className="space-y-6 pt-2">
                            {/* Section 1: Invoicing & Consignee */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-muted/30 rounded-xl border">
                                <div>
                                    <Label>Link to Sales Invoice (Optional)</Label>
                                    <select
                                        value={createForm.data.invoice_id}
                                        onChange={(e) => handleInvoiceSelect(e.target.value)}
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        <option value="">-- Standalone Delivery Note --</option>
                                        {invoices.map((inv) => (
                                            <option key={inv.id} value={inv.id}>
                                                {inv.invoice_number} ({Number(inv.total_amount).toLocaleString()} TZS)
                                            </option>
                                        ))}
                                    </select>
                                    <span className="text-[11px] text-muted-foreground">Auto-populates items & customer</span>
                                </div>

                                <div>
                                    <Label>Customer / Consignee *</Label>
                                    <select
                                        value={createForm.data.customer_id}
                                        onChange={(e) => createForm.setData('customer_id', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {customers.map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <Label>Fulfillment Warehouse *</Label>
                                    <select
                                        value={createForm.data.warehouse_id}
                                        onChange={(e) => createForm.setData('warehouse_id', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {warehouses.map((wh) => (
                                            <option key={wh.id} value={wh.id}>
                                                {wh.name} ({wh.city || 'Tanzania'})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            {/* Section 2: Dates & Destination */}
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <Label>Dispatch Date *</Label>
                                    <Input
                                        type="date"
                                        value={createForm.data.delivery_date}
                                        onChange={(e) => createForm.setData('delivery_date', e.target.value)}
                                        required
                                        className="mt-1"
                                    />
                                </div>

                                <div>
                                    <Label>Estimated Arrival Date</Label>
                                    <Input
                                        type="date"
                                        value={createForm.data.estimated_delivery_date}
                                        onChange={(e) => createForm.setData('estimated_delivery_date', e.target.value)}
                                        className="mt-1"
                                    />
                                </div>

                                <div>
                                    <Label>Destination City</Label>
                                    <Input
                                        placeholder="e.g. Dar es Salaam, Arusha"
                                        value={createForm.data.destination_city}
                                        onChange={(e) => createForm.setData('destination_city', e.target.value)}
                                        className="mt-1"
                                    />
                                </div>

                                <div>
                                    <Label>Contact Person / Phone</Label>
                                    <Input
                                        placeholder="Name & Phone"
                                        value={createForm.data.contact_person}
                                        onChange={(e) => createForm.setData('contact_person', e.target.value)}
                                        className="mt-1"
                                    />
                                </div>
                            </div>

                            <div>
                                <Label>Shipping Destination Address *</Label>
                                <Textarea
                                    placeholder="Full delivery location address in Tanzania..."
                                    value={createForm.data.shipping_address}
                                    onChange={(e) => createForm.setData('shipping_address', e.target.value)}
                                    required
                                    className="mt-1"
                                    rows={2}
                                />
                            </div>

                            {/* Section 3: Fleet Vehicle & Driver */}
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-muted/20 rounded-xl border">
                                <div>
                                    <Label>Assign Fleet Vehicle</Label>
                                    <select
                                        value={createForm.data.vehicle_id}
                                        onChange={(e) => handleVehicleSelect(e.target.value, createForm)}
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        <option value="">-- Outside Carrier / Manual --</option>
                                        {vehicles.map((v) => (
                                            <option key={v.id} value={v.id}>
                                                {v.name} ({v.plate_number})
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <Label>Assign Driver</Label>
                                    <select
                                        value={createForm.data.driver_id}
                                        onChange={(e) => handleDriverSelect(e.target.value, createForm)}
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        <option value="">-- Outside Driver / Manual --</option>
                                        {drivers.map((d) => (
                                            <option key={d.id} value={d.id}>
                                                {d.name} ({d.phone})
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <Label>Vehicle Plate / Driver Phone</Label>
                                    <Input
                                        placeholder="Plate: T 123 ABC"
                                        value={createForm.data.vehicle_plate}
                                        onChange={(e) => createForm.setData('vehicle_plate', e.target.value)}
                                        className="mt-1"
                                    />
                                </div>
                            </div>

                            {/* Section 4: Line Items */}
                            <div className="space-y-3">
                                <div className="flex items-center justify-between">
                                    <Label className="text-base font-semibold">Shipment Line Items</Label>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handleAddItem(createForm)}
                                    >
                                        <Plus className="w-3.5 h-3.5 mr-1" /> Add Item Line
                                    </Button>
                                </div>

                                <div className="border rounded-lg overflow-hidden">
                                    <Table>
                                        <TableHeader>
                                            <TableRow className="bg-muted/40">
                                                <TableHead>Product</TableHead>
                                                <TableHead className="w-28">Ordered</TableHead>
                                                <TableHead className="w-28">Shipped *</TableHead>
                                                <TableHead className="w-24">Unit</TableHead>
                                                <TableHead className="w-36">Batch / Serial</TableHead>
                                                <TableHead className="w-12"></TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {createForm.data.items.map((item, index) => (
                                                <TableRow key={index}>
                                                    <TableCell>
                                                        <select
                                                            value={item.product_id || ''}
                                                            onChange={(e) =>
                                                                handleItemChange(index, 'product_id', e.target.value, createForm)
                                                            }
                                                            className="w-full text-xs rounded border border-input bg-background p-1.5"
                                                        >
                                                            {products.map((p) => (
                                                                <option key={p.id} value={p.id}>
                                                                    {p.name} ({p.sku || 'No SKU'})
                                                                </option>
                                                            ))}
                                                        </select>
                                                    </TableCell>

                                                    <TableCell>
                                                        <Input
                                                            type="number"
                                                            step="0.01"
                                                            value={item.quantity_ordered}
                                                            onChange={(e) =>
                                                                handleItemChange(index, 'quantity_ordered', e.target.value, createForm)
                                                            }
                                                            className="h-8 text-xs"
                                                        />
                                                    </TableCell>

                                                    <TableCell>
                                                        <Input
                                                            type="number"
                                                            step="0.01"
                                                            value={item.quantity_shipped}
                                                            onChange={(e) =>
                                                                handleItemChange(index, 'quantity_shipped', e.target.value, createForm)
                                                            }
                                                            required
                                                            className="h-8 text-xs font-semibold"
                                                        />
                                                    </TableCell>

                                                    <TableCell>
                                                        <Input
                                                            value={item.unit || 'units'}
                                                            onChange={(e) =>
                                                                handleItemChange(index, 'unit', e.target.value, createForm)
                                                            }
                                                            className="h-8 text-xs"
                                                        />
                                                    </TableCell>

                                                    <TableCell>
                                                        <Input
                                                            placeholder="Batch#"
                                                            value={item.batch_number || ''}
                                                            onChange={(e) =>
                                                                handleItemChange(index, 'batch_number', e.target.value, createForm)
                                                            }
                                                            className="h-8 text-xs"
                                                        />
                                                    </TableCell>

                                                    <TableCell>
                                                        <Button
                                                            type="button"
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleRemoveItem(index, createForm)}
                                                            className="h-8 w-8 p-0 text-red-500"
                                                        >
                                                            <Trash2 className="w-3.5 h-3.5" />
                                                        </Button>
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </div>

                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setIsCreateOpen(false)}
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={createForm.processing}
                                    className="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold"
                                >
                                    Save Delivery Note
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 2: Edit Delivery Note */}
                <Dialog open={isEditOpen} onOpenChange={setIsEditOpen}>
                    <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2">
                                <Edit3 className="w-5 h-5 text-blue-500" />
                                Edit Delivery Note: {activeNote?.delivery_number}
                            </DialogTitle>
                        </DialogHeader>

                        <form onSubmit={submitEdit} className="space-y-4 pt-2">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <Label>Dispatch Date</Label>
                                    <Input
                                        type="date"
                                        value={editForm.data.delivery_date}
                                        onChange={(e) => editForm.setData('delivery_date', e.target.value)}
                                        required
                                        className="mt-1"
                                    />
                                </div>

                                <div>
                                    <Label>Customer / Consignee</Label>
                                    <select
                                        value={editForm.data.customer_id}
                                        onChange={(e) => editForm.setData('customer_id', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {customers.map((c) => (
                                            <option key={c.id} value={c.id}>
                                                {c.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <Label>Fulfillment Warehouse</Label>
                                    <select
                                        value={editForm.data.warehouse_id}
                                        onChange={(e) => editForm.setData('warehouse_id', e.target.value)}
                                        required
                                        className="w-full text-sm rounded-md border border-input bg-background px-3 py-2 mt-1"
                                    >
                                        {warehouses.map((wh) => (
                                            <option key={wh.id} value={wh.id}>
                                                {wh.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div>
                                <Label>Shipping Address</Label>
                                <Textarea
                                    value={editForm.data.shipping_address}
                                    onChange={(e) => editForm.setData('shipping_address', e.target.value)}
                                    required
                                    className="mt-1"
                                />
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <Label>Vehicle Plate</Label>
                                    <Input
                                        value={editForm.data.vehicle_plate}
                                        onChange={(e) => editForm.setData('vehicle_plate', e.target.value)}
                                        className="mt-1"
                                    />
                                </div>
                                <div>
                                    <Label>Driver Name</Label>
                                    <Input
                                        value={editForm.data.driver_name}
                                        onChange={(e) => editForm.setData('driver_name', e.target.value)}
                                        className="mt-1"
                                    />
                                </div>
                                <div>
                                    <Label>Driver Phone</Label>
                                    <Input
                                        value={editForm.data.driver_phone}
                                        onChange={(e) => editForm.setData('driver_phone', e.target.value)}
                                        className="mt-1"
                                    />
                                </div>
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={() => setIsEditOpen(false)}>
                                    Cancel
                                </Button>
                                <Button type="submit" disabled={editForm.processing}>
                                    Save Changes
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 3: Dispatch & Deduct Stock Confirmation */}
                <Dialog open={isDispatchOpen} onOpenChange={setIsDispatchOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-amber-500">
                                <Send className="w-5 h-5" />
                                Confirm Dispatch: {activeNote?.delivery_number}
                            </DialogTitle>
                            <DialogDescription>
                                Are you sure you want to dispatch this delivery? This will immediately decrement{' '}
                                <strong className="text-foreground">{activeNote?.total_units_shipped} units</strong> from warehouse{' '}
                                <strong className="text-foreground">{activeNote?.warehouse?.name}</strong> and issue an immutable verification hash.
                            </DialogDescription>
                        </DialogHeader>

                        <div className="p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl space-y-2 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Consignee:</span>
                                <span className="font-semibold">{activeNote?.customer?.name}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Destination:</span>
                                <span>{activeNote?.destination_city}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Vehicle:</span>
                                <span className="font-mono">{activeNote?.vehicle_plate || 'Unassigned'}</span>
                            </div>
                        </div>

                        <DialogFooter>
                            <Button variant="outline" onClick={() => setIsDispatchOpen(false)}>
                                Cancel
                            </Button>
                            <Button
                                onClick={submitDispatch}
                                className="bg-amber-600 hover:bg-amber-700 text-white font-semibold"
                            >
                                Confirm Dispatch & Deduct Stock
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {/* MODAL 4: Proof of Delivery (POD) Confirmation */}
                <Dialog open={isPodOpen} onOpenChange={setIsPodOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle className="flex items-center gap-2 text-emerald-500">
                                <UserCheck className="w-5 h-5" />
                                Record Proof of Delivery (POD)
                            </DialogTitle>
                            <DialogDescription>
                                Confirm delivery handover to consignee for {activeNote?.delivery_number}.
                            </DialogDescription>
                        </DialogHeader>

                        <form onSubmit={submitPod} className="space-y-4 pt-2">
                            <div>
                                <Label>Recipient / Receiver Name *</Label>
                                <Input
                                    value={podForm.data.recipient_name}
                                    onChange={(e) => podForm.setData('recipient_name', e.target.value)}
                                    placeholder="Full name of person receiving goods"
                                    required
                                    className="mt-1"
                                />
                            </div>

                            <div>
                                <Label>Actual Handover Date</Label>
                                <Input
                                    type="date"
                                    value={podForm.data.actual_delivery_date}
                                    onChange={(e) => podForm.setData('actual_delivery_date', e.target.value)}
                                    required
                                    className="mt-1"
                                />
                            </div>

                            <div>
                                <Label>Handover Notes / Inspection State</Label>
                                <Textarea
                                    value={podForm.data.notes}
                                    onChange={(e) => podForm.setData('notes', e.target.value)}
                                    placeholder="Notes on goods condition, seal intact, etc."
                                    className="mt-1"
                                    rows={3}
                                />
                            </div>

                            <DialogFooter>
                                <Button type="button" variant="outline" onClick={() => setIsPodOpen(false)}>
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={podForm.processing}
                                    className="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold"
                                >
                                    Confirm POD Sign-Off
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>

                {/* MODAL 5: Printable Verifiable Delivery Slip */}
                <Dialog open={isSlipOpen} onOpenChange={setIsSlipOpen}>
                    <DialogContent className="max-w-3xl max-h-[92vh] overflow-y-auto">
                        <DialogHeader className="print:hidden">
                            <DialogTitle className="flex items-center justify-between">
                                <span className="flex items-center gap-2">
                                    <Printer className="w-5 h-5 text-indigo-500" />
                                    Delivery Note Voucher: {activeNote?.delivery_number}
                                </span>
                                <Button
                                    onClick={() => window.print()}
                                    size="sm"
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                                >
                                    <Printer className="w-4 h-4 mr-2" /> Print Slip
                                </Button>
                            </DialogTitle>
                        </DialogHeader>

                        {/* Printable Delivery Manifest Document */}
                        <div className="p-8 bg-white text-slate-900 rounded-xl border border-slate-200 shadow-sm space-y-6">
                            {/* Header */}
                            <div className="flex justify-between items-start border-b pb-6">
                                <div>
                                    <h2 className="text-2xl font-black tracking-tight text-slate-900">
                                        MUTAMARK ENTERPRISES LIMITED
                                    </h2>
                                    <p className="text-xs text-slate-600 font-medium">
                                        Plot 42, Nyerere Road, Industrial Area | P.O. Box 7102, Dar es Salaam, Tanzania
                                    </p>
                                    <div className="text-xs text-slate-600 space-x-3 mt-1">
                                        <span><strong>TIN:</strong> 104-982-341</span>
                                        <span><strong>VRN:</strong> 40-023912-K</span>
                                        <span><strong>Tel:</strong> +255 22 211 4455</span>
                                    </div>
                                </div>
                                <div className="text-right">
                                    <div className="text-xl font-bold tracking-tight text-emerald-800">
                                        OFFICIAL DELIVERY NOTE
                                    </div>
                                    <div className="text-sm font-mono font-bold text-slate-900 mt-1">
                                        {activeNote?.delivery_number}
                                    </div>
                                    <div className="text-xs text-slate-500 mt-0.5">
                                        Date: {activeNote?.delivery_date}
                                    </div>
                                </div>
                            </div>

                            {/* Consignee & Transport Meta */}
                            <div className="grid grid-cols-2 gap-6 text-xs">
                                <div className="p-3 bg-slate-50 rounded border border-slate-200">
                                    <div className="font-bold text-slate-700 uppercase mb-1">CONSIGNEE / SHIP TO:</div>
                                    <div className="text-sm font-bold text-slate-900">{activeNote?.customer?.name}</div>
                                    <div className="text-slate-600 mt-1 whitespace-pre-line">
                                        {activeNote?.shipping_address}
                                    </div>
                                    <div className="text-slate-600 mt-1">
                                        <strong>City:</strong> {activeNote?.destination_city}
                                    </div>
                                    {activeNote?.contact_person && (
                                        <div className="text-slate-600">
                                            <strong>Attn:</strong> {activeNote?.contact_person} ({activeNote?.contact_phone})
                                        </div>
                                    )}
                                </div>

                                <div className="p-3 bg-slate-50 rounded border border-slate-200 space-y-1">
                                    <div className="font-bold text-slate-700 uppercase mb-1">DISPATCH & LOGISTICS:</div>
                                    <div><strong>Fulfillment Warehouse:</strong> {activeNote?.warehouse?.name}</div>
                                    <div><strong>Vehicle Plate:</strong> <span className="font-mono font-bold">{activeNote?.vehicle_plate || 'N/A'}</span></div>
                                    <div><strong>Driver:</strong> {activeNote?.driver_name || 'Assigned Driver'} {activeNote?.driver_phone && `(${activeNote?.driver_phone})`}</div>
                                    <div><strong>Tracking Number:</strong> <span className="font-mono">{activeNote?.tracking_number}</span></div>
                                    {activeNote?.invoice && (
                                        <div><strong>Linked Invoice:</strong> {activeNote.invoice.invoice_number}</div>
                                    )}
                                </div>
                            </div>

                            {/* Itemized Manifest */}
                            <div className="border border-slate-300 rounded overflow-hidden">
                                <table className="w-full text-xs text-left">
                                    <thead className="bg-slate-100 font-bold border-b border-slate-300">
                                        <tr>
                                            <th className="p-2.5">#</th>
                                            <th className="p-2.5">Item Description</th>
                                            <th className="p-2.5">SKU / Code</th>
                                            <th className="p-2.5">Batch / Serial</th>
                                            <th className="p-2.5 text-right">Ordered</th>
                                            <th className="p-2.5 text-right">Shipped Qty</th>
                                            <th className="p-2.5 text-center">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-200">
                                        {activeNote?.items.map((item, idx) => (
                                            <tr key={idx}>
                                                <td className="p-2.5 text-slate-500">{idx + 1}</td>
                                                <td className="p-2.5 font-semibold text-slate-900">{item.product_name}</td>
                                                <td className="p-2.5 font-mono text-slate-600">{item.sku || '-'}</td>
                                                <td className="p-2.5 font-mono text-slate-600">{item.batch_number || '-'}</td>
                                                <td className="p-2.5 text-right">{item.quantity_ordered}</td>
                                                <td className="p-2.5 text-right font-bold text-slate-900">{item.quantity_shipped}</td>
                                                <td className="p-2.5 text-center text-slate-600">{item.unit || 'units'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot className="bg-slate-50 font-bold border-t border-slate-300">
                                        <tr>
                                            <td colSpan={5} className="p-2.5 text-right">Total Shipped Quantity:</td>
                                            <td className="p-2.5 text-right text-emerald-800 font-bold text-sm">
                                                {activeNote?.total_units_shipped}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {/* Notes */}
                            {activeNote?.notes && (
                                <div className="text-xs text-slate-600 bg-slate-50 p-2.5 rounded border">
                                    <strong>Special Instructions:</strong> {activeNote.notes}
                                </div>
                            )}

                            {/* Verification QR and Digital Signature Block */}
                            <div className="flex items-center justify-between pt-4 border-t border-slate-200 text-xs">
                                <div className="flex items-center gap-3">
                                    <div className="p-2 bg-slate-100 rounded border border-slate-300">
                                        <QrCode className="w-12 h-12 text-slate-800" />
                                    </div>
                                    <div className="space-y-0.5">
                                        <div className="font-bold text-slate-900">VERIFIABLE DIGITAL DOCUMENT</div>
                                        <div className="text-[10px] text-slate-500 font-mono">
                                            HASH: {activeNote?.verification_hash?.substring(0, 24)}...
                                        </div>
                                        <div className="text-[10px] text-emerald-700 font-semibold">
                                            Status: {activeNote?.status.toUpperCase()}
                                        </div>
                                    </div>
                                </div>

                                {activeNote?.status === 'delivered' && (
                                    <div className="text-right">
                                        <div className="font-bold text-emerald-800">PROOF OF DELIVERY CONFIRMED</div>
                                        <div className="text-slate-600">Recipient: {activeNote.proof_of_delivery_recipient}</div>
                                        <div className="text-[10px] text-slate-500 font-mono">
                                            SIG: {activeNote.proof_of_delivery_signature}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Signatures 3-Column Block */}
                            <div className="grid grid-cols-3 gap-6 pt-6 border-t border-slate-200 text-xs text-center">
                                <div className="space-y-10">
                                    <div className="border-b border-slate-400 pb-1 font-semibold">
                                        Warehouse Logistics Officer
                                    </div>
                                    <div className="text-[11px] text-slate-500">Authorized Signature & Stamp</div>
                                </div>

                                <div className="space-y-10">
                                    <div className="border-b border-slate-400 pb-1 font-semibold">
                                        {activeNote?.driver_name || 'Driver / Carrier'}
                                    </div>
                                    <div className="text-[11px] text-slate-500">Transporter Handover</div>
                                </div>

                                <div className="space-y-10">
                                    <div className="border-b border-slate-400 pb-1 font-semibold">
                                        {activeNote?.proof_of_delivery_recipient || 'Consignee Receiver'}
                                    </div>
                                    <div className="text-[11px] text-slate-500">Received in Good Order</div>
                                </div>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>

                {/* MODAL 6: Delete Confirmation */}
                <Dialog open={isDeleteOpen} onOpenChange={setIsDeleteOpen}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle className="text-red-600 flex items-center gap-2">
                                <Trash2 className="w-5 h-5" />
                                Delete Delivery Note
                            </DialogTitle>
                            <DialogDescription>
                                Are you sure you want to delete delivery note {activeNote?.delivery_number}? If goods were already dispatched, inventory stock will be restored automatically.
                            </DialogDescription>
                        </DialogHeader>

                        <DialogFooter>
                            <Button variant="outline" onClick={() => setIsDeleteOpen(false)}>
                                Cancel
                            </Button>
                            <Button
                                onClick={submitDelete}
                                variant="destructive"
                            >
                                Delete Delivery Note
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </AuthenticatedLayout>
    );
}
