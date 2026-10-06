<template>
  <div class="translatable-input">
    <div v-if="label" class="mb-2 text-sm font-medium text-gray-700">{{ label }}</div>
    
    <!-- 多語系模式 -->
    <el-tabs v-if="activeLanguages.length > 1" type="border-card" v-model="activeTab">
      <el-tab-pane
        v-for="lang in activeLanguages"
        :key="lang.code"
        :name="lang.code"
      >
        <template #label>
          <span class="tab-label">
            {{ lang.name }}
            <span
              class="status-dot"
              :class="{ filled: hasContent(lang.code) }"
              :title="hasContent(lang.code) ? '已填寫' : '未填寫'"
            >{{ hasContent(lang.code) ? '●' : '○' }}</span>
          </span>
        </template>
        <el-input
          v-model="internalValue[lang.code]"
          :placeholder="placeholder"
          @input="handleInput"
          :type="type"
          :rows="rows"
          :maxlength="maxlength"
          :show-word-limit="!!maxlength"
        />
      </el-tab-pane>
    </el-tabs>

    <!-- 單語系模式 -->
    <div v-else-if="activeLanguages.length === 1">
      <el-input
        v-model="internalValue[activeLanguages[0].code]"
        :placeholder="placeholder"
        @input="handleInput"
        :type="type"
        :rows="rows"
        :maxlength="maxlength"
        :show-word-limit="!!maxlength"
      />
    </div>

    <div v-else>
      <el-skeleton animated :rows="1" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue';

const props = defineProps({
  modelValue: {
    type: [Object, String],
    default: () => ({})
  },
  label: String,
  placeholder: String,
  type: {
    type: String,
    default: 'text'
  },
  rows: {
    type: Number,
    default: 2
  },
  maxlength: {
    type: [Number, String],
    default: null
  },
  languages: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue']);

const activeTab = ref('');
const internalValue = ref({});

const activeLanguages = computed(() => {
  return props.languages.filter(l => l.is_active);
});

const parseValue = (val) => {
  if (!val) return {};
  if (typeof val === 'object' && !Array.isArray(val)) {
    // 確保每個值都是字串，避免 [object Object]
    const result = {};
    for (const [key, v] of Object.entries(val)) {
      result[key] = (typeof v === 'string') ? v : '';
    }
    return result;
  }
  if (typeof val === 'string') {
    try {
      const p = JSON.parse(val);
      if (p && typeof p === 'object') {
        // 遞迴確保值是字串
        const result = {};
        for (const [key, v] of Object.entries(p)) {
          result[key] = (typeof v === 'string') ? v : '';
        }
        return result;
      }
      return {};
    } catch (e) {
      // 處理純字串情況，放入預設語言
      const def = activeLanguages.value.find(l => l.is_default) || activeLanguages.value[0];
      return def ? { [def.code]: val } : {};
    }
  }
  return {};
};

const initData = () => {
  const parsed = parseValue(props.modelValue);
  const newValue = {};
  
  activeLanguages.value.forEach(lang => {
    newValue[lang.code] = parsed[lang.code] || '';
  });
  
  internalValue.value = newValue;

  if (!activeTab.value && activeLanguages.value.length > 0) {
    const def = activeLanguages.value.find(l => l.is_default) || activeLanguages.value[0];
    activeTab.value = def.code;
  }
};

onMounted(() => {
  initData();
});

watch(() => props.languages, () => {
  // 語言變更時重新初始化
  nextTick(() => initData());
}, { deep: true });

watch(() => props.modelValue, (newVal, oldVal) => {
  // 當 modelValue 變更時重新初始化（避免與 internalValue 比較導致的問題）
  if (JSON.stringify(newVal) !== JSON.stringify(oldVal)) {
    nextTick(() => initData());
  }
}, { deep: true });

const handleInput = () => {
  emit('update:modelValue', { ...internalValue.value });
};

const hasContent = (langCode) => {
  const val = internalValue.value[langCode];
  return val && val.trim().length > 0;
};
</script>

<style scoped>
.tab-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.status-dot {
  font-size: 10px;
  color: #c0c4cc;
}
.status-dot.filled {
  color: #67c23a;
}
</style>