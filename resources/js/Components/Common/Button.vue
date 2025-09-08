<script setup>
import { computed } from 'vue';

defineEmits(["click"]);

const props = defineProps({
  type: {
    type: String,
    default: "button",
  },
  variant: {
    type: String,
    validator: (prop) => ["primary", "secondary", "danger"].includes(prop),
    default: "primary",
  },
  size: {
    type: String,
    validator: (prop) => ["sm", "md", "lg"].includes(prop),
    default: "md",
  },
  fullWidth: {
    type: Boolean,
    default: false,
  },
});

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2 text-xs',
    lg: 'px-6 py-3 text-sm'
  };
  return sizes[props.size];
});
</script>

<template>
  <button
    :type="type"
    @click="$emit('click')"
    class="inline-flex items-center justify-center rounded-md font-semibold uppercase tracking-widest disabled:opacity-25 transition focus:outline-none focus:ring-2 focus:ring-offset-2"
    :class="[
      variant,
      sizeClasses,
      fullWidth ? 'w-full' : ''
    ]"
  >
    <slot />
  </button>
</template>

<style lang="postcss" scoped>
.primary {
  @apply bg-gray-800 border border-transparent text-white hover:bg-gray-700 active:bg-gray-900 focus:ring-gray-500;
}
.secondary {
  @apply bg-white border border-gray-300 text-gray-700 shadow-sm hover:text-gray-500 focus:ring-blue-500 active:text-gray-800 active:bg-gray-50;
}
.danger {
  @apply bg-red-800 border border-transparent text-white hover:bg-red-700 active:bg-red-900 focus:ring-red-500;
}
</style>
