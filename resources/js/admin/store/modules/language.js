import { defineStore } from 'pinia';
import axios from 'axios';

export const useLanguageStore = defineStore('language', {
    state: () => ({
        languages: [],
        loaded: false,
    }),
    actions: {
        async fetchLanguages() {
            if (this.loaded) return;
            try {
                const response = await axios.get('/api/languages');
                this.languages = response.data.data;
                this.loaded = true;
            } catch (err) {
                console.error('Fetch languages error:', err);
            }
        },
    },
});
