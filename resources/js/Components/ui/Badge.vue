<script setup lang="ts">
import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import { computed } from 'vue';

const badgeVariants = cva(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary text-primary-foreground',
                accent: 'border-transparent bg-accent text-accent-foreground',
                secondary: 'border-transparent bg-secondary text-secondary-foreground',
                destructive: 'border-rose-500/30 bg-rose-500/15 text-rose-300',
                outline: 'border-slate-700 bg-slate-800/40 text-slate-300',
                success: 'border-emerald-500/30 bg-emerald-500/15 text-emerald-400',
                warning: 'border-amber-500/30 bg-amber-500/15 text-amber-400',
                info: 'border-sky-500/30 bg-sky-500/15 text-sky-400',
                muted: 'border-slate-800 bg-slate-800/60 text-slate-400',
            },
        },
        defaultVariants: { variant: 'default' },
    },
);

type BadgeVariants = VariantProps<typeof badgeVariants>;

const props = withDefaults(
    defineProps<{ variant?: BadgeVariants['variant']; class?: string }>(),
    { variant: 'default' },
);

const classes = computed(() => badgeVariants({ variant: props.variant, className: cn(props.class) }));
</script>

<template>
    <span :class="classes"><slot /></span>
</template>
