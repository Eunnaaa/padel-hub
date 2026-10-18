<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="rounded-xl border-gray-300 shadow-sm focus:border-brand-accent focus:ring-brand-accent dark:border-slate-800 dark:bg-slate-950/80 dark:text-white dark:focus:border-emerald-500 dark:focus:ring-emerald-500"
        v-model="model"
        ref="input"
    />
</template>
