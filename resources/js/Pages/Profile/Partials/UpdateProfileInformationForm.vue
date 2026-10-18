<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone ?? '',
    gender: user.gender ?? '',
    birth_date: user.birth_date ?? '',
    skill_level: user.skill_level ?? '',
    avatar: null as File | null,
});

const avatarPreview = ref<string | null>(user.avatar_url ?? null);

const onAvatar = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    form.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const hasAvatar = computed(() => avatarPreview.value !== null);

const submit = () => {
    form.patch(route('profile.update'), { forceFormData: true });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-black uppercase tracking-tight text-white">
                Informasi Profil
            </h2>

            <p class="mt-1 text-xs text-slate-400">
                Perbarui data profil, kontak WhatsApp, dan level permainan padel kamu.
            </p>
        </header>

        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <div class="flex items-center gap-5">
                <div class="relative flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 ring-2 ring-emerald-500/40">
                    <img v-if="hasAvatar" :src="avatarPreview ?? undefined" alt="Avatar" class="h-full w-full object-cover" />
                    <span v-else class="text-2xl font-black text-emerald-400">{{ user.name.charAt(0).toUpperCase() }}</span>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-slate-900 bg-emerald-400" />
                </div>
                <div>
                    <InputLabel for="avatar" value="Foto Profil / Avatar" />
                    <input
                        id="avatar"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="mt-1.5 block w-full text-xs text-slate-400 file:mr-3 file:rounded-xl file:border file:border-slate-700 file:bg-slate-800 file:px-3.5 file:py-2 file:text-xs file:font-bold file:text-emerald-400 hover:file:bg-slate-700"
                        @change="onAvatar"
                    />
                    <InputError class="mt-2" :message="form.errors.avatar" />
                </div>
            </div>

            <div>
                <InputLabel for="name" value="Nama Lengkap *" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1.5 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Alamat Email *" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="phone" value="Nomor WhatsApp" />

                <TextInput
                    id="phone"
                    type="tel"
                    class="mt-1.5 block w-full"
                    v-model="form.phone"
                    placeholder="08123456789"
                    autocomplete="tel"
                />

                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel for="gender" value="Jenis Kelamin" />

                    <select
                        id="gender"
                        v-model="form.gender"
                        class="mt-1.5 block h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                    >
                        <option value="">— Pilih —</option>
                        <option value="male">Laki-laki</option>
                        <option value="female">Perempuan</option>
                    </select>

                    <InputError class="mt-2" :message="form.errors.gender" />
                </div>

                <div>
                    <InputLabel for="birth_date" value="Tanggal Lahir" />

                    <TextInput
                        id="birth_date"
                        type="date"
                        class="mt-1.5 block w-full"
                        v-model="form.birth_date"
                        :max="new Date().toISOString().slice(0, 10)"
                    />

                    <InputError class="mt-2" :message="form.errors.birth_date" />
                </div>
            </div>

            <div>
                <InputLabel for="skill_level" value="Skill Level Padel" />

                <select
                    id="skill_level"
                    v-model="form.skill_level"
                    class="mt-1.5 block h-11 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-3 text-xs font-semibold text-white shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 [&>option]:bg-slate-900 [&>option]:text-white"
                >
                    <option value="">— Pilih —</option>
                    <option value="beginner">Beginner (Pemula)</option>
                    <option value="intermediate">Intermediate (Menengah)</option>
                    <option value="advanced">Advanced (Mahir)</option>
                    <option value="pro">Pro (Atlet / Kompetitif)</option>
                </select>

                <InputError class="mt-2" :message="form.errors.skill_level" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-xs text-amber-400">
                    Email kamu belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline hover:text-white"
                    >
                        Klik di sini untuk mengirim ulang link verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-bold text-emerald-400"
                >
                    Link verifikasi baru telah dikirimkan ke email kamu.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-2.5 text-xs font-bold text-slate-950 shadow-md shadow-emerald-500/20 transition hover:scale-105 disabled:opacity-50"
                >
                    {{ form.processing ? 'Menyimpan…' : 'Simpan Profil' }}
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs font-bold text-emerald-400"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
