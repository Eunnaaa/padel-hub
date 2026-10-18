<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, LogOut, MailCheck } from '@lucide/vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Verifikasi Email" />

        <div class="mb-6">
            <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-400 shadow-inner">
                <MailCheck class="h-5 w-5" />
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-white">Verifikasi Alamat Email</h1>
            <p class="mt-1 text-xs leading-relaxed text-slate-400">
                Terima kasih telah mendaftar di PadelHub! Silakan periksa kotak masuk emailmu dan klik tautan verifikasi yang kami kirimkan.
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-3 text-xs font-medium text-emerald-300"
        >
            Tautan verifikasi baru telah berhasil dikirimkan ke alamat email yang kamu daftarkan.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="pt-2">
                <button
                    type="submit"
                    class="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-emerald-500/25 transition-all hover:from-emerald-400 hover:to-green-500 hover:shadow-emerald-500/35 active:scale-[0.98] disabled:opacity-50"
                    :disabled="form.processing"
                >
                    <span>{{ form.processing ? 'Mengirim Ulang…' : 'Kirim Ulang Email Verifikasi' }}</span>
                    <ArrowRight class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                </button>
            </div>

            <div class="pt-3 text-center">
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 transition hover:text-rose-400"
                >
                    <LogOut class="h-3.5 w-3.5" />
                    <span>Keluar dari Akun</span>
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
