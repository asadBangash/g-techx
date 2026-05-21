export function resolveBrandLogoUrl(path?: string, pageProps?: Record<string, unknown>): string {
    const brandLogoUrl = pageProps?.brandLogoUrl as string | undefined;
    const baseUrl = (pageProps?.baseUrl as string) || (typeof window !== 'undefined' ? window.location.origin : '');
    const imageUrlPrefix = pageProps?.imageUrlPrefix as string | undefined;
    const fallback = brandLogoUrl || `${baseUrl}/assets/brand/gtechx-logo.png`;

    if (!path) {
        return fallback;
    }

    if (path.startsWith('http')) {
        return path;
    }

    if (path.includes('packages/workdo') || path.includes('storage/media') || path.startsWith('assets/')) {
        const cleanPath = path.startsWith('/') ? path : `/${path}`;
        return `${baseUrl}${cleanPath}`;
    }

    if (imageUrlPrefix) {
        const prefixEndsWithSlash = imageUrlPrefix.endsWith('/');
        const pathStartsWithSlash = path.startsWith('/');
        if (prefixEndsWithSlash && pathStartsWithSlash) {
            return imageUrlPrefix + path.substring(1);
        }
        if (!prefixEndsWithSlash && !pathStartsWithSlash) {
            return `${imageUrlPrefix}/${path}`;
        }
        return imageUrlPrefix + path;
    }

    return fallback;
}
