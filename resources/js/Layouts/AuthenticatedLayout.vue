<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Toaster, toast } from 'vue-sonner';
import {
    Building2,
    CalendarCheck,
    Clock,
    CreditCard,
    Layers,
    LayoutDashboard,
    LogOut,
    MapPin,
    Menu,
    MoreVertical,
    Plus,
    QrCode,
    Shield,
    Sparkles,
    TrendingUp,
    User,
    X,
} from '@lucide/vue';

const props = withDefaults(
    defineProps<{
        dark?: boolean;
    }>(),
    {
        dark: true,
    },
);

const showingSidebar = ref(false);

const page = usePage();
watch(
    () => (page.props.flash as { success?: string; error?: string } | undefined),
    (flash) => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    },
);

interface NavItem {
    label: string;
    route: string;
    pattern: string;
    icon: any;
    badge?: string;
    badgeColor?: string;
}

interface NavGroup {
    title: string;
    items: NavItem[];
}

const navGroups = computed<NavGroup[]>(() => {
    const role = (page.props.auth.user as { role?: string } | null | undefined)?.role;

    if (role === 'admin') {
        return [
            {
                title: 'Overview & Sistem',
                items: [
                    { label: 'Dashboard Admin', route: 'admin.dashboard', pattern: 'admin.*', icon: Shield },
                    { label: 'Analytics Venue', route: 'venue-dashboard.index', pattern: 'venue-dashboard.*', icon: TrendingUp },
                ],
            },
            {
                title: 'Manajemen Fasilitas',
                items: [
                    { label: 'Manajemen Venue', route: 'venues.index', pattern: 'venues.*', icon: Building2 },
                    { label: 'Manajemen Lapangan', route: 'courts.index', pattern: 'courts.*', icon: Layers },
                    { label: 'Monitoring Slot', route: 'availability.index', pattern: 'availability.*', icon: Clock, badge: 'Live', badgeColor: 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' },
                ],
            },
            {
                title: 'Operasional & Transaksi',
                items: [
                    { label: 'Check-in Pemain', route: 'checkin.index', pattern: 'checkin.*', icon: QrCode, badge: 'QR', badgeColor: 'bg-sky-500/20 text-sky-400 border border-sky-500/30' },
                    { label: 'Monitoring Booking', route: 'bookings.index', pattern: 'bookings.*', icon: CalendarCheck },
                    { label: 'Laporan Pembayaran', route: 'payments.index', pattern: 'payments.*', icon: CreditCard },
                ],
            },
        ];
    }

    if (role === 'venue_owner') {
        return [
            {
                title: 'Ringkasan Venue',
                items: [
                    { label: 'Dashboard & Analytics', route: 'venue-dashboard.index', pattern: 'venue-dashboard.*', icon: LayoutDashboard },
                ],
            },
            {
                title: 'Fasilitas & Lapangan',
                items: [
                    { label: 'Venue Saya', route: 'venues.index', pattern: 'venues.*', icon: Building2 },
                    { label: 'Lapangan Saya', route: 'courts.index', pattern: 'courts.*', icon: Layers },
                    { label: 'Monitoring Slot', route: 'availability.index', pattern: 'availability.*', icon: Clock, badge: 'Live', badgeColor: 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' },
                ],
            },
            {
                title: 'Operasional Pemain',
                items: [
                    { label: 'Check-in Pemain', route: 'checkin.index', pattern: 'checkin.*', icon: QrCode, badge: 'QR', badgeColor: 'bg-sky-500/20 text-sky-400 border border-sky-500/30' },
                    { label: 'Daftar Booking', route: 'bookings.index', pattern: 'bookings.*', icon: CalendarCheck },
                    { label: 'Riwayat Pembayaran', route: 'payments.index', pattern: 'payments.*', icon: CreditCard },
                ],
            },
        ];
    }

    return [
        {
            title: 'Menu Utama',
            items: [
                { label: 'Dashboard', route: 'dashboard', pattern: 'dashboard', icon: LayoutDashboard },
                { label: 'Cari Venue', route: 'venues.explore', pattern: 'venues.*', icon: MapPin },
            ],
        },
        {
            title: 'Reservasi & Jadwal',
            items: [
                { label: 'Ketersediaan Slot', route: 'availability.index', pattern: 'availability.*', icon: Clock, badge: 'Live', badgeColor: 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' },
                { label: 'Bookings Saya', route: 'bookings.index', pattern: 'bookings.*', icon: CalendarCheck },
            ],
        },
        {
            title: 'Finansial & Tagihan',
            items: [
                { label: 'Riwayat Pembayaran', route: 'payments.index', pattern: 'payments.*', icon: CreditCard },
            ],
        },
    ];
});
</script>

<template>
    <div
        class="min-h-screen transition-colors duration-200"
        :class="dark ? 'dark bg-slate-950 text-white selection:bg-brand-accent/25 selection:text-white' : 'bg-background text-foreground'"
    >
        <Toaster position="top-right" richColors closeButton />

        <!-- Desktop sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-slate-800/80 bg-slate-950 font-sans text-slate-100 shadow-2xl lg:flex"
        >
            <!-- Ambient Dot Grid in Sidebar -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.04]"
                style="background-image: radial-gradient(#22C55E 1px, transparent 1px); background-size: 24px 24px"
            />

            <!-- Sidebar Header Brand -->
            <div class="relative z-10 flex h-16 items-center border-b border-slate-800/80 px-6">
                <Link href="/" class="group inline-flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-green-600 p-2 shadow-lg shadow-emerald-500/20 transition-transform group-hover:scale-105">
                        <ApplicationLogo class="h-full w-full text-slate-950" />
                    </div>
                    <div>
                        <span class="text-lg font-black tracking-tight text-white">Padel<span class="text-emerald-400">Hub</span></span>
                    </div>
                </Link>
            </div>

            <!-- Quick Action CTA Button -->
            <div class="relative z-10 px-3.5 pt-3.5 pb-1">
                <Link
                    v-if="$page.props.auth.user.role === 'admin'"
                    :href="route('venues.create')"
                    class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-3.5 py-2.5 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] hover:shadow-emerald-500/30 active:scale-[0.98]"
                >
                    <Plus class="h-4 w-4 transition-transform group-hover:rotate-90" />
                    <span>Tambah Venue Baru</span>
                </Link>
                <Link
                    v-else-if="$page.props.auth.user.role === 'venue_owner'"
                    :href="route('courts.create')"
                    class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-3.5 py-2.5 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] hover:shadow-emerald-500/30 active:scale-[0.98]"
                >
                    <Plus class="h-4 w-4 transition-transform group-hover:rotate-90" />
                    <span>Tambah Lapangan</span>
                </Link>
                <Link
                    v-else
                    :href="route('bookings.create')"
                    class="group relative flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-3.5 py-2.5 text-xs font-bold text-slate-950 shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] hover:shadow-emerald-500/30 active:scale-[0.98]"
                >
                    <Plus class="h-4 w-4 transition-transform group-hover:rotate-90" />
                    <span>Booking Lapangan</span>
                </Link>
            </div>

            <!-- Sidebar Nav List -->
            <nav class="relative z-10 flex-1 space-y-4 overflow-y-auto px-3.5 py-3">
                <div v-for="(group, gIdx) in navGroups" :key="gIdx" class="space-y-1">
                    <p class="px-3 text-[10px] font-black uppercase tracking-wider text-slate-300">
                        {{ group.title }}
                    </p>
                    <div class="space-y-1">
                        <Link
                            v-for="item in group.items"
                            :key="item.route"
                            :href="route(item.route)"
                            class="group relative flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold tracking-wide transition-all"
                            :class="route().current(item.pattern)
                                ? 'border border-emerald-500/30 bg-emerald-500/15 text-emerald-300 shadow-sm shadow-emerald-950/40'
                                : 'border border-transparent text-slate-400 hover:border-slate-800 hover:bg-slate-900/80 hover:text-white'"
                        >
                            <!-- Left Neon Glow Indicator on active item -->
                            <span
                                v-if="route().current(item.pattern)"
                                class="absolute -left-1 top-1/2 h-5 w-1.5 -translate-y-1/2 rounded-r-full bg-emerald-400 shadow-[0_0_10px_#22c55e]"
                            />

                            <div class="flex items-center gap-2.5">
                                <component
                                    :is="item.icon"
                                    class="h-4 w-4 shrink-0 transition-transform group-hover:scale-110"
                                    :class="route().current(item.pattern) ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400'"
                                />
                                <span>{{ item.label }}</span>
                            </div>

                            <span
                                v-if="item.badge"
                                class="rounded px-1.5 py-0.5 text-[9.5px] font-bold"
                                :class="item.badgeColor ?? 'bg-slate-800 text-slate-300'"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </div>
                </div>

                <!-- Community Promo Perk Widget -->
                <div
                    v-if="$page.props.auth.user.role === 'player' || !$page.props.auth.user.role"
                    class="rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/10 via-slate-900/60 to-slate-950 p-3 shadow-lg backdrop-blur-md"
                >
                    <div class="flex items-center gap-1.5 text-emerald-400">
                        <Sparkles class="h-3.5 w-3.5" />
                        <span class="text-[10px] font-black uppercase tracking-wider">Promo Member</span>
                    </div>
                    <p class="mt-1 text-xs font-bold text-white">Diskon 20% Pemain Baru</p>
                    <p class="mt-0.5 text-[10px] text-slate-400">Pakai voucher saat checkout:</p>
                    <div class="mt-2 flex items-center justify-between rounded-lg border border-dashed border-emerald-500/40 bg-emerald-500/10 px-2.5 py-1">
                        <span class="font-mono text-[11px] font-black tracking-wider text-emerald-300">PADELBARU</span>
                        <span class="rounded bg-emerald-500 px-1 py-0.5 text-[8.5px] font-black text-slate-950">20% OFF</span>
                    </div>
                </div>
            </nav>

            <!-- Bottom User Profile Card & Dropdown -->
            <div class="relative z-10 border-t border-slate-800/80 bg-slate-950/60 p-3.5">
                <div class="flex items-center justify-between gap-2.5 rounded-2xl border border-slate-800/80 bg-slate-900/70 p-2.5 backdrop-blur-md">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <!-- User Avatar with Neon Ring & Online Beacon -->
                        <div class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-emerald-400 to-green-600 font-black text-slate-950 shadow-md shadow-emerald-500/20 ring-1 ring-emerald-400/40">
                            <img
                                v-if="$page.props.auth.user.avatar_url"
                                :src="$page.props.auth.user.avatar_url"
                                alt="Avatar"
                                class="h-full w-full object-cover"
                            />
                            <span v-else class="text-sm font-black">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-slate-950 bg-emerald-400" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-white">{{ $page.props.auth.user.name }}</p>
                            <div class="mt-0.5 flex items-center gap-1">
                                <span
                                    v-if="$page.props.auth.user.role === 'admin'"
                                    class="inline-flex items-center rounded-md border border-amber-500/30 bg-amber-500/10 px-1.5 py-0.5 text-[9.5px] font-bold text-amber-400"
                                >
                                    🛡️ Admin
                                </span>
                                <span
                                    v-else-if="$page.props.auth.user.role === 'venue_owner'"
                                    class="inline-flex items-center rounded-md border border-blue-500/30 bg-blue-500/10 px-1.5 py-0.5 text-[9.5px] font-bold text-blue-400"
                                >
                                    🏟️ Owner
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-md border border-emerald-500/30 bg-emerald-500/10 px-1.5 py-0.5 text-[9.5px] font-bold text-emerald-400"
                                >
                                    🎾 Pemain
                                </span>
                            </div>
                        </div>
                    </div>

                    <Dropdown align="right" width="48" open-up content-classes="p-1.5 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl backdrop-blur-xl text-slate-200">
                        <template #trigger>
                            <button
                                type="button"
                                aria-label="User menu"
                                class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition-all hover:bg-slate-800 hover:text-white focus:outline-none"
                            >
                                <MoreVertical class="h-4 w-4" />
                            </button>
                        </template>
                        <template #content>
                            <div class="mb-1 border-b border-slate-800/80 px-3 py-2">
                                <p class="truncate text-[11px] font-bold text-white">{{ $page.props.auth.user.name }}</p>
                                <p class="truncate text-[10px] text-slate-400">{{ $page.props.auth.user.email }}</p>
                            </div>
                            <Link
                                :href="route('profile.edit')"
                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 transition hover:bg-emerald-500/15 hover:text-emerald-300"
                            >
                                <User class="h-3.5 w-3.5 text-emerald-400" />
                                <span>Profil Saya</span>
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-xs font-semibold text-rose-300 transition hover:bg-rose-500/15 hover:text-rose-200"
                            >
                                <LogOut class="h-3.5 w-3.5 text-rose-400" />
                                <span>Keluar Akun</span>
                            </Link>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </aside>

        <!-- Mobile topbar -->
        <div class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-800/80 bg-slate-950 px-4 text-white lg:hidden">
            <Link href="/" class="inline-flex items-center gap-2.5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-green-600 p-1.5 text-slate-950 shadow-md">
                    <ApplicationLogo class="h-full w-full" />
                </div>
                <span class="font-extrabold tracking-tight">Padel<span class="text-emerald-400">Hub</span></span>
            </Link>
            <button
                type="button"
                aria-label="Open sidebar menu"
                @click="showingSidebar = true"
                class="rounded-xl border border-slate-800 bg-slate-900 p-2 text-slate-300 hover:bg-slate-800 hover:text-white"
            >
                <Menu class="h-5 w-5" />
            </button>
        </div>

        <!-- Mobile sidebar -->
        <div
            v-if="showingSidebar"
            class="fixed inset-0 z-50 lg:hidden"
        >
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity" @click="showingSidebar = false" />
            <div class="absolute inset-y-0 left-0 flex w-72 flex-col border-r border-slate-800 bg-slate-950 text-white shadow-2xl">
                <div class="flex h-16 items-center justify-between border-b border-slate-800 px-6">
                    <div class="inline-flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-green-600 p-1.5 text-slate-950 shadow-md">
                            <ApplicationLogo class="h-full w-full" />
                        </div>
                        <span class="text-lg font-black tracking-tight">Padel<span class="text-emerald-400">Hub</span></span>
                    </div>
                    <button
                        type="button"
                        aria-label="Close sidebar menu"
                        @click="showingSidebar = false"
                        class="rounded-xl border border-slate-800 bg-slate-900 p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
                <!-- Quick CTA Mobile -->
                <div class="px-3.5 pt-3 pb-1">
                    <Link
                        v-if="$page.props.auth.user.role === 'admin'"
                        :href="route('venues.create')"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-3.5 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20"
                        @click="showingSidebar = false"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Tambah Venue Baru</span>
                    </Link>
                    <Link
                        v-else-if="$page.props.auth.user.role === 'venue_owner'"
                        :href="route('courts.create')"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-3.5 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20"
                        @click="showingSidebar = false"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Tambah Lapangan</span>
                    </Link>
                    <Link
                        v-else
                        :href="route('bookings.create')"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-3.5 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20"
                        @click="showingSidebar = false"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Booking Lapangan</span>
                    </Link>
                </div>

                <nav class="flex-1 space-y-4 overflow-y-auto px-3.5 py-3">
                    <div v-for="(group, gIdx) in navGroups" :key="gIdx" class="space-y-1">
                        <p class="px-3 text-[10px] font-black uppercase tracking-wider text-slate-400">
                            {{ group.title }}
                        </p>
                        <div class="space-y-1">
                            <Link
                                v-for="item in group.items"
                                :key="item.route"
                                :href="route(item.route)"
                                class="group relative flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold tracking-wide transition-all"
                                :class="route().current(item.pattern)
                                    ? 'border border-emerald-500/30 bg-emerald-500/15 text-emerald-300'
                                    : 'border border-transparent text-slate-400 hover:border-slate-800 hover:bg-slate-900 hover:text-white'"
                                @click="showingSidebar = false"
                            >
                                <div class="flex items-center gap-2.5">
                                    <component
                                        :is="item.icon"
                                        class="h-4 w-4 shrink-0"
                                        :class="route().current(item.pattern) ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400'"
                                    />
                                    <span>{{ item.label }}</span>
                                </div>
                                <span
                                    v-if="item.badge"
                                    class="rounded px-1.5 py-0.5 text-[9.5px] font-bold"
                                    :class="item.badgeColor ?? 'bg-slate-800 text-slate-300'"
                                >
                                    {{ item.badge }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </nav>
                <div class="border-t border-slate-800/80 p-4">
                    <div class="flex items-center gap-3">
                        <div class="relative flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-emerald-400 to-green-600 font-black text-slate-950 shadow-md shadow-emerald-500/20">
                            <img
                                v-if="$page.props.auth.user.avatar_url"
                                :src="$page.props.auth.user.avatar_url"
                                alt="Avatar"
                                class="h-full w-full object-cover"
                            />
                            <span v-else class="text-sm font-black">{{ $page.props.auth.user.name.charAt(0).toUpperCase() }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-bold text-white">{{ $page.props.auth.user.name }}</p>
                            <p class="truncate text-[11px] text-slate-400">{{ $page.props.auth.user.email }}</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-center text-xs">
                        <Link
                            :href="route('profile.edit')"
                            class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900 px-3 py-2 font-semibold text-slate-200 transition hover:bg-slate-800 hover:text-white"
                            @click="showingSidebar = false"
                        >
                            <User class="h-3.5 w-3.5 text-emerald-400" />
                            <span>Profil</span>
                        </Link>
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex items-center justify-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 font-semibold text-rose-300 transition hover:bg-rose-500/20 hover:text-white"
                            @click="showingSidebar = false"
                        >
                            <LogOut class="h-3.5 w-3.5 text-rose-400" />
                            <span>Keluar</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Wrapper -->
        <div class="lg:pl-64">
            <header
                v-if="$slots.header"
                class="border-b transition-colors"
                :class="dark ? 'border-slate-800/80 bg-slate-950/80 backdrop-blur-md text-white' : 'border-brand-border bg-white text-foreground'"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="relative z-10">
                <div
                    v-if="dark"
                    class="pointer-events-none fixed inset-0 opacity-[0.06]"
                    style="background-image: radial-gradient(#22C55E 1px, transparent 1px); background-size: 28px 28px"
                />
                <slot />
            </main>
        </div>
    </div>
</template>
