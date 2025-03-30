<template>
  <footer class="bg-white border-t mt-auto py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center text-gray-500 text-sm">
        <p>&copy; {{ currentYear }} EventHub. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
</script>