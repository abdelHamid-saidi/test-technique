<script setup>
  defineProps({
    modelValue: String,
    label: String,
    name: {
      type: String,
      required: true,
    },
    required: {
      type: Boolean,
      default: false,
    },
    type: {
      type: String,
      default: 'datetime-local'
    }
  });
  
  defineEmits(["update:modelValue"]);
</script>

<template>
  <div>
    <label :for="name" class="font-medium text-sm text-gray-700">
      <span v-if="label">{{ label }}</span>
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    <input
      :name="name"
      ref="input"
      :type="type"
      :required="required"
      class="p-3 w-full outline-0 border    border-gray-300  focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm transition mt-1"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>
