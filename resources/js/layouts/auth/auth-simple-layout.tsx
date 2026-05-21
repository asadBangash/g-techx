import { Link, usePage } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';
import { LanguageSwitcher } from '@/components/language-switcher';
import { useBrand } from '@/contexts/brand-context';
import { useFavicon } from '@/hooks/use-favicon';
import GtechxLogoIcon from '@/components/gtechx-logo-icon';
import CookieConsent from '@/components/cookie-consent';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    const pageProps = usePage().props as Record<string, unknown>;
    const { settings } = useBrand();
    const { adminAllSetting, brand } = pageProps as {
        adminAllSetting?: Record<string, unknown>;
        brand?: { short_name?: string; tagline?: string; full_name?: string };
    };
    useFavicon();

    const nameParts = (brand?.short_name || 'G-TechX').split('-', 2);

    return (
        <div className="gtechx-auth min-h-screen bg-[#060e1e] text-[#f0f6ff] relative overflow-hidden">
            <div
                className="absolute inset-0 opacity-40"
                style={{
                    backgroundImage:
                        'linear-gradient(rgba(0,201,167,.055) 1px, transparent 1px), linear-gradient(90deg, rgba(0,201,167,.055) 1px, transparent 1px)',
                    backgroundSize: '60px 60px',
                }}
            />
            <div className="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#00c9a7]/10 blur-3xl" />
            <div className="absolute -bottom-24 -left-24 h-80 w-80 rounded-full bg-[#f0a500]/10 blur-3xl" />

            <div className="absolute top-6 right-6 z-20 hidden md:block">
                <LanguageSwitcher />
            </div>

            <div className="relative z-10 flex min-h-screen flex-col items-center justify-center p-6">
                <div className="w-full max-w-md">
                    <div className="mb-8 text-center">
                        <Link href="/" className="inline-flex flex-col items-center gap-3">
                            <div className="flex items-center gap-3">
                                <GtechxLogoIcon className="h-12 w-12 shrink-0" />
                                <div className="text-left leading-none">
                                    <div className="text-2xl font-bold tracking-tight">
                                        {nameParts[0]}
                                        {nameParts[1] ? (
                                            <>
                                                -<span className="text-[#00c9a7]">{nameParts[1]}</span>
                                            </>
                                        ) : null}
                                    </div>
                                    <div className="mt-1 text-[0.58rem] font-bold uppercase tracking-[0.18em] text-[#7a90b0]">
                                        {brand?.tagline || 'Accounting Solution'}
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <div className="rounded-2xl border border-[#00c9a7]/20 bg-[#0d1f3c]/80 p-6 shadow-[0_24px_80px_rgba(0,0,0,0.5)] backdrop-blur-xl sm:p-8">
                        <div className="mb-6 text-center">
                            <h1 className="text-xl font-bold text-white sm:text-2xl">{title}</h1>
                            <div className="mx-auto my-3 h-px w-12 bg-gradient-to-r from-[#00c9a7] to-[#f0a500]" />
                            <p className="text-sm text-[#a8bcd4]">{description}</p>
                        </div>
                        {children}
                    </div>

                    <div className="mt-6 text-center">
                        <p className="text-xs text-[#7a90b0]">
                            {settings.footerText ||
                                `© ${new Date().getFullYear()} Global TechX & Accounting Solution (G-TechX)`}
                        </p>
                        <Link href="/" className="mt-2 inline-block text-xs text-[#00c9a7] hover:underline">
                            ← Back to website
                        </Link>
                    </div>
                </div>
            </div>

            <CookieConsent settings={adminAllSetting || {}} />
        </div>
    );
}
