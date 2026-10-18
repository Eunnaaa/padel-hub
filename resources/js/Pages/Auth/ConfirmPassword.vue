<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowRight, Eye, EyeOff, Lock, ShieldAlert } from '@lucide/vue';
import { ref } from 'vue';

const form = useForm({
    password: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Konfirmasi Kata Sandi" />

        <div class="mb-6">
            <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 shadow-inner">
                <ShieldAlert class="h-5 w-5" />
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">Area Aman</h1>
            <p class="mt-1 text-xs leading-relaxed text-slate-400">
                Ini adalah area sensitif aplikasi. Mohon konfirmasikan kata sandi kamu sebelum melanjutkan.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="password" class="mb-1.5 block text-xs font-semibold text-slate-300">
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
                        autocomplete="current-password"
                        autofocus
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

            <div class="pt-2">
                <button
                    type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-400 hover:to-green-500 hover:shadow-emerald-500/35 active:scale-[0.98] disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Memverifikasi…' : 'Konfirmasi Kata Sandi' }}</span>
                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
