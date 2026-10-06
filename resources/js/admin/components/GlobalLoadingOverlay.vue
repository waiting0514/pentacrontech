<template>
    <div v-if="store.isLoading" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity">
        <div class="flex flex-col items-center">
            <div class="h-12 w-12 animate-spin rounded-full border-4 border-white/20 border-t-white"></div>
        </div>
    </div>
</template>

<script setup>
import { useLoadingStore } from '../store/modules/loading';

const store = useLoadingStore();
</script>
