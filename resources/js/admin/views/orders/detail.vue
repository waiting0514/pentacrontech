<script setup>
import { ref, onMounted, computed, getCurrentInstance } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { orderService } from '../../../services';
import { ElMessage } from 'element-plus';

const { proxy } = getCurrentInstance();
const route = useRoute();
const router = useRouter();

const order = ref(null);
const isLoading = ref(false);
const isUpdating = ref(false);
const selectedStatus = ref('');

const statusOptions = [
    { value: 'pending', label: () => proxy.$t('admin.orders.status_pending') },
    { value: 'processing', label: () => proxy.$t('admin.orders.status_processing') },
    { value: 'completed', label: () => proxy.$t('admin.orders.status_completed') },
    { value: 'cancelled', label: () => proxy.$t('admin.orders.status_cancelled') },
];

const statusTagType = {
    pending: 'warning',
    processing: 'primary',
    completed: 'success',
    cancelled: 'info',
};

const total = computed(() => {
    if (!order.value?.order_items) return 0;
    return order.value.order_items.reduce((sum, item) => sum + Number(item.price) * item.quantity, 0);
});

const fetchOrder = async () => {
    isLoading.value = true;
    try {
        const res = await orderService.get(route.params.id);
        order.value = res.data.data;
        selectedStatus.value = order.value.status;
    } catch {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    } finally {
        isLoading.value = false;
    }
};

const updateStatus = async () => {
    if (selectedStatus.value === order.value.status) return;
    isUpdating.value = true;
    try {
        await orderService.updateStatus(order.value.id, selectedStatus.value);
        order.value.status = selectedStatus.value;
        ElMessage.success(proxy.$t('admin.messages.update_success'));
    } catch {
        ElMessage.error(proxy.$t('admin.messages.update_failed'));
        selectedStatus.value = order.value.status;
    } finally {
        isUpdating.value = false;
    }
};

onMounted(fetchOrder);
</script>

<template>
    <div class="detail-page" v-loading="isLoading">

        <!-- Back Nav + Order Number -->
        <div class="detail-header">
            <button class="back-btn" @click="router.push('/admin/orders')">
                <svg viewBox="0 0 16 16" fill="none" width="14" height="14"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ $t('admin.orders.back_to_list') }}
            </button>
            <div class="order-hero" v-if="order">
                <div class="order-hero-num">{{ order.order_number }}</div>
                <span class="status-pill-lg" :class="`status-${order.status}`">
                    {{ $t(`admin.orders.status_${order.status}`) }}
                </span>
            </div>
        </div>

        <template v-if="order">
            <div class="detail-grid">

                <!-- Block A: Customer Info -->
                <div class="info-card main-card">
                    <div class="card-header-line">
                        <span class="card-label">{{ $t('admin.orders.customer_info') }}</span>
                        <span class="card-date">{{ order.created_at?.substring(0, 16).replace('T', ' ') }}</span>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-key">{{ $t('admin.orders.customer') }}</div>
                            <div class="info-val">{{ order.customer_name }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-key">{{ $t('admin.orders.email') }}</div>
                            <div class="info-val">
                                <a :href="`mailto:${order.customer_email}`" class="info-link">{{ order.customer_email }}</a>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-key">{{ $t('admin.orders.company') }}</div>
                            <div class="info-val" :class="{ 'info-empty': !order.company_name }">{{ order.company_name || '—' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-key">{{ $t('admin.orders.phone') }}</div>
                            <div class="info-val">
                                <a v-if="order.customer_phone" :href="`tel:${order.customer_phone}`" class="info-link">{{ order.customer_phone }}</a>
                                <span v-else class="info-empty">—</span>
                            </div>
                        </div>
                        <div class="info-item info-full">
                            <div class="info-key">{{ $t('admin.orders.address') }}</div>
                            <div class="info-val" :class="{ 'info-empty': !order.customer_address }">{{ order.customer_address || '—' }}</div>
                        </div>
                        <div class="info-item info-full" v-if="order.notes">
                            <div class="info-key">{{ $t('admin.orders.notes') }}</div>
                            <div class="info-val notes-val">{{ order.notes }}</div>
                        </div>
                    </div>
                </div>

                <!-- Block B: Status Control -->
                <div class="info-card side-card">
                    <div class="card-header-line">
                        <span class="card-label">{{ $t('admin.orders.status_control') }}</span>
                    </div>
                    <div class="status-display">
                        <div class="status-now-label">目前狀態</div>
                        <span class="status-pill-lg" :class="`status-${order.status}`">
                            {{ $t(`admin.orders.status_${order.status}`) }}
                        </span>
                    </div>
                    <div class="status-divider"></div>
                    <div class="status-update-label">{{ $t('admin.orders.update_status') }}</div>
                    <el-select v-model="selectedStatus" class="status-select">
                        <el-option
                            v-for="opt in statusOptions"
                            :key="opt.value"
                            :value="opt.value"
                            :label="opt.label()"
                        />
                    </el-select>
                    <button
                        class="update-btn"
                        :class="{ loading: isUpdating, disabled: selectedStatus === order.status }"
                        :disabled="selectedStatus === order.status || isUpdating"
                        @click="updateStatus"
                    >
                        <span v-if="isUpdating">更新中…</span>
                        <span v-else>{{ $t('admin.orders.update_status') }}</span>
                    </button>
                </div>
            </div>

            <!-- Block C: Order Items -->
            <div class="items-card">
                <div class="card-header-line">
                    <span class="card-label">{{ $t('admin.orders.items') }}</span>
                    <span class="items-count">{{ order.order_items?.length }} 項商品</span>
                </div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>{{ $t('admin.orders.product_name') }}</th>
                            <th class="num-col">{{ $t('admin.orders.price') }}</th>
                            <th class="num-col">{{ $t('admin.orders.quantity') }}</th>
                            <th class="num-col">{{ $t('admin.orders.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in order.order_items" :key="item.id" class="item-row">
                            <td class="product-name-cell">{{ item.product_name }}</td>
                            <td class="num-col">
                                <span v-if="Number(item.price) > 0" class="price-val">NT$ {{ Number(item.price).toLocaleString() }}</span>
                                <span v-else class="price-empty">詢價</span>
                            </td>
                            <td class="num-col qty-val">× {{ item.quantity }}</td>
                            <td class="num-col">
                                <span v-if="Number(item.price) > 0" class="subtotal-val">NT$ {{ (Number(item.price) * item.quantity).toLocaleString() }}</span>
                                <span v-else class="price-empty">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="items-total">
                    <span class="total-label-text">{{ $t('admin.orders.total') }}</span>
                    <span v-if="total > 0" class="total-amount">NT$ {{ total.toLocaleString() }}</span>
                    <span v-else class="total-inquire">以詢價為準</span>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.detail-page {
    padding: 32px 32px 64px;
    max-width: 1100px;
    font-family: 'Instrument Sans', 'PingFang TC', sans-serif;
}

/* Header */
.detail-header {
    margin-bottom: 28px;
}
.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: none;
    border: none;
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    color: #78716c;
    cursor: pointer;
    padding: 0;
    margin-bottom: 20px;
    transition: color 0.15s;
}
.back-btn:hover { color: #b45309; }
.order-hero {
    display: flex;
    align-items: center;
    gap: 16px;
}
.order-hero-num {
    font-family: 'Fraunces', 'JetBrains Mono', Georgia, serif;
    font-size: 28px;
    font-weight: 600;
    color: #1c1917;
    letter-spacing: 0.01em;
}

/* Status Pills */
.status-pill-lg {
    display: inline-flex;
    align-items: center;
    padding: 5px 14px;
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: 0.04em;
}
.status-pending   { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-completed  { background: #d1fae5; color: #065f46; }
.status-cancelled  { background: #f5f5f4; color: #78716c; }

/* Layout */
.detail-grid {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 16px;
    margin-bottom: 16px;
}

/* Cards */
.info-card, .items-card {
    background: #ffffff;
    border: 1px solid #e7e5e4;
    border-radius: 12px;
    padding: 24px;
}
.card-header-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1.5px solid #f5f5f4;
}
.card-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #a8a29e;
}
.card-date {
    font-family: 'JetBrains Mono', monospace;
    font-size: 12px;
    color: #a8a29e;
}

/* Info Grid */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 32px;
}
.info-full { grid-column: 1 / -1; }
.info-key {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #a8a29e;
    margin-bottom: 4px;
}
.info-val {
    font-size: 14px;
    color: #1c1917;
    font-weight: 500;
}
.info-empty { color: #d4ccc9; font-weight: 400; }
.info-link {
    color: #b45309;
    text-decoration: none;
    font-weight: 500;
}
.info-link:hover { text-decoration: underline; }
.notes-val {
    line-height: 1.6;
    color: #44403c;
    font-weight: 400;
    white-space: pre-wrap;
}

/* Side Card */
.side-card { display: flex; flex-direction: column; }
.status-display {
    margin-bottom: 16px;
}
.status-now-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #a8a29e;
    margin-bottom: 8px;
}
.status-divider {
    height: 1px;
    background: #f5f5f4;
    margin: 16px 0;
}
.status-update-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: #a8a29e;
    margin-bottom: 8px;
}
.status-select { width: 100%; margin-bottom: 12px; }
.update-btn {
    width: 100%;
    padding: 10px;
    background: #1c1917;
    color: #fef3c7;
    border: none;
    border-radius: 8px;
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, opacity 0.15s;
    letter-spacing: 0.02em;
}
.update-btn:hover:not(.disabled):not(.loading) { background: #b45309; }
.update-btn.disabled { background: #e7e5e4; color: #a8a29e; cursor: not-allowed; }
.update-btn.loading { opacity: 0.7; cursor: wait; }

/* Items Table */
.items-card { margin-top: 0; }
.items-count {
    font-size: 12px;
    color: #a8a29e;
    font-weight: 500;
}
.items-table {
    width: 100%;
    border-collapse: collapse;
}
.items-table th {
    padding: 8px 12px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #a8a29e;
    border-bottom: 1.5px solid #f5f5f4;
}
.item-row td {
    padding: 14px 12px;
    border-bottom: 1px solid #f9f7f4;
    vertical-align: middle;
}
.item-row:last-child td { border-bottom: none; }
.product-name-cell {
    font-weight: 500;
    color: #1c1917;
    font-size: 14px;
}
.num-col { text-align: right; }
.price-val { font-size: 13.5px; color: #44403c; }
.price-empty { font-size: 12px; color: #c4b5b0; }
.qty-val { font-size: 13px; color: #78716c; font-family: 'JetBrains Mono', monospace; }
.subtotal-val { font-size: 13.5px; font-weight: 600; color: #1c1917; }

.items-total {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 16px;
    padding: 16px 12px 4px;
    border-top: 1.5px solid #f5f5f4;
    margin-top: 4px;
}
.total-label-text {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #a8a29e;
}
.total-amount {
    font-family: 'Fraunces', serif;
    font-size: 22px;
    font-weight: 600;
    color: #b45309;
}
.total-inquire {
    font-size: 14px;
    color: #a8a29e;
    font-style: italic;
}
</style>
