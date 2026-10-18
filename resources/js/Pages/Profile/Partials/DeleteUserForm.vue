<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-base font-black uppercase tracking-tight text-rose-400">
                Hapus Akun Pengguna
            </h2>

            <p class="mt-1 text-xs text-slate-400">
                Setelah akun dihapus, seluruh data profil, riwayat reservasi, dan transaksi akan dihapus secara permanen.
            </p>
        </header>

        <button
            type="button"
            @click="confirmUserDeletion"
            class="inline-flex items-center gap-2 rounded-xl border border-rose-500/40 bg-rose-500/15 px-5 py-2.5 text-xs font-bold text-rose-300 transition hover:bg-rose-500/25 hover:text-white"
        >
            Hapus Akun Saya
        </button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6 sm:p-8">
                <h2 class="text-base font-black uppercase tracking-tight text-white">
                    Konfirmasi Hapus Akun?
                </h2>

                <p class="mt-2 text-xs text-slate-400">
                    Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi kamu untuk mengonfirmasi penghapusan permanen.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Kata Sandi Akun"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="Masukkan kata sandi kamu"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="closeModal"
                        class="rounded-xl border border-slate-700 bg-slate-800 px-4 py-2 text-xs font-semibold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                        class="rounded-xl border border-rose-500/40 bg-rose-600 px-5 py-2 text-xs font-bold text-white shadow-md shadow-rose-600/30 transition hover:bg-rose-500"
                    >
                        Ya, Hapus Akun
                    </button>
                </div>
            </div>
        </Modal>
    </section>
</template>
