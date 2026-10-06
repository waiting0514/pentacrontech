import { defineStore } from 'pinia';
import { projectService } from '../../../services';

export const useProjectStore = defineStore('project', {
    state: () => ({
        currentProject: null,
        isLoading: false,
    }),
    getters: {
        isAiEnabled: (state) => state.currentProject?.is_ai_enabled ?? true,
    },
    actions: {
        async fetchCurrentProject() {
            this.isLoading = true;
            try {
                const response = await projectService.current();
                this.currentProject = response.data.data;
                return this.currentProject;
            } catch (error) {
                console.error('Failed to fetch project:', error);
                throw error;
            } finally {
                this.isLoading = false;
            }
        },
        updateProject(data) {
            if (this.currentProject) {
                this.currentProject = { ...this.currentProject, ...data };
            }
        }
    }
});
