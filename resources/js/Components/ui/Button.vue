<script setup lang="ts">
import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default: 'bg-gradient-to-r from-emerald-500 to-green-600 text-slate-950 font-bold shadow-md shadow-emerald-500/20 hover:from-emerald-400 hover:to-green-500',
                accent: 'bg-emerald-500 text-slate-950 font-bold shadow-md shadow-emerald-500/20 hover:bg-emerald-400',
                destructive: 'border border-rose-500/30 bg-rose-600/90 text-white shadow-sm hover:bg-rose-500',
                outline: 'border border-slate-700 bg-slate-800/60 text-slate-200 hover:bg-slate-700 hover:text-white',
                secondary: 'border border-slate-800 bg-slate-900 text-slate-200 hover:bg-slate-800',
                ghost: 'text-slate-300 hover:bg-slate-800 hover:text-white',
                link: 'text-emerald-400 underline-offset-4 hover:underline hover:text-emerald-300',
            },
            size: {
                default: 'h-10 rounded-xl px-4 py-2 text-xs font-bold',
                sm: 'h-8 rounded-lg px-3 text-xs font-semibold',
                lg: 'h-11 rounded-xl px-8 text-sm font-bold',
                icon: 'h-10 w-10 rounded-xl',
            },
        },
        defaultVariants: { variant: 'default', size: 'default' },
    },
);

type ButtonVariants = VariantProps<typeof buttonVariants>;

const props = withDefaults(
    defineProps<{
        variant?: ButtonVariants['variant'];
        size?: ButtonVariants['size'];
        as?: 'button' | 'link';
        href?: string;
        type?: 'button' | 'submit' | 'reset';
        class?: string;
    }>(),
    { variant: 'default', size: 'default', as: 'button', type: 'button' },
);

const classes = computed(() =>
    buttonVariants({ variant: props.variant, size: props.size, className: cn(props.class) }),
);
</script>

<template>
    <Link v-if="as === 'link'" :href="href ?? '#'" :class="classes">
        <slot />
    </Link>
    <button v-else :type="type" :class="classes">
        <slot />
    </button>
</template>
