<script setup>
defineProps({
  data: {
    type: Array,
    default() {
      return [];
    },
  },
  headings: {
    type: Array,
    default() {
      return [];
    },
  },
  columnWidths: {
    type: Array,
    default() {
      return [];
    },
  },
});
</script>

<template>
  <!-- Version desktop -->
  <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-gray-500 shadow-lg rounded-lg overflow-hidden">
      <thead class="text-gray-700 uppercase bg-gradient-to-r from-blue-50 to-indigo-50">
        <tr>
          <th v-for="(heading, headingIndex) in headings" :key="`heading_${headingIndex}`" 
              :class="[
                'px-6 py-4 font-semibold tracking-wide border-b border-gray-200',
                headingIndex === headings.length - 1 ? 'text-center' : 'text-left',
                columnWidths[headingIndex] || 'w-auto'
              ]">
            {{heading}}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(row, rowIndex) in data"
          :key="`row_${rowIndex}`"
          class="border-b odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition-colors duration-200"
        >
          <slot name="row" :item="row" />
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Version mobile -->
  <div class="md:hidden space-y-4">
    <div
      v-for="(row, rowIndex) in data"
      :key="`mobile_row_${rowIndex}`"
      class="bg-white rounded-lg shadow-md p-4 border border-gray-200"
    >
      <slot name="mobile-row" :item="row" :headings="headings" />
    </div>
  </div>
</template>

<style scoped>
</style>
