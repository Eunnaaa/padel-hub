<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowRight,
    Eye,
    EyeOff,
    Lock,
    LogIn,
    Mail,
    Shield,
    User,
    Zap,
} from '@lucide/vue';
import { ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const fillDemo = (email: string) => {
    form.email = email;
    form.password = 'password';
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke Akun" />

        <div class="mb-6">
            <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-400 mb-3 shadow-inner">
                <LogIn class="h-5 w-5" />
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">Selamat Datang Kembali</h1>
            <p class="mt-1 text-xs text-slate-400">Masuk untuk mengelola jadwal main dan mengakses Match Pass digitalmu.</p>
        </div>

        <!-- Quick Demo Account Fillers -->
        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-950/60 p-3">
            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-400 mb-2">
                <span class="inline-flex items-center gap-1.5">
                    <Zap class="h-3.5 w-3.5 text-amber-400" />
                    <span>Akses Cepat Akun Demo:</span>
                </span>
                <span class="text-slate-500">1-Klik isi</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button
                    type="button"
                    @click="fillDemo('player@padel.test')"
                    class="flex items-center justify-center gap-1.5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1.5 text-xs font-semibold text-emerald-300 transition-all hover:bg-emerald-500/20 active:scale-95"
                >
                    <User class="h-3.5 w-3.5 text-emerald-400" />
                    <span>Akun Pemain</span>
                </button>
                <button
                    type="button"
                    @click="fillDemo('admin@padel.test')"
                    class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800/80 px-2.5 py-1.5 text-xs font-semibold text-slate-200 transition-all hover:bg-slate-700 active:scale-95"
                >
                    <Shield class="h-3.5 w-3.5 text-slate-400" />
                    <span>Akun Admin</span>
                </button>
            </div>
        </div>

        <div v-if="status" class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-xs font-medium text-emerald-300">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email Input -->
            <div>
                <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-300">
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
                        autofocus
                        autocomplete="username"
                        placeholder="nama@email.com"
                        class="w-full rounded-xl border border-slate-700/80 bg-slate-800/60 py-2.5 pr-4 pl-10 text-sm text-white placeholder-slate-500 transition-all focus:border-emerald-500 focus:bg-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    />
                </div>
                <InputError class="mt-1.5 text-xs text-rose-400" :message="form.errors.email" />
            </div>

            <!-- Password Input -->
            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="block text-xs font-semibold text-slate-300">
                        Kata Sandi
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-medium text-slate-400 transition hover:text-emerald-400"
                    >
                        Lupa password?
                    </Link>
                </div>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                        <Lock class="h-4 w-4" />
                    </div>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
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
                <InputError class="mt-1.5 text-xs text-rose-400" :message="form.errors.password" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center pt-1">
                <label class="flex cursor-pointer items-center gap-2">
                    <Checkbox
                        name="remember"
                        v-model:checked="form.remember"
                        class="h-4 w-4 rounded border-slate-700 bg-slate-800 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-900"
                    />
                    <span class="text-xs text-slate-400">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-400 hover:to-green-500 hover:shadow-emerald-500/35 active:scale-[0.98] disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Memproses Masuk…' : 'Masuk Sekarang' }}</span>
                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                </button>
            </div>

            <!-- Switch to Register -->
            <div class="pt-3 text-center">
                <p class="text-xs text-slate-400">
                    Belum punya akun pemain?
                    <Link
                        :href="route('register')"
                        class="ms-1 font-bold text-emerald-400 transition hover:text-emerald-300 hover:underline"
                    >
                        Daftar gratis di sini
                    </Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>
