import { useState } from 'react';
import axios from 'axios';
import { useTranslation } from 'react-i18next';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/ui/input-error';
import { PhoneInputComponent } from '@/components/ui/phone-input';

export interface QuickCustomer {
    id: number;
    name: string;
    email: string;
}

interface QuickCustomerModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onCreated: (customer: QuickCustomer) => void;
}

interface FormData {
    name: string;
    email: string;
    mobile: string;
    address: string;
    city: string;
    state: string;
    country: string;
    zip_code: string;
}

const initialForm: FormData = {
    name: '',
    email: '',
    mobile: '',
    address: '',
    city: '',
    state: '',
    country: '',
    zip_code: '',
};

export default function QuickCustomerModal({ open, onOpenChange, onCreated }: QuickCustomerModalProps) {
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
            const response = await axios.post(route('sales-invoices.quick.customer'), form);
            onCreated(response.data.customer);
            handleClose();
        } catch (error: any) {
            if (error.response?.status === 422) {
                const validationErrors = error.response.data.errors ?? {};
                const mapped: Record<string, string> = {};
                Object.entries(validationErrors).forEach(([key, value]) => {
                    mapped[key] = Array.isArray(value) ? value[0] : String(value);
                });
                if (error.response.data.message && !Object.keys(mapped).length) {
                    mapped.email = error.response.data.message;
                }
                setErrors(mapped);
            } else {
                setErrors({ email: error.response?.data?.message || t('Failed to create customer.') });
            }
        } finally {
            setProcessing(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={(value) => (value ? onOpenChange(true) : handleClose())}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>{t('Create Customer')}</DialogTitle>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <Label htmlFor="quick_customer_name" required>{t('Customer Name')}</Label>
                        <Input
                            id="quick_customer_name"
                            value={form.name}
                            onChange={(e) => setForm({ ...form, name: e.target.value })}
                            placeholder={t('Enter customer name')}
                            required
                        />
                        <InputError message={errors.name} />
                    </div>
                    <div>
                        <Label htmlFor="quick_customer_email" required>{t('Email')}</Label>
                        <Input
                            id="quick_customer_email"
                            type="email"
                            value={form.email}
                            onChange={(e) => setForm({ ...form, email: e.target.value })}
                            placeholder={t('Enter email address')}
                            required
                        />
                        <InputError message={errors.email} />
                    </div>
                    <div>
                        <PhoneInputComponent
                            label={t('Mobile Number')}
                            value={form.mobile}
                            onChange={(value) => setForm({ ...form, mobile: value })}
                            placeholder="+1234567890"
                            error={errors.mobile}
                        />
                    </div>
                    <div>
                        <Label htmlFor="quick_customer_address">{t('Address')}</Label>
                        <Input
                            id="quick_customer_address"
                            value={form.address}
                            onChange={(e) => setForm({ ...form, address: e.target.value })}
                            placeholder={t('Enter address')}
                        />
                        <InputError message={errors.address} />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="quick_customer_city">{t('City')}</Label>
                            <Input
                                id="quick_customer_city"
                                value={form.city}
                                onChange={(e) => setForm({ ...form, city: e.target.value })}
                                placeholder={t('Enter city')}
                            />
                            <InputError message={errors.city} />
                        </div>
                        <div>
                            <Label htmlFor="quick_customer_state">{t('State')}</Label>
                            <Input
                                id="quick_customer_state"
                                value={form.state}
                                onChange={(e) => setForm({ ...form, state: e.target.value })}
                                placeholder={t('Enter state')}
                            />
                            <InputError message={errors.state} />
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <Label htmlFor="quick_customer_country">{t('Country')}</Label>
                            <Input
                                id="quick_customer_country"
                                value={form.country}
                                onChange={(e) => setForm({ ...form, country: e.target.value })}
                                placeholder={t('Enter country')}
                            />
                            <InputError message={errors.country} />
                        </div>
                        <div>
                            <Label htmlFor="quick_customer_zip">{t('Zip Code')}</Label>
                            <Input
                                id="quick_customer_zip"
                                value={form.zip_code}
                                onChange={(e) => setForm({ ...form, zip_code: e.target.value })}
                                placeholder={t('Enter zip code')}
                            />
                            <InputError message={errors.zip_code} />
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
