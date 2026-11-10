<script setup>
import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { Close } from '@element-plus/icons-vue';
import { settingsService, uploadService } from '../../../services';
import { ElMessage } from 'element-plus';
import TranslatableInput from '../../components/TranslatableInput.vue';
import { useSiteStore } from '../../store/modules/site';
import { useLanguageStore } from '../../store/modules/language';

const { proxy } = getCurrentInstance();
const siteStore = useSiteStore();
const languageStore = useLanguageStore();
const languages = computed(() => languageStore.languages);

const isLoading = ref(false);
const logoUploading = ref(false);

const settings = ref({
    name: {}, yearly_budget_twd: 1000,
    logo_url: '',
    product_default_inquiry_mode: false,
    is_ai_enabled: true,
    meta_title: {}, meta_description: {}, meta_keywords: {},
    contact_phone: '', contact_email: '', contact_address: {},
    maintenance_mode: false, maintenance_message: {},
    // SMTP
    smtp_host: '', smtp_port: 587, smtp_encryption: 'tls',
    smtp_username: '', smtp_password: '',
    smtp_from_address: '', smtp_from_name: '',
    // CAPTCHA
    captcha_provider: 'none',
    recaptcha_site_key: '', recaptcha_secret_key: '',
    turnstile_site_key: '', turnstile_secret_key: '',
});

const hasSmtpPassword = ref(false);
const hasRecaptchaSecret = ref(false);
const hasTurnstileSecret = ref(false);
const smtpTesting = ref(false);

const ensureObject = (val) => {
    if (val && typeof val === 'object' && !Array.isArray(val)) return { ...val };
    if (typeof val === 'string') {
        try {
            const parsed = JSON.parse(val);
            return typeof parsed === 'object' ? parsed : {};
        } catch (e) {
            return { en: val };
        }
    }
    return {};
};

const fetchSettings = async () => {
    isLoading.value = true;
    try {
        const response = await settingsService.get();
        const data = response.data.data;
        settings.value = {
            name: ensureObject(data.name),
            logo_url: data.logo_url || '',
            yearly_budget_twd: parseFloat(data.yearly_budget_twd) || 1000,
            product_default_inquiry_mode: Boolean(data.product_default_inquiry_mode),
            is_ai_enabled: data.is_ai_enabled !== undefined ? Boolean(data.is_ai_enabled) : true,
            meta_title: ensureObject(data.meta_title),
            meta_description: ensureObject(data.meta_description),
            meta_keywords: ensureObject(data.meta_keywords),
            contact_phone: data.contact_phone || '',
            contact_email: data.contact_email || '',
            contact_address: ensureObject(data.contact_address),
            maintenance_mode: data.maintenance_mode || false,
            maintenance_message: ensureObject(data.maintenance_message),
            // SMTP
            smtp_host: data.smtp_host || '',
            smtp_port: data.smtp_port || 587,
            smtp_encryption: data.smtp_encryption || 'tls',
            smtp_username: data.smtp_username || '',
            smtp_password: '',
            smtp_from_address: data.smtp_from_address || '',
            smtp_from_name: data.smtp_from_name || '',
            // CAPTCHA
            captcha_provider: data.captcha_provider || 'none',
            recaptcha_site_key: data.recaptcha_site_key || '',
            recaptcha_secret_key: '',
            turnstile_site_key: data.turnstile_site_key || '',
            turnstile_secret_key: '',
        };
        hasSmtpPassword.value = Boolean(data.has_smtp_password);
        hasRecaptchaSecret.value = Boolean(data.has_recaptcha_secret_key);
        hasTurnstileSecret.value = Boolean(data.has_turnstile_secret_key);
    } catch (err) {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    } finally {
        isLoading.value = false;
    }
};

const handleLogoUpload = async (options) => {
    logoUploading.value = true;
    try {
        const formData = new FormData();
        formData.append('file', options.file);
        const res = await uploadService.uploadImage(formData);
        settings.value.logo_url = res.data.data.url;
        ElMessage.success('Logo 上傳成功');
    } catch (err) {
        ElMessage.error('Logo 上傳失敗');
    } finally {
        logoUploading.value = false;
    }
};

const handleLogoRemove = async () => {
    settings.value.logo_url = '';
};

// 密碼／金鑰留空代表不修改，不送出
const buildPayload = () => {
    const payload = { ...settings.value };
    ['smtp_password', 'recaptcha_secret_key', 'turnstile_secret_key'].forEach((key) => {
        if (!payload[key]) delete payload[key];
    });
    return payload;
};

const handleSave = async () => {
    isLoading.value = true;
    try {
        const payload = buildPayload();
        await settingsService.update(payload);
        siteStore.updateSettings(settings.value);
        ElMessage.success(proxy.$t('admin.messages.settings_saved'));
    } catch (err) {
        ElMessage.error(err.response?.data?.message || proxy.$t('admin.messages.save_failed'));
    } finally {
        isLoading.value = false;
    }
};

const handleTestSmtp = async () => {
    smtpTesting.value = true;
    try {
        // 先儲存設定再測試
        const payload = buildPayload();
        await settingsService.update(payload);
        siteStore.updateSettings(settings.value);
        // 測試寄信
        const res = await settingsService.testSmtp();
        ElMessage.success(res.data.message || '測試信已發送');
    } catch (err) {
        ElMessage.error(err.response?.data?.message || '測試寄信失敗');
    } finally {
        smtpTesting.value = false;
    }
};

onMounted(() => {
    languageStore.fetchLanguages();
    fetchSettings();
});
</script>

<template>
  <div v-loading="isLoading">
    <h2 style="margin-bottom: 20px;">{{ $t('admin.settings.title') }}</h2>

    <el-form :model="settings" label-width="130px" @submit.prevent="handleSave">
      <!-- Basic Settings -->
      <el-card :header="$t('admin.settings.basic')" style="margin-bottom: 20px;">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item :label="$t('admin.settings.site_name')">
              <TranslatableInput v-model="settings.name" :languages="languages" placeholder="" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item label="網站 Logo">
          <div style="display: flex; align-items: flex-start; gap: 16px;">
            <div v-if="settings.logo_url" style="position: relative;">
              <img :src="settings.logo_url" alt="Logo" style="height: 60px; width: auto; border: 1px solid #e4e7ed; border-radius: 4px; padding: 4px; background: #fff;" />
              <el-button
                type="danger" size="small" circle
                style="position: absolute; top: -8px; right: -8px; width: 20px; height: 20px; min-height: 20px;"
                @click="handleLogoRemove"
              >
                <el-icon style="font-size: 10px;"><Close /></el-icon>
              </el-button>
            </div>
            <el-upload
              :http-request="handleLogoUpload"
              :show-file-list="false"
              accept="image/*"
              :disabled="logoUploading"
            >
              <el-button :loading="logoUploading" plain>
                {{ settings.logo_url ? '更換 Logo' : '上傳 Logo' }}
              </el-button>
            </el-upload>
          </div>
          <div style="font-size: 12px; color: #909399; margin-top: 4px;">建議使用透明背景 PNG 或 SVG，高度至少 80px</div>
        </el-form-item>
        <el-form-item :label="$t('admin.settings.yearly_budget')">
          <el-input-number v-model="settings.yearly_budget_twd" :min="0" :precision="0" :step="100" />
          <div style="font-size: 12px; color: #909399; margin-top: 4px;">{{ $t('admin.settings.budget_hint') }}</div>
        </el-form-item>
      </el-card>

      <!-- Product Settings -->
      <el-card :header="$t('admin.settings.product_settings')" style="margin-bottom: 20px;">
        <el-form-item :label="$t('admin.settings.default_inquiry_mode')">
          <el-switch v-model="settings.product_default_inquiry_mode" :active-text="$t('admin.settings.enabled')" :inactive-text="$t('admin.settings.disabled')" />
          <div style="font-size: 12px; color: #909399; margin-top: 4px;">
            {{ $t('admin.settings.inquiry_mode_hint') }}
          </div>
        </el-form-item>
      </el-card>

      <!-- AI Settings -->
      <el-card :header="$t('admin.settings.ai_settings')" style="margin-bottom: 20px;">
        <el-form-item :label="$t('admin.settings.ai_enabled')">
          <el-switch v-model="settings.is_ai_enabled" :active-text="$t('admin.settings.enabled')" :inactive-text="$t('admin.settings.disabled')" />
          <div style="font-size: 12px; color: #909399; margin-top: 4px;">
            {{ $t('admin.settings.ai_enabled_hint') }}
          </div>
        </el-form-item>
      </el-card>

      <!-- SEO Settings -->
      <el-card :header="$t('admin.settings.seo')" style="margin-bottom: 20px;">
        <el-form-item :label="$t('admin.settings.meta_title')">
          <TranslatableInput v-model="settings.meta_title" :languages="languages" :placeholder="$t('admin.settings.meta_title_hint')" />
        </el-form-item>
        <el-form-item :label="$t('admin.settings.meta_description')">
          <TranslatableInput v-model="settings.meta_description" :languages="languages" type="textarea" :rows="3" :placeholder="$t('admin.settings.meta_description_hint')" />
        </el-form-item>
        <el-form-item :label="$t('admin.settings.meta_keywords')">
          <TranslatableInput v-model="settings.meta_keywords" :languages="languages" :placeholder="$t('admin.settings.meta_keywords_hint')" />
        </el-form-item>
      </el-card>

      <!-- Company Info -->
      <el-card :header="$t('admin.settings.company_info')" style="margin-bottom: 20px;">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item :label="$t('admin.settings.phone')">
              <el-input v-model="settings.contact_phone" placeholder="+886-2-1234-5678" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item :label="$t('admin.settings.email')">
              <el-input v-model="settings.contact_email" placeholder="info@example.com" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item :label="$t('admin.settings.address')">
          <TranslatableInput v-model="settings.contact_address" :languages="languages" type="textarea" :rows="2" placeholder="" />
        </el-form-item>
      </el-card>

      <!-- SMTP / Email Settings -->
      <el-card header="郵件設定 (SMTP)" style="margin-bottom: 20px;">
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="SMTP 主機">
              <el-input v-model="settings.smtp_host" placeholder="smtp.gmail.com" />
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="連接埠">
              <el-select v-model="settings.smtp_port" placeholder="選擇連接埠">
                <el-option :value="587" label="587 (TLS)" />
                <el-option :value="465" label="465 (SSL)" />
                <el-option :value="25" label="25" />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="加密方式">
              <el-select v-model="settings.smtp_encryption" placeholder="選擇加密方式">
                <el-option value="tls" label="TLS" />
                <el-option value="ssl" label="SSL" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="SMTP 帳號">
              <el-input v-model="settings.smtp_username" placeholder="your-email@gmail.com" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="應用程式密碼">
              <el-input
                v-model="settings.smtp_password"
                type="password"
                show-password
                :placeholder="hasSmtpPassword ? '已設定（留空不修改）' : '請輸入 Gmail 應用程式密碼'"
              />
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="寄件人地址">
              <el-input v-model="settings.smtp_from_address" placeholder="noreply@example.com" />
              <div style="font-size: 12px; color: #909399; margin-top: 4px;">留空則使用 SMTP 帳號作為寄件人</div>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="寄件人名稱">
              <el-input v-model="settings.smtp_from_name" placeholder="公司名稱" />
            </el-form-item>
          </el-col>
        </el-row>
        <el-form-item>
          <el-button type="warning" :loading="smtpTesting" @click="handleTestSmtp">
            發送測試信
          </el-button>
          <span style="font-size: 12px; color: #909399; margin-left: 8px;">測試信將發送至聯絡信箱（{{ settings.contact_email || '未設定' }}）</span>
        </el-form-item>
      </el-card>

      <!-- CAPTCHA -->
      <el-card header="驗證碼設定" style="margin-bottom: 20px;">
        <el-form-item label="驗證碼服務">
          <el-radio-group v-model="settings.captcha_provider">
            <el-radio value="none">不啟用</el-radio>
            <el-radio value="recaptcha">Google reCAPTCHA</el-radio>
            <el-radio value="turnstile">Cloudflare Turnstile</el-radio>
          </el-radio-group>
        </el-form-item>

        <!-- reCAPTCHA -->
        <template v-if="settings.captcha_provider === 'recaptcha'">
          <el-form-item label="Site Key">
            <el-input v-model="settings.recaptcha_site_key" placeholder="6Lxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" />
            <div style="font-size: 12px; color: #909399; margin-top: 4px;">從 <a href="https://www.google.com/recaptcha/admin" target="_blank" style="color: #409eff;">Google reCAPTCHA</a> 取得</div>
          </el-form-item>
          <el-form-item label="Secret Key">
            <el-input v-model="settings.recaptcha_secret_key" type="password" show-password :placeholder="hasRecaptchaSecret ? '已設定（留空不修改）' : '6Lxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'" />
          </el-form-item>
        </template>

        <!-- Turnstile -->
        <template v-if="settings.captcha_provider === 'turnstile'">
          <el-form-item label="Site Key">
            <el-input v-model="settings.turnstile_site_key" placeholder="0x4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" />
            <div style="font-size: 12px; color: #909399; margin-top: 4px;">從 <a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank" style="color: #409eff;">Cloudflare Dashboard</a> 取得</div>
          </el-form-item>
          <el-form-item label="Secret Key">
            <el-input v-model="settings.turnstile_secret_key" type="password" show-password :placeholder="hasTurnstileSecret ? '已設定（留空不修改）' : '0x4xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'" />
          </el-form-item>
        </template>
      </el-card>

      <!-- Maintenance -->
      <el-card :header="$t('admin.settings.maintenance')" style="margin-bottom: 20px;">
        <el-form-item :label="$t('admin.settings.maintenance_mode')">
          <el-switch v-model="settings.maintenance_mode" :active-text="$t('admin.settings.enabled')" :inactive-text="$t('admin.settings.disabled')" />
          <div style="font-size: 12px; margin-top: 4px;" :style="{ color: settings.maintenance_mode ? '#e6a23c' : '#909399' }">
            {{ settings.maintenance_mode ? $t('admin.settings.maintenance_enabled') : $t('admin.settings.maintenance_disabled') }}
          </div>
        </el-form-item>
        <el-form-item v-if="settings.maintenance_mode" :label="$t('admin.settings.maintenance_message')">
          <TranslatableInput v-model="settings.maintenance_message" :languages="languages" type="textarea" :rows="3" placeholder="" />
        </el-form-item>
      </el-card>

      <div style="text-align: right;">
        <el-button type="primary" size="large" @click="handleSave" :loading="isLoading">{{ $t('admin.settings.save_settings') }}</el-button>
      </div>
    </el-form>
  </div>
</template>
