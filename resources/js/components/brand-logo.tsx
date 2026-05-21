import { ImgHTMLAttributes } from 'react';
import { usePage } from '@inertiajs/react';
import { useBrand } from '@/contexts/brand-context';
import { resolveBrandLogoUrl } from '@/utils/brand';
import GtechxLogoIcon from '@/components/gtechx-logo-icon';

interface BrandLogoProps extends ImgHTMLAttributes<HTMLImageElement> {
    variant?: 'dark' | 'light';
    showIconOnly?: boolean;
}

export default function BrandLogo({
    variant = 'dark',
    showIconOnly = false,
    className = '',
    ...props
}: BrandLogoProps) {
    const pageProps = usePage().props as Record<string, unknown>;
    const { settings } = useBrand();
    const { brand } = pageProps as { brand?: { short_name?: string } };

    if (showIconOnly) {
        return <GtechxLogoIcon className={className || 'h-10 w-10'} />;
    }

    const logoSetting = variant === 'light' ? settings.logo_light : settings.logo_dark;
    const logoSrc = resolveBrandLogoUrl(logoSetting, pageProps);

    return (
        <img
            src={logoSrc}
            alt={settings.titleText || brand?.short_name || 'G-TechX'}
            className={className || 'h-10 w-auto max-w-[220px] object-contain'}
            {...props}
        />
    );
}
