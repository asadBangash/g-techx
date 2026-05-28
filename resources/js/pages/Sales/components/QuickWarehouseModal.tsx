import { useState } from 'react';
import axios from 'axios';
import { useTranslation } from 'react-i18next';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { PhoneInputComponent } from '@/components/ui/phone-input';

export interface QuickWarehouse {
    id: number;
    name: string;
    address: string;
}

interface QuickWarehouseModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onCreated: (warehouse: QuickWarehouse) => void;
}

interface FormData {
    name: string;
    address: string;
    city: string;
    zip_code: string;
    phone: string;
    email: string;
}

const initialForm: FormData = {
    name: '',
    address: '',
    city: '',
    zip_code: '',
    phone: '',
    email: '',
};

export default function QuickWarehouseModal({ open, onOpenChange, onCreated }: QuickWarehouseModalProps) {
    const { t } = useTranslation();
    const [form, setForm] = useState<FormData>(initialForm);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [processing, setProcessing] = useState(false);

    const resetForm = () => {
        setForm(initialForm);
        setErrors({});
    };

    const handleClose = () => {
        resetForm();
        onOpenChange(false);
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setProcessing(true);
        setErrors({});

        try {
            const response = await axios.post(route('sales-invoices.quick.warehouse'), {
                ...form,
                is_active: true,
            });
            onCreated(response.data.warehouse);
            handleClose();
        } catch (error: any) {
            if (error.response?.status === 422) {
                const validationErrors = error.response.data.errors ?? {};
                const mapped: Record<string, string> = {};
                Object.entries(validationErrors).forEach(([key, value]) => {
                    mapped[key] = Array.isArray(value) ? value[0] : String(value);
                });
                setErrors(mapped);
            } else {
                setErrors({ name: error.response?.data?.message || t('Failed to create warehouse.') });
            }
        } finally {
            setProcessing(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={(value) => (value ? onOpenChange(true) : handleClose())}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>{t('Create Warehouse')}</DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <Label htmlFor="quick_warehouse_name" required>{t('Name')}</Label>
                        <Input
                            id="quick_warehouse_name"
                            value={form.name}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                            placeholder={t('Enter warehouse name')}
                            required
                        />
                        <InputError message={errors.name} />
                    </div>
                    <div>
                        <Label htmlFor="quick_warehouse_address" required>{t('Address')}</Label>
                        <Input
                            id="quick_warehouse_address"
                            value={form.address}
                            onChange={(e) => setForm({ ...form, address: e.target.value })}
                            placeholder={t('Enter full address')}
                            required
                        />
                        <InputError message={errors.address} />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="quick_warehouse_city" required>{t('City')}</Label>
                            <Input
                                id="quick_warehouse_city"
                                value={form.city}
                                onChange={(e) => setForm({ ...form, city: e.target.value })}
                                placeholder={t('Enter city')}
                                required
                            />
                            <InputError message={errors.city} />
                        </div>
                        <div>
                            <Label htmlFor="quick_warehouse_zip" required>{t('Zip Code')}</Label>
                            <Input
                                id="quick_warehouse_zip"
                                value={form.zip_code}
                                onChange={(e) => setForm({ ...form, zip_code: e.target.value })}
                                placeholder={t('Enter zip code')}
                                required
                            />
                            <InputError message={errors.zip_code} />
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="quick_warehouse_phone">{t('Phone')}</Label>
                            <PhoneInputComponent
                                value={form.phone}
                                onChange={(value) => setForm({ ...form, phone: value })}
                                placeholder={t('Enter phone number')}
                            />
                            <InputError message={errors.phone} />
                        </div>
                        <div>
                            <Label htmlFor="quick_warehouse_email">{t('Email')}</Label>
                            <Input
                                id="quick_warehouse_email"
                                type="email"
                                value={form.email}
                                onChange={(e) => setForm({ ...form, email: e.target.value })}
                                placeholder={t('Enter email address')}
                            />
                            <InputError message={errors.email} />
                        </div>
                    </div>
                    <div className="flex justify-end gap-2 pt-2">
                        <Button type="button" variant="outline" onClick={handleClose}>
                            {t('Cancel')}
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? t('Creating...') : t('Create')}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
