import React, { useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useFormFields } from '@/hooks/useFormFields';
import { PurchaseInvoiceItem } from './types';
import AuthenticatedLayout from '@/layouts/authenticated-layout';
import InvoiceItemsTable from './components/InvoiceItemsTable';
import QuickVendorModal, { QuickVendor } from './components/QuickVendorModal';
import QuickWarehouseModal, { QuickWarehouse } from '../Sales/components/QuickWarehouseModal';
import { useTaxCalculator } from './components/TaxCalculator';
import InvoiceCurrencyFields from '@/components/InvoiceCurrencyFields';
import { formatCurrency, getCompanySetting } from '@/utils/helpers';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { InputError } from '@/components/ui/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DatePicker } from '@/components/ui/date-picker';
import { Separator } from '@/components/ui/separator';
import { CalendarDays, Package, Plus } from 'lucide-react';

interface CreateProps {
    vendors: Array<{id: number; name: string; email: string; currency_code?: string}>;
    products: Array<{id: number; name: string; sku: string; purchase_price: number; unit: string; type: string; taxes: Array<{id: number; tax_name: string; rate: number}>}>;
    warehouses: Array<{id: number; name: string; address: string}>;
    modules?: {recurringinvoicebill?: boolean};
    quickAddUrls?: {
        vendor: string;
        warehouse: string;
    };
    auth: {
        user: {
            permissions: string[];
        };
    };
    [key: string]: any;
}

export default function Create() {
    const { t } = useTranslation();
    const {
        vendors: initialVendors,
        products,
        warehouses: initialWarehouses,
        modules,
        quickAddUrls,
        auth,
        defaultCurrency,
    } = usePage<CreateProps>().props;
    const baseCurrency = defaultCurrency || getCompanySetting('defaultCurrency') || 'USD';

    const [vendors, setVendors] = useState(initialVendors);
    const [warehouses, setWarehouses] = useState(initialWarehouses);
    const [vendorModalOpen, setVendorModalOpen] = useState(false);
    const [warehouseModalOpen, setWarehouseModalOpen] = useState(false);

    const canCreateVendor = auth?.user?.permissions?.includes('create-vendors');
    const canCreateWarehouse = auth?.user?.permissions?.includes('create-warehouses');

    const { data, setData, post, processing, errors } = useForm({
        invoice_date: new Date().toISOString().split('T')[0],
        due_date: '',
        vendor_id: '',
        warehouse_id: '',
        payment_terms: '',
        notes: '',
        currency_code: baseCurrency,
        exchange_rate: 1,
        sync_to_google_calendar: false,
        items: [{
            product_id: 0,
            quantity: 1,
            unit_price: 0,
            discount_percentage: 0,
            discount_amount: 0,
            tax_percentage: 0,
            tax_amount: 0,
            total_amount: 0
        }] as PurchaseInvoiceItem[]
    });

    const calendarFields = useFormFields('createCalendarSyncField', data, setData, errors, 'create', t, 'Purchase');

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('purchase-invoices.store'));
    };

    const totals = useTaxCalculator(data.items);

    const handleVendorChange = (vendorId: string) => {
        const vendor = vendors.find((item) => item.id.toString() === vendorId);
        if (vendor?.currency_code) {
            setData((prev) => ({
                ...prev,
                vendor_id: vendorId,
                currency_code: vendor.currency_code!,
                exchange_rate: vendor.currency_code === baseCurrency ? 1 : prev.exchange_rate,
            }));
        } else {
            setData('vendor_id', vendorId);
        }
    };

    const handleVendorCreated = (vendor: QuickVendor) => {
        setVendors((prev) => {
            if (prev.some((item) => item.id === vendor.id)) {
                return prev;
            }
            return [...prev, vendor];
        });
        setData('vendor_id', vendor.id.toString());
    };

    const handleWarehouseCreated = (warehouse: QuickWarehouse) => {
        setWarehouses((prev) => {
            if (prev.some((item) => item.id === warehouse.id)) {
                return prev;
            }
            return [...prev, warehouse];
        });
        setData('warehouse_id', warehouse.id.toString());
    };

    // Recurring fields hook
    const recurringFields = useFormFields('purchaseInvoiceCreateFields', data, setData, errors, 'create');

    return (
        <AuthenticatedLayout
            breadcrumbs={[
                {label: t('Purchase'), url: route('purchase-invoices.index')},
                {label: t('Create Purchase Invoice')}
            ]}
            pageTitle={t('Create Purchase Invoice')}
            backUrl={route('purchase-invoices.index')}
        >
            <Head title={t('Create Purchase Invoice')} />

            <div>
                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Invoice Details */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <CalendarDays className="h-5 w-5" />
                                {t('Purchase Invoice Details')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div>
                                    <Label htmlFor="invoice_date" required>
                                        {t('Invoice Date')}
                                    </Label>
                                    <DatePicker
                                        id="invoice_date"
                                        value={data.invoice_date}
                                        onChange={(value) => setData('invoice_date', value)}
                                        required
                                    />
                                    <InputError message={errors.invoice_date} />
                                </div>

                                <div>
                                    <Label htmlFor="due_date" required>
                                        {t('Due Date')}
                                    </Label>
                                    <DatePicker
                                        id="due_date"
                                        value={data.due_date}
                                        onChange={(value) => setData('due_date', value)}
                                        required
                                    />
                                    <InputError message={errors.due_date} />
                                </div>

                                <div>
                                    <div className="flex items-center gap-1.5 mb-1.5">
                                        <Label htmlFor="vendor_id" required className="mb-0">
                                            {t('Vendor')}
                                        </Label>
                                        {canCreateVendor && (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                                className="h-6 w-6 shrink-0"
                                                onClick={() => setVendorModalOpen(true)}
                                                title={t('Add Vendor')}
                                            >
                                                <Plus className="h-3.5 w-3.5" />
                                            </Button>
                                        )}
                                    </div>
                                    <Select value={data.vendor_id} onValueChange={handleVendorChange}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Vendor')} />
                                        </SelectTrigger>
                                        <SelectContent searchable>
                                            {vendors.map((vendor) => (
                                                <SelectItem key={vendor.id} value={vendor.id.toString()}>
                                                    {vendor.name} - {vendor.email}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.vendor_id} />
                                </div>

                                <div>
                                    <div className="flex items-center gap-1.5 mb-1.5">
                                        <Label htmlFor="warehouse_id" required className="mb-0">
                                            {t('Warehouse')}
                                        </Label>
                                        {canCreateWarehouse && (
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="icon"
                                                className="h-6 w-6 shrink-0"
                                                onClick={() => setWarehouseModalOpen(true)}
                                                title={t('Add Warehouse')}
                                            >
                                                <Plus className="h-3.5 w-3.5" />
                                            </Button>
                                        )}
                                    </div>
                                    <Select value={data.warehouse_id} onValueChange={(value) => setData('warehouse_id', value)}>
                                        <SelectTrigger>
                                            <SelectValue placeholder={t('Select Warehouse')} />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {warehouses.map((warehouse) => (
                                                <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                    {warehouse.name} - {warehouse.address}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.warehouse_id} />
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <InvoiceCurrencyFields
                                    currencyCode={data.currency_code}
                                    exchangeRate={data.exchange_rate}
                                    defaultCurrency={baseCurrency}
                                    onCurrencyChange={(code) => setData('currency_code', code)}
                                    onExchangeRateChange={(rate) => setData('exchange_rate', rate)}
                                    errors={{
                                        currency_code: errors.currency_code,
                                        exchange_rate: errors.exchange_rate,
                                    }}
                                />

                                <div>
                                    <Label htmlFor="payment_terms">
                                        {t('Payment Terms')}
                                    </Label>
                                    <Input
                                        id="payment_terms"
                                        value={data.payment_terms}
                                        onChange={(e) => setData('payment_terms', e.target.value)}
                                        placeholder={t('e.g., Net 30')}
                                    />
                                </div>

                                <div>
                                    <Label htmlFor="notes">
                                        {t('Notes')}
                                    </Label>
                                    <Textarea
                                        id="notes"
                                        value={data.notes}
                                        onChange={(e) => setData('notes', e.target.value)}
                                        rows={2}
                                        placeholder={t('Additional notes...')}
                                    />
                                </div>
                            </div>

                            {/* Recurring fields */}
                            {modules?.recurringinvoicebill && recurringFields.map((field) => (
                                <div key={field.id} className="mt-4">{field.component}</div>
                            ))}
                            
                            {/* Calendar Sync Field */}
                            <div className="mt-6">
                                {calendarFields.map((field) => (
                                    <div key={field.id}>
                                        {field.component}
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Invoice Items */}
                    <Card>
                        <CardHeader>
                            <div className="flex items-center justify-between">
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Package className="h-5 w-5" />
                                    {t('Purchase Invoice Items')}
                                </CardTitle>
                                <Button
                                    type="button"
                                    onClick={() => {
                                        const newItem = {
                                            product_id: 0,
                                            quantity: 1,
                                            unit_price: 0,
                                            discount_percentage: 0,
                                            discount_amount: 0,
                                            tax_percentage: 0,
                                            tax_amount: 0,
                                            total_amount: 0
                                        };
                                        setData('items', [...data.items, newItem]);
                                    }}
                                    variant="default"
                                    size="sm"
                                >
                                    + {t('Add Item')}
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <InvoiceItemsTable
                                items={data.items}
                                onChange={(items) => setData('items', items)}
                                errors={errors}
                                products={products}
                                showAddButton={false}
                                currencyCode={data.currency_code}
                            />

                            {/* Invoice Summary - Bottom of Items */}
                            <div className="mt-6 flex justify-end">
                                <div className="w-80 bg-muted/30 rounded-lg p-4">
                                    <h3 className="font-semibold mb-3">{t('Invoice Summary')}</h3>
                                    <div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">{t('Subtotal')}</span>
                                            <span className="font-medium">{formatCurrency(totals.subtotal, undefined, data.currency_code)}</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">{t('Discount')}</span>
                                            <span className="font-medium text-red-600">-{formatCurrency(totals.discountAmount, undefined, data.currency_code)}</span>
                                        </div>
                                        <div className="flex justify-between text-sm">
                                            <span className="text-muted-foreground">{t('Tax')}</span>
                                            <span className="font-medium">{formatCurrency(totals.taxAmount, undefined, data.currency_code)}</span>
                                        </div>
                                        <Separator className="my-2" />
                                        <div className="flex justify-between">
                                            <span className="font-semibold">{t('Total')}</span>
                                            <span className="font-bold text-lg">{formatCurrency(totals.total, undefined, data.currency_code)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Actions */}
                    <div className="flex justify-between items-center">
                        <div className="text-sm text-muted-foreground">
                            {data.items.length} {t('items added')}
                        </div>
                        <div className="flex gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => window.history.back()}
                            >
                                {t('Cancel')}
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing || data.items.length === 0}
                            >
                                {processing ? t('Creating...') : t('Create')}
                            </Button>
                        </div>
                    </div>
                </form>
            </div>

            <QuickVendorModal
                open={vendorModalOpen}
                onOpenChange={setVendorModalOpen}
                onCreated={handleVendorCreated}
                storeUrl={quickAddUrls?.vendor ?? ''}
            />
            <QuickWarehouseModal
                open={warehouseModalOpen}
                onOpenChange={setWarehouseModalOpen}
                onCreated={handleWarehouseCreated}
                storeUrl={quickAddUrls?.warehouse ?? ''}
            />
        </AuthenticatedLayout>
    );
}
