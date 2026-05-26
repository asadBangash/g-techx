import "./bootstrap";
import "../css/app.css";
import "../css/rtl.css";
import "./i18n";

import { createRoot } from "react-dom/client";
import { createInertiaApp, router } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ThemeProvider } from "@/components/theme-provider";
import { Toaster } from "sonner";
import { Suspense } from "react";
import axios from "axios";

const syncCsrfToken = (token?: string | null) => {
    if (!token) {
        return;
    }

    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        meta.setAttribute('content', token);
    }

    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
};

// Keep CSRF meta tag in sync on every Inertia navigation
router.on('success', (event) => {
    const token = (event.detail.page.props as { csrf_token?: string })?.csrf_token;
    syncCsrfToken(token);
});

// Handle Inertia external redirects (409) and expired CSRF tokens (419)
router.on('invalid', (event) => {
    const response = event.detail.response;
    const status = response?.status;

    if (status === 409) {
        const location = response.headers?.['x-inertia-location'];
        if (location) {
            window.location.href = location;
            return;
        }
    }

    if (status === 419) {
        event.preventDefault();
        window.location.reload();
    }
});

// Silent CSRF token refresh fallback
const refreshToken = async () => {
    try {
        const response = await fetch(window.location.href, {
            method: 'GET',
            credentials: 'same-origin',
        });
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newToken = doc.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        syncCsrfToken(newToken);
    } catch (e) {}
};

router.on('before', () => {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!token) {
        refreshToken();
    }
});

// Global fetch interceptor
const originalFetch = window.fetch;
window.fetch = async (...args) => {
    const [url, options] = args;
    
    // Ensure fresh token before request
    if (options && options.method && options.method !== 'GET') {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            await refreshToken();
        }
        // Update token in headers
        const newToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (newToken && options.headers) {
            (options.headers as any)['X-CSRF-TOKEN'] = newToken;
        }
    }
    
    const response = await originalFetch(...args);
    
    // Fallback: retry on 419 error
    if (response.status === 419) {
        await refreshToken();
        if (options && options.headers) {
            const newToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (newToken) {
                (options.headers as any)['X-CSRF-TOKEN'] = newToken;
            }
        }
        return originalFetch(...args);
    }
    return response;
};

createInertiaApp({
    title: (title) => {
        const initialPage = JSON.parse(
            document.getElementById("app")?.dataset.page || "{}"
        );
        const pageProps = initialPage?.props ?? {};
        let customTitle;
        if (pageProps?.auth?.user?.type === "superadmin") {
            customTitle = pageProps?.adminAllSetting?.titleText;
        } else if (pageProps?.auth?.user?.type) {
            customTitle = pageProps?.companyAllSetting?.titleText;
        } else {
            customTitle = pageProps?.adminAllSetting?.titleText;
        }
        const appName = customTitle || import.meta.env.VITE_APP_NAME || "Laravel";
        return `${title} - ${appName}`;
    },
    resolve: (name) => {
        const allPages = {
            ...import.meta.glob('./pages/**/*.tsx'),
            ...import.meta.glob('../../packages/workdo/*/src/Resources/js/Pages/**/*.tsx')
        };

        // Try pages directory (lowercase p)
        const lowerPagePath = `./pages/${name}.tsx`;
        if (allPages[lowerPagePath]) {
            return allPages[lowerPagePath]();
        }

        // Try package pages
        const [packageName, ...pagePath] = name.split('/');
        const packagePagePath = `../../packages/workdo/${packageName}/src/Resources/js/Pages/${pagePath.join('/')}.tsx`;
        if (allPages[packagePagePath]) {
            return allPages[packagePagePath]();
        }

        throw new Error(`Page not found: ${name}`);
    },
    setup({ el, App, props }) {
        // Make props globally available
        (window as any).page = props;
        const root = createRoot(el);

        root.render(
            <ThemeProvider
                attribute="class"
                defaultTheme="light"
                enableSystem
                disableTransitionOnChange
            >
                <Suspense fallback={null}>
                    <App {...props} />
                </Suspense>
                <Toaster position="top-center" richColors />
            </ThemeProvider>
        );
    },
    progress: {
        color: "#4B5563",
    },
});
