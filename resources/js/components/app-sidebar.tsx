"use client"

import * as React from "react"
import { Search } from "lucide-react"
import { NavMain } from "@/components/nav-main"
import {
  Sidebar,
  SidebarContent,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarInput,
} from "@/components/ui/sidebar"
import { Link, usePage } from "@inertiajs/react"
import { PageProps } from "@/types"
import { allMenuItems } from "@/utils/menu"
import { useTranslation } from 'react-i18next'
import { useBrand } from "@/contexts/brand-context"
import GtechxLogoIcon from "@/components/gtechx-logo-icon"

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    const pageProps = usePage<PageProps>().props as Record<string, unknown>
    const { t } = useTranslation()
    const { settings, getCompleteSidebarProps } = useBrand()
    const [searchQuery, setSearchQuery] = React.useState("")
    const { brand } = pageProps as { brand?: { short_name?: string; tagline?: string } }

    const sidebarProps = getCompleteSidebarProps()
    const nameParts = (settings.titleText || brand?.short_name || 'G-TechX').split('-', 2)

    return (
        <Sidebar
            variant={settings.sidebarVariant as any}
            side={settings.layoutDirection === 'rtl' ? 'right' : 'left'}
            collapsible="icon"
            className={`gtechx-sidebar border-[#132848] ${sidebarProps.className || ''}`}
            style={{
                ...sidebarProps.style,
                backgroundColor: '#060e1e',
                color: '#f0f6ff',
            }}
            {...props}
        >
            <SidebarHeader className="border-b border-[#132848]">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={route('dashboard')} className="flex h-auto !py-4 items-center justify-center">
                                <div className="group-data-[collapsible=icon]:hidden flex min-w-0 items-center gap-2.5 px-1">
                                    <GtechxLogoIcon className="h-10 w-10 shrink-0" />
                                    <div className="min-w-0 leading-none">
                                        <div className="truncate text-base font-bold text-white">
                                            {nameParts[0]}
                                            {nameParts[1] ? (
                                                <>
                                                    -<span className="text-[#00c9a7]">{nameParts[1]}</span>
                                                </>
                                            ) : null}
                                        </div>
                                        <div className="mt-0.5 truncate text-[0.55rem] font-bold uppercase tracking-[0.14em] text-[#7a90b0]">
                                            {brand?.tagline || 'Accounting Solution'}
                                        </div>
                                    </div>
                                </div>

                                <div className="hidden h-9 w-9 group-data-[collapsible=icon]:flex items-center justify-center">
                                    <GtechxLogoIcon className="h-9 w-9" />
                                </div>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <div className="px-2 group-data-[collapsible=icon]:px-2">
                    <div className="relative">
                        <Search className="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-[#7a90b0] group-data-[collapsible=icon]:hidden" />
                        <SidebarInput
                            placeholder="Search menu..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="border-[#132848] bg-[#0d1f3c] pl-8 text-[#f0f6ff] placeholder:text-[#7a90b0] group-data-[collapsible=icon]:hidden focus-visible:border-[#00c9a7] focus-visible:ring-[#00c9a7]/30"
                        />
                    </div>
                </div>
            </SidebarHeader>
            <SidebarContent className="bg-[#060e1e]">
                <NavMain items={allMenuItems()} searchQuery={searchQuery} />
            </SidebarContent>
        </Sidebar>
    )
}
