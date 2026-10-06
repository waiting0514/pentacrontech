import { useUserStore } from '../store/modules/user';

export function usePermission() {
    const userStore = useUserStore();

    const can = (permission) => {
        if (!userStore.userInfo) return false;
        if (userStore.userInfo.role === 'super-admin') return true;
        return (userStore.userInfo.permissions || []).includes(permission);
    };

    const is = (role) => {
        if (!userStore.userInfo) return false;
        return userStore.userInfo.role === role;
    };

    return { can, is };
}
