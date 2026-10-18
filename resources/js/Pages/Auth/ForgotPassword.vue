<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, KeyRound, Mail } from '@lucide/vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Kata Sandi" />

        <div class="mb-6">
            <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-400 shadow-inner">
                <KeyRound class="h-5 w-5" />
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">Reset Kata Sandi</h1>
            <p class="mt-1 text-xs leading-relaxed text-slate-400">
                Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan verifikasi untuk membuat kata sandi baru.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-xs font-medium text-emerald-300"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
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

            <div class="pt-2">
                <button
                    type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-400 hover:to-green-500 hover:shadow-emerald-500/35 active:scale-[0.98] disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Mengirim Tautan…' : 'Kirim Tautan Reset' }}</span>
                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                </button>
            </div>

            <div class="pt-3 text-center">
                <Link
                    :href="route('login')"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 transition hover:text-emerald-400"
                >
                    <ArrowLeft class="h-3.5 w-3.5" />
                    <span>Kembali ke halaman Masuk</span>
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
