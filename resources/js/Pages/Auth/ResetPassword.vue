<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, Eye, EyeOff, Lock, Mail, ShieldCheck } from '@lucide/vue';
import { ref } from 'vue';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Kata Sandi" />

        <div class="mb-6">
            <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-400 shadow-inner">
                <Lock class="h-5 w-5" />
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">Buat Kata Sandi Baru</h1>
            <p class="mt-1 text-xs text-slate-400">Atur kata sandi baru untuk akun PadelHub kamu.</p>
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
                        class="w-full rounded-xl border border-slate-700/80 bg-slate-800/60 py-2.5 pr-4 pl-10 text-sm text-white placeholder-slate-500 transition-all focus:border-emerald-500 focus:bg-slate-800 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none"
                    />
                </div>
                <InputError class="mt-1.5 text-xs text-rose-400" :message="form.errors.email" />
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-xs font-semibold text-slate-300">
                    Kata Sandi Baru
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
                <InputError class="mt-1.5 text-xs text-rose-400" :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-xs font-semibold text-slate-300">
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
                <InputError class="mt-1.5 text-xs text-rose-400" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-400 hover:to-green-500 hover:shadow-emerald-500/35 active:scale-[0.98] disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Menyimpan Kata Sandi…' : 'Simpan Kata Sandi' }}</span>
                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
