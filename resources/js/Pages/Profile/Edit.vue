<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { User } from '@lucide/vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head title="Profil Saya — PadelHub" />

    <AuthenticatedLayout :dark="true">
        <template #header>
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-bold text-emerald-400">
                    <User class="h-3.5 w-3.5" />
                    <span>PROFIL & PREFERENSI</span>
                </div>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-white sm:text-3xl">Pengaturan Akun</h2>
                <p class="mt-1 text-sm text-slate-400">Kelola informasi identitas, avatar pemain, dan keamanan akun PadelHub kamu.</p>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-8 px-4 sm:px-6 lg:px-8">
                <!-- Profile Information Card -->
                <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                    />
                </div>

                <!-- Update Password Card -->
                <div class="rounded-3xl border border-slate-800/80 bg-slate-900/60 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                    <UpdatePasswordForm />
                </div>

                <!-- Delete Account Card -->
                <div class="rounded-3xl border border-rose-500/30 bg-rose-500/5 p-6 shadow-2xl backdrop-blur-md sm:p-8">
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
