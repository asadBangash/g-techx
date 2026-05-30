import React from 'react';
import { useTranslation } from 'react-i18next';
import { usePage } from '@inertiajs/react';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { getCompanySetting } from '@/utils/helpers';

interface CurrencyOption {
    id: number;
    name: string;
    code: string;
    symbol: string;
}

interface InvoiceCurrencyFieldsProps {
    currencyCode: string;
    exchangeRate: number | string;
    defaultCurrency?: string;
    onCurrencyChange: (code: string) => void;
    onExchangeRateChange: (rate: number) => void;
    errors?: {
        currency_code?: string;
        exchange_rate?: string;
    };
}

export default function InvoiceCurrencyFields({
    currencyCode,
    exchangeRate,
    defaultCurrency,
    onCurrencyChange,
    onExchangeRateChange,
    errors = {},
}: InvoiceCurrencyFieldsProps) {
    const { t } = useTranslation();
    const { currencies = [] } = usePage().props as { currencies?: CurrencyOption[] };
    const baseCurrency = defaultCurrency || getCompanySetting('defaultCurrency') || 'USD';
    const isForeignCurrency = currencyCode !== baseCurrency;

    const handleCurrencyChange = (code: string) => {
        onCurrencyChange(code);
        if (code === baseCurrency) {
            onExchangeRateChange(1);
        }
    };

    return (
        <>
            <div>
                <Label htmlFor="currency_code" required>
                    {t('Currency')}
                </Label>
                <Select value={currencyCode} onValueChange={handleCurrencyChange}>
                    <SelectTrigger>
                        <SelectValue placeholder={t('Select Currency')} />
                    </SelectTrigger>
                    <SelectContent searchable>
                        {currencies.map((currency) => (
                            <SelectItem key={currency.code} value={currency.code}>
                                {currency.code} — {currency.name} ({currency.symbol})
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <InputError message={errors.currency_code} />
            </div>

            {isForeignCurrency && (
                <div>
                    <Label htmlFor="exchange_rate" required>
                        {t('Exchange Rate')} ({currencyCode} → {baseCurrency})
                    </Label>
                    <Input
                        id="exchange_rate"
                        type="number"
                        value={exchangeRate}
                        onChange={(e) => onExchangeRateChange(parseFloat(e.target.value) || 0)}
                        min="0.000001"
                        step="0.000001"
                        required
                    />
                    <p className="text-xs text-muted-foreground mt-1">
                        {t('Rate to convert invoice amounts to your base currency ({{base}}).', { base: baseCurrency })}
                    </p>
                    <InputError message={errors.exchange_rate} />
                </div>
            )}
        </>
    );
}
