import { FormEventHandler, useEffect, useState } from "react";
import AuthLayout from "@/layouts/auth-layout";
import { Head, Link, useForm, router } from "@inertiajs/react";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import InputError from "@/components/ui/input-error";
import { Checkbox } from "@/components/ui/checkbox";

import { useTranslation } from 'react-i18next';
import { useFormFields } from '@/hooks/useFormFields';
import { usePageButtons } from '@/hooks/usePageButtons';

export default function Login({
    status,
    canResetPassword,
    enableRegistration,
    prefillEmail = '',
    isDemo = false,
}: {
    status?: string;
    canResetPassword: boolean;
    enableRegistration?: boolean;
    prefillEmail?: string;
    isDemo?: boolean;
}) {
    const { t } = useTranslation();
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        email: prefillEmail || "",
        password: "",
        remember: false,
        recaptcha_token: null,
    });

    const formFields = useFormFields('getReCaptchFields', data, setData, errors, 'create', t);
    const showLoading = processing || isSubmitting;
    const loginButtons = usePageButtons('getLoginButtons', { t, isLoading: showLoading });

    useEffect(() => {
        if (prefillEmail) {
            setData('email', prefillEmail);
        }
    }, [prefillEmail]);

    useEffect(() => {
        if (isDemo) {
            setData((prevData) => ({
                ...prevData,
                email: 'company@example.com',
                password: '1234',
            }));
        }
    }, [isDemo]);

    useEffect(() => {
        return () => {
            reset("password");
        };
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        if (processing || isSubmitting) {
            return;
        }

        setIsSubmitting(true);

        post(route("login"), {
            onError: () => {
                setIsSubmitting(false);
                if ((window as any).refreshRecaptchaV3) {
                    (window as any).refreshRecaptchaV3();
                }
            },
            onFinish: () => setIsSubmitting(false),
        });
    };

    const handleQuickLogin = (email: string, password: string) => {
        if (processing || isSubmitting) {
            return;
        }

        setIsSubmitting(true);

        router.post(route('login'), {
            email,
            password,
            remember: data.remember,
            recaptcha_token: data.recaptcha_token || '',
        }, {
            onError: () => setIsSubmitting(false),
            onFinish: () => setIsSubmitting(false),
        });
    };

    return (
        <AuthLayout
            title={t('Log in to your account')}
            description={t('Enter your email and password below to log in')}
        >
            <Head title={t('Log in')} />

            {status && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="email" className="text-sm font-medium text-[#a8bcd4]">{t('Email address')}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="email"
                            placeholder="email@example.com"
                            className="w-full border-[#132848] bg-[#04091a] px-3 py-2 text-sm text-white placeholder:text-[#7a90b0] focus-visible:border-[#00c9a7] focus-visible:ring-[#00c9a7]/30"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="space-y-2">
                        <div className="flex items-center justify-between">
                            <Label htmlFor="password" className="text-sm font-medium text-[#a8bcd4]">{t('Password')}</Label>
                            {canResetPassword && (
                                <Link
                                    href={route('password.request')}
                                    className="text-sm text-[#00c9a7] hover:underline"
                                    tabIndex={5}
                                >
                                    {t('Forgot password?')}
                                </Link>
                            )}
                        </div>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            placeholder={t('Password')}
                            className="w-full border-[#132848] bg-[#04091a] px-3 py-2 text-sm text-white placeholder:text-[#7a90b0] focus-visible:border-[#00c9a7] focus-visible:ring-[#00c9a7]/30"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="mb-5 mt-4 flex items-center space-x-3">
                        <Checkbox
                            id="remember"
                            name="remember"
                            checked={data.remember}
                            onCheckedChange={(checked) => setData('remember', !!checked)}
                            tabIndex={3}
                            className="h-[14px] w-[14px] rounded border-[#132848]"
                        />
                        <Label htmlFor="remember" className="text-sm text-[#a8bcd4]">{t('Remember me')}</Label>
                    </div>

                    {formFields.map((field) => (
                        <div key={field.id}>
                            {field.component}
                        </div>
                    ))}

                    <Button
                        type="submit"
                        className="mt-4 w-full rounded-full bg-[#00c9a7] py-2.5 text-sm font-bold tracking-wide text-[#04091a] shadow-[0_4px_20px_rgba(0,201,167,0.3)] transition-all hover:bg-[#009f85] hover:shadow-[0_8px_30px_rgba(0,201,167,0.4)]"
                        tabIndex={4}
                        disabled={showLoading}
                        data-test="login-button"
                    >
                        {showLoading ? t('Signing in...') : t('SIGN IN')}
                    </Button>

                    {loginButtons.length > 0 && (
                        <>
                            {/* Divider */}
                            <div className="my-5">
                                <div className="flex items-center">
                                    <div className="h-px flex-1 bg-[#132848]"></div>
                                    <div className="mx-4 h-2 w-2 rotate-45 bg-[#00c9a7]"></div>
                                    <div className="h-px flex-1 bg-[#132848]"></div>
                                </div>
                            </div>
                            
                            <div className="space-y-2">
                                <div className="relative">
                                    <div className="absolute inset-0 flex items-center">
                                        <span className="w-full border-t border-[#132848]" />
                                    </div>
                                    <div className="relative flex justify-center text-xs uppercase">
                                        <span className="bg-[#0d1f3c] px-2 text-[#7a90b0]">{t('Or continue with')}</span>
                                    </div>
                                </div>
                                {loginButtons.map((button) => (
                                    <div key={button.id}>
                                        {button.component}
                                    </div>
                                ))}
                            </div>
                        </>
                    )}
                </div>

                {enableRegistration && (
                    <div className="text-center mt-5">
                        <p className="text-sm text-[#7a90b0]">
                            {t("Don't have an account?")}{' '}
                            <Link href={route('register')} tabIndex={6} className="font-medium text-[#00c9a7] hover:underline">
                                {t('Create one')}
                            </Link>
                        </p>
                    </div>
                )}

                {isDemo && (
                    <div className="mt-5">
                        <div className="flex items-center">
                            <div className="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                            <div className="w-2 h-2 rotate-45 mx-4 bg-primary"></div>
                            <div className="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                        </div>
                    </div>
                )}

                {isDemo && (
                    <div>
                        <h3 className="mb-4 text-center text-sm font-medium tracking-wider text-[#a8bcd4]">{t('Quick Access')}</h3>
                        <div className="grid sm:grid-cols-2 gap-3">
                            <Button
                                type="button"
                                onClick={() => handleQuickLogin('superadmin@example.com', '1234')}
                                disabled={showLoading}
                                className="group relative col-span-2 h-auto rounded-full border border-[#00c9a7]/30 bg-[#00c9a7]/10 px-4 py-2 text-[13px] font-medium text-[#00c9a7] transition-all hover:bg-[#00c9a7]/20 disabled:opacity-50 sm:col-span-2"
                            >
                                {t('Login as Super Admin')}
                            </Button>
                            <Button
                                type="button"
                                onClick={() => handleQuickLogin('company@example.com', '1234')}
                                disabled={showLoading}
                                className="group relative h-auto rounded-full border border-[#00c9a7]/30 bg-[#00c9a7]/10 px-4 py-2 text-[13px] font-medium text-[#00c9a7] transition-all hover:bg-[#00c9a7]/20 disabled:opacity-50"
                            >
                                {t('Login as Company')}
                            </Button>
                            <Button
                                type="button"
                                onClick={() => handleQuickLogin('john.smith@company.com', '1234')}
                                disabled={showLoading}
                                className="group relative h-auto rounded-full border border-[#00c9a7]/30 bg-[#00c9a7]/10 px-4 py-2 text-[13px] font-medium text-[#00c9a7] transition-all hover:bg-[#00c9a7]/20 disabled:opacity-50"
                            >
                                {t('Login as Employee')}
                            </Button>
                            <Button
                                type="button"
                                onClick={() => handleQuickLogin('sarah.johnson@client.com', '1234')}
                                disabled={showLoading}
                                className="group relative h-auto rounded-full border border-[#00c9a7]/30 bg-[#00c9a7]/10 px-4 py-2 text-[13px] font-medium text-[#00c9a7] transition-all hover:bg-[#00c9a7]/20 disabled:opacity-50"
                            >
                                {t('Login as Customer')}
                            </Button>
                            <Button
                                type="button"
                                onClick={() => handleQuickLogin('alex.vendor@supplier.com', '1234')}
                                disabled={showLoading}
                                className="group relative h-auto rounded-full border border-[#00c9a7]/30 bg-[#00c9a7]/10 px-4 py-2 text-[13px] font-medium text-[#00c9a7] transition-all hover:bg-[#00c9a7]/20 disabled:opacity-50"
                            >
                                {t('Login as Vendor')}
                            </Button>
                        </div>
                    </div>
                )}
            </form>
        </AuthLayout>
    );
}
