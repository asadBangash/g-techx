import BrandLogo from "@/components/brand-logo";
import { Link, usePage, Head } from "@inertiajs/react";
import { PropsWithChildren } from "react";
import CookieConsent from "@/components/cookie-consent";
import { BrandProvider } from "@/contexts/brand-context";
import { useFavicon } from "@/hooks/use-favicon";
import { useFlashMessages } from "@/hooks/useFlashMessages";

function GuestContent({ children }: PropsWithChildren) {
    const { companyAllSetting, adminAllSetting, brand } = usePage().props as any;
    useFavicon();
    useFlashMessages();
    
    return (
        <>
        <Head>
            {companyAllSetting?.metaKeywords && (
                <meta name="keywords" content={companyAllSetting.metaKeywords} />
            )}
            {companyAllSetting?.metaDescription && (
                <meta name="description" content={companyAllSetting.metaDescription} />
            )}
            {companyAllSetting?.metaImage && (
                <meta property="og:image" content={companyAllSetting.metaImage} />
            )}
        </Head>
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-background">
            <div>
                <Link href="/" className="flex flex-col items-center gap-1">
                    <BrandLogo className="h-16 w-16 object-contain" />
                    <span className="text-sm font-semibold text-foreground">
                        {brand?.short_name || 'G-TechX'}
                    </span>
                </Link>
            </div>

            <div className="w-full sm:max-w-md mt-6 px-6 py-4">{children}</div>
            <CookieConsent settings={adminAllSetting || {}} />
        </div>
        </>
    );
}

export default function Guest(props: PropsWithChildren) {
    return (
        <BrandProvider>
            <GuestContent {...props} />
        </BrandProvider>
    );
}
