import { ref } from 'vue';

const activeRequests = ref(0);
const isLoading = ref(false);

export function useLoading() {
    const startLoading = () => {
        activeRequests.value++;
        isLoading.value = true;
    };

    const stopLoading = () => {
        activeRequests.value = Math.max(0, activeRequests.value - 1);
        if (activeRequests.value === 0) {
            isLoading.value = false;
        }
    };

    const resetLoading = () => {
        activeRequests.value = 0;
        isLoading.value = false;
    };

    return {
        isLoading,
        activeRequests,
        startLoading,
        stopLoading,
        resetLoading,
    };
}
