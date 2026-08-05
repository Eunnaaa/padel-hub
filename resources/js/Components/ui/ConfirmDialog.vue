<script setup lang="ts">
import Button from '@/Components/ui/Button.vue';
import {
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogOverlay,
    AlertDialogPortal,
    AlertDialogRoot,
    AlertDialogTitle,
} from 'reka-ui';

defineProps<{
    open: boolean;
    title: string;
    description?: string;
    confirmLabel?: string;
    destructive?: boolean;
}>();

const emit = defineEmits<{ 'update:open': [value: boolean]; confirm: [] }>();
</script>

<template>
    <AlertDialogRoot :open="open" @update:open="emit('update:open', $event)">
        <AlertDialogPortal>
            <AlertDialogOverlay class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm" />
            <AlertDialogContent
                class="fixed left-1/2 top-1/2 z-50 w-[calc(100%-2rem)] max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-border bg-card p-6 shadow-lg"
            >
                <AlertDialogTitle class="text-lg font-bold text-foreground">{{ title }}</AlertDialogTitle>
                <AlertDialogDescription v-if="description" class="mt-2 text-sm text-muted-foreground">
                    {{ description }}
                </AlertDialogDescription>
                <slot />
                <div class="mt-6 flex justify-end gap-3">
                    <AlertDialogCancel as-child>
                        <Button variant="outline" @click="emit('update:open', false)">Batal</Button>
                    </AlertDialogCancel>
                    <AlertDialogAction as-child>
                        <Button :variant="destructive ? 'destructive' : 'default'" @click="emit('confirm')">
                            {{ confirmLabel ?? 'Konfirmasi' }}
                        </Button>
                    </AlertDialogAction>
                </div>
            </AlertDialogContent>
        </AlertDialogPortal>
    </AlertDialogRoot>
</template>
