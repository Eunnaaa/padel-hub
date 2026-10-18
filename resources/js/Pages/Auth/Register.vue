<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    Eye,
    EyeOff,
    Lock,
    Mail,
    Phone,
    ShieldCheck,
    Sparkles,
    User,
    UserPlus,
} from '@lucide/vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun Pemain" />

        <div class="mb-5">
            <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-400 mb-3 shadow-inner">
                <UserPlus class="h-5 w-5" />
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">Buat Akun Pemain</h1>
            <p class="mt-1 text-xs text-slate-400">Daftar gratis dalam 30 detik untuk mulai booking lapangan & match pass.</p>
        </div>

        <!-- Athletic Feature Badges Strip -->
        <div class="mb-5 flex items-center justify-between gap-1.5 rounded-xl border border-slate-800 bg-slate-950/60 px-3 py-2 text-[11px] font-medium text-slate-300">
            <span class="flex items-center gap-1 text-emerald-400">
                <Sparkles class="h-3.5 w-3.5" /> Booking Instan
            </span>
            <span class="text-slate-600">•</span>
            <span>🎟️ QR Match Pass</span>
            <span class="text-slate-600">•</span>
            <span>🏷️ Diskon Promo</span>
        </div>

        <form @submit.prevent="submit" class="space-y-3.5">
            <!-- Name Input -->
            <div>
                <label for="name" class="mb-1 block text-xs font-semibold text-slate-300">
                    Nama Lengkap
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                        <User class="h-4 w-4" />
                    </div>
                    <input
                        id="name"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Contoh: Budi Santoso"
                        class="w-full rounded-xl border border-slate-700/80 bg-slate-800/60 py-2.5 pr-4 pl-10 text-sm text-white placeholder-slate-500 transition-all focus:border-emerald-500 focus:bg-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    />
                </div>
                <InputError class="mt-1 text-xs text-rose-400" :message="form.errors.name" />
            </div>

            <!-- Email Input -->
            <div>
                <label for="email" class="mb-1 block text-xs font-semibold text-slate-300">
                    Alamat Email
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                        <Mail class="h-4 w-4" />
                    </div>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autocomplete="username"
                        placeholder="nama@email.com"
                        class="w-full rounded-xl border border-slate-700/80 bg-slate-800/60 py-2.5 pr-4 pl-10 text-sm text-white placeholder-slate-500 transition-all focus:border-emerald-500 focus:bg-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    />
                </div>
                <InputError class="mt-1 text-xs text-rose-400" :message="form.errors.email" />
            </div>

            <!-- Phone Input -->
            <div>
                <label for="phone" class="mb-1 block text-xs font-semibold text-slate-300">
                    Nomor WhatsApp / HP
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                        <Phone class="h-4 w-4" />
                    </div>
                    <input
                        id="phone"
                        type="tel"
                        v-model="form.phone"
                        required
                        autocomplete="tel"
                        placeholder="0812 3456 7890"
                        class="w-full rounded-xl border border-slate-700/80 bg-slate-800/60 py-2.5 pr-4 pl-10 text-sm text-white placeholder-slate-500 transition-all focus:border-emerald-500 focus:bg-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    />
                </div>
                <InputError class="mt-1 text-xs text-rose-400" :message="form.errors.phone" />
            </div>

            <!-- Password Input -->
            <div>
                <label for="password" class="mb-1 block text-xs font-semibold text-slate-300">
                    Kata Sandi
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                        <Lock class="h-4 w-4" />
                    </div>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                        class="w-full rounded-xl border border-slate-700/80 bg-slate-800/60 py-2.5 pr-10 pl-10 text-sm text-white placeholder-slate-500 transition-all focus:border-emerald-500 focus:bg-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 focus:outline-none"
                        tabindex="-1"
                    >
                        <EyeOff v-if="showPassword" class="h-4 w-4" />
                        <Eye v-else class="h-4 w-4" />
                    </button>
                </div>
                <InputError class="mt-1 text-xs text-rose-400" :message="form.errors.password" />
            </div>

            <!-- Password Confirmation Input -->
            <div>
                <label for="password_confirmation" class="mb-1 block text-xs font-semibold text-slate-300">
                    Konfirmasi Kata Sandi
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                        <ShieldCheck class="h-4 w-4" />
                    </div>
                    <input
                        id="password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi"
                        class="w-full rounded-xl border border-slate-700/80 bg-slate-800/60 py-2.5 pr-10 pl-10 text-sm text-white placeholder-slate-500 transition-all focus:border-emerald-500 focus:bg-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    />
                    <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 focus:outline-none"
                        tabindex="-1"
                    >
                        <EyeOff v-if="showConfirmPassword" class="h-4 w-4" />
                        <Eye v-else class="h-4 w-4" />
                    </button>
                </div>
                <InputError class="mt-1 text-xs text-rose-400" :message="form.errors.password_confirmation" />
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-400 hover:to-green-500 hover:shadow-emerald-500/35 active:scale-[0.98] disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Mendaftarkan Akun…' : 'Daftar Akun Sekarang' }}</span>
                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                </button>
            </div>

            <!-- Switch to Login -->
            <div class="pt-3 text-center">
                <p class="text-xs text-slate-400">
                    Sudah punya akun?
                    <Link
                        :href="route('login')"
                        class="ms-1 font-bold text-emerald-400 transition hover:text-emerald-300 hover:underline"
                    >
                        Masuk di sini
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
