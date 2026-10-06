<script setup>
import { ref, computed, watch, onMounted, getCurrentInstance } from 'vue';
import { userService } from '../../../services';
import { ElMessage, ElMessageBox } from 'element-plus';
import { usePermission } from '../../composables/usePermission';

const { proxy } = getCurrentInstance();
const { is } = usePermission();

const users = ref([]);
const isLoading = ref(false);
const showDialog = ref(false);
const isEditing = ref(false);
const currentUser = ref(null);
const isSubmitting = ref(false);

const form = ref({
    username: '',
    name: '',
    email: '',
    password: '',
    role: 'editor',
});

const dirty = ref(false);

const fieldError = (field) => {
    if (!dirty.value) return '';
    if (!form.value[field]) return proxy.$t('admin.validation.required');
    if (field === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value[field])) {
        return proxy.$t('admin.validation.invalid_email');
    }
    return '';
};

const passwordError = computed(() => {
    if (!dirty.value) return '';
    const pwd = form.value.password;
    if (!pwd) return !isEditing.value ? proxy.$t('admin.validation.required') : '';
    if (pwd.length < 8) return proxy.$t('admin.validation.min_length', { min: 8 });
    return '';
});

const isFormValid = computed(() => {
    if (passwordError.value) return false;
    if (!form.value.username || !form.value.name || !form.value.email || !form.value.role) return false;
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) return false;
    if (!isEditing.value && !form.value.password) return false;
    return true;
});

watch(form, () => { dirty.value = true; }, { deep: true });

const fetchUsers = async () => {
    isLoading.value = true;
    try {
        const response = await userService.list();
        users.value = response.data.data || response.data;
    } catch {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    } finally {
        isLoading.value = false;
    }
};

const openCreate = () => {
    isEditing.value = false;
    currentUser.value = null;
    form.value = { username: '', name: '', email: '', password: '', role: 'editor' };
    dirty.value = false;
    showDialog.value = true;
};

const openEdit = (user) => {
    isEditing.value = true;
    currentUser.value = user;
    form.value = { username: user.username, name: user.name, email: user.email, password: '', role: user.role };
    dirty.value = false;
    showDialog.value = true;
};

const saveUser = async () => {
    isSubmitting.value = true;
    try {
        const data = { ...form.value };
        if (isEditing.value) {
            delete data.username;
            if (!data.password) delete data.password;
        }

        if (isEditing.value) {
            await userService.update(currentUser.value.id, data);
        } else {
            await userService.create(data);
        }
        showDialog.value = false;
        ElMessage.success(proxy.$t('admin.messages.save_success'));
        await fetchUsers();
    } catch (err) {
        if (err.response?.status === 422 && err.response?.data?.errors) {
            const firstError = Object.values(err.response.data.errors).flat()[0];
            if (firstError) ElMessage.error(firstError);
        } else if (err.response?.status !== 422) {
            ElMessage.error(err.response?.data?.message || proxy.$t('admin.messages.save_failed'));
        }
    } finally {
        isSubmitting.value = false;
    }
};

const toggleStatus = async (user) => {
    try {
        await userService.toggleStatus(user.id);
        ElMessage.success(user.is_active ? proxy.$t('admin.users.account_disabled') : proxy.$t('admin.users.account_enabled'));
        await fetchUsers();
    } catch (err) {
        ElMessage.error(err.response?.data?.message || proxy.$t('admin.messages.operation_failed'));
    }
};

const deleteUser = async (user) => {
    try {
        await ElMessageBox.confirm(proxy.$t('admin.messages.confirm_delete_user', { name: user.name }), proxy.$t('admin.messages.warning'), { type: 'warning' });
        await userService.destroy(user.id);
        ElMessage.success(proxy.$t('admin.messages.deleted'));
        await fetchUsers();
    } catch (err) {
        if (err !== 'cancel') ElMessage.error(err.response?.data?.message || proxy.$t('admin.messages.delete_failed'));
    }
};

const roleTag = (role) => {
    const map = { 'super-admin': 'danger', manager: 'warning', editor: 'info' };
    return map[role] || 'info';
};

const roleLabel = (role) => {
    const map = {
        'super-admin': proxy.$t('admin.users.role_super_admin'),
        manager: proxy.$t('admin.users.role_manager'),
        editor: proxy.$t('admin.users.role_editor'),
    };
    return map[role] || role;
};

onMounted(fetchUsers);
</script>

<template>
  <div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2>{{ $t('admin.users.title') }}</h2>
      <el-button type="primary" @click="openCreate">
        <el-icon><Plus /></el-icon> {{ $t('admin.users.create') }}
      </el-button>
    </div>

    <el-table :data="users" border stripe v-loading="isLoading">
      <el-table-column :label="$t('admin.users.username')" prop="username" />
      <el-table-column :label="$t('admin.users.name')" prop="name" />
      <el-table-column :label="$t('admin.users.email')" prop="email" />
      <el-table-column :label="$t('admin.users.role')" width="140" align="center">
        <template #default="{ row }">
          <el-tag :type="roleTag(row.role)" effect="dark">{{ roleLabel(row.role) }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.users.status')" width="120" align="center">
        <template #default="{ row }">
          <el-tag :type="row.is_active ? 'success' : 'danger'">
            {{ row.is_active ? $t('admin.users.active') : $t('admin.users.inactive') }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.users.last_login')" width="180">
        <template #default="{ row }">
          {{ row.last_login_at ? new Date(row.last_login_at).toLocaleString('zh-TW') : '-' }}
        </template>
      </el-table-column>
      <el-table-column :label="$t('admin.table.actions')" width="220" align="center">
        <template #default="{ row }">
          <el-button type="primary" link @click="openEdit(row)">{{ $t('admin.actions.edit') }}</el-button>
          <el-button v-if="row.username !== 'admin'" :type="row.is_active ? 'warning' : 'success'" link @click="toggleStatus(row)">
            {{ row.is_active ? $t('admin.users.disable') : $t('admin.users.enable') }}
          </el-button>
          <el-button v-if="is('super-admin') && row.username !== 'admin'" type="danger" link @click="deleteUser(row)">{{ $t('admin.actions.delete') }}</el-button>
        </template>
      </el-table-column>
    </el-table>

    <el-dialog v-model="showDialog" :title="isEditing ? $t('admin.users.edit') : $t('admin.users.create')" width="500px" destroy-on-close>
      <el-form :model="form" label-width="100px">
        <el-form-item :label="$t('admin.users.username')" required :error="fieldError('username')">
          <el-input v-model="form.username" :disabled="isEditing" />
        </el-form-item>
        <el-form-item :label="$t('admin.users.name')" required :error="fieldError('name')">
          <el-input v-model="form.name" />
        </el-form-item>
        <el-form-item :label="$t('admin.users.email')" required :error="fieldError('email')">
          <el-input v-model="form.email" type="email" />
        </el-form-item>
        <el-form-item :label="isEditing ? $t('admin.users.new_password') : $t('admin.users.password')" :required="!isEditing" :error="passwordError">
          <el-input v-model="form.password" type="password" show-password :placeholder="isEditing ? $t('admin.users.password_placeholder') : ''" />
        </el-form-item>
        <el-form-item :label="$t('admin.users.role')" required>
          <el-select v-model="form.role">
            <el-option :label="$t('admin.users.role_manager')" value="manager" />
            <el-option :label="$t('admin.users.role_editor')" value="editor" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showDialog = false">{{ $t('admin.actions.cancel') }}</el-button>
        <el-button type="primary" @click="saveUser" :loading="isSubmitting" :disabled="!isFormValid">{{ $t('admin.actions.save') }}</el-button>
      </template>
    </el-dialog>
  </div>
</template>
