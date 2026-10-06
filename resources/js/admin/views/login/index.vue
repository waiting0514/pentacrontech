<script setup>
import { ref, reactive, getCurrentInstance } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '../../store/modules/user';
import { ElMessage } from 'element-plus';

const { proxy } = getCurrentInstance();
const router = useRouter();
const userStore = useUserStore();
const loading = ref(false);
const loginFormRef = ref(null);

const loginForm = reactive({
  username: '',
  password: '',
});

const rules = {
  username: [
    { required: true, message: () => proxy.$t('admin.auth.username_required'), trigger: 'blur' },
  ],
  password: [
    { required: true, message: () => proxy.$t('admin.auth.password_required'), trigger: 'blur' },
  ],
};

const handleLogin = async () => {
  const valid = await loginFormRef.value.validate().catch(() => false);
  if (!valid) return;

  loading.value = true;
  try {
    await userStore.login({
      username: loginForm.username,
      password: loginForm.password,
    });
    router.push('/admin');
  } catch (err) {
    ElMessage.error(err.response?.data?.message || proxy.$t('admin.auth.login_failed'));
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="logo-icon">
          <el-icon :size="40" color="#fff">
            <MagicStick />
          </el-icon>
        </div>
        <h1>{{ $t('admin.auth.title') }}</h1>
        <p>{{ $t('admin.auth.subtitle') }}</p>
      </div>

      <el-form ref="loginFormRef" :model="loginForm" :rules="rules" size="large" @keyup.enter="handleLogin">
        <el-form-item prop="username">
          <el-input v-model="loginForm.username" :placeholder="$t('admin.auth.username')" :prefix-icon="UserIcon" />
        </el-form-item>

        <el-form-item prop="password">
          <el-input v-model="loginForm.password" type="password" :placeholder="$t('admin.auth.password')" :prefix-icon="Lock" show-password />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" :loading="loading" class="login-btn" @click="handleLogin">
            {{ loading ? $t('admin.auth.logging_in') : $t('admin.auth.login') }}
          </el-button>
        </el-form-item>
      </el-form>

      <div class="login-footer">
        <el-text type="info" size="small">{{ $t('admin.auth.admin_only') }}</el-text>
      </div>
    </div>
  </div>
</template>

<script>
import { User as UserIcon, Lock } from '@element-plus/icons-vue';
export default {
  setup() {
    return { UserIcon, Lock };
  },
};
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-card {
  width: 400px;
  padding: 40px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.login-header {
  text-align: center;
  margin-bottom: 32px;
}

.logo-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: linear-gradient(135deg, #409eff, #7c3aed);
  margin-bottom: 16px;
}

.login-header h1 {
  font-size: 24px;
  font-weight: 700;
  color: #303133;
  margin: 0 0 8px;
}

.login-header p {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.login-btn {
  width: 100%;
}

.login-footer {
  text-align: center;
  margin-top: 16px;
}
</style>
