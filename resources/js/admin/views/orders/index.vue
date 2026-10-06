<script setup>
import { ref, onMounted, getCurrentInstance } from 'vue';
import { useRouter } from 'vue-router';
import { orderService } from '../../../services';
import { ElMessage } from 'element-plus';

const { proxy } = getCurrentInstance();
const router = useRouter();

const orders = ref([]);
const isLoading = ref(false);
const total = ref(0);
const currentPage = ref(1);
const pageSize = ref(15);
const searchQuery = ref('');
const filterStatus = ref('');

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

const fetchOrders = async () => {
    isLoading.value = true;
    try {
        const params = { page: currentPage.value };
        if (filterStatus.value) params.status = filterStatus.value;
        if (searchQuery.value) params.search = searchQuery.value;

        const res = await orderService.list(params);
        orders.value = res.data.data.data;
        total.value = res.data.data.total;
    } catch {
        ElMessage.error(proxy.$t('admin.messages.load_failed'));
    } finally {
        isLoading.value = false;
    }
};

const handleSearch = () => {
    currentPage.value = 1;
    fetchOrders();
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchOrders();
};

const goToDetail = (row) => {
    router.push(`/admin/orders/${row.id}`);
};

const statusLabel = (status) => {
    return proxy.$t(`admin.orders.status_${status}`);
};

onMounted(fetchOrders);
</script>

<template>
    <div class="orders-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <div class="header-eyebrow">管理後台</div>
                <h1 class="page-title">{{ $t('admin.orders.title') }}</h1>
            </div>
            <div class="total-badge">
                <span class="total-num">{{ total }}</span>
                <span class="total-label">筆詢價</span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <div class="filter-group">
                <div class="search-wrap">
                    <svg class="search-icon" viewBox="0 0 20 20" fill="none"><circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="1.5"/><path d="M13 13l3.5 3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    <input
                        v-model="searchQuery"
                        :placeholder="$t('admin.orders.search_placeholder')"
                        class="search-input"
                        @keyup.enter="handleSearch"
                    />
                    <button v-if="searchQuery" class="clear-btn" @click="searchQuery = ''; handleSearch()">✕</button>
                </div>

                <div class="status-tabs">
                    <button
                        v-for="opt in [{ value: '', label: () => $t('admin.orders.all_status') }, ...statusOptions]"
                        :key="opt.value"
                        class="status-tab"
                        :class="{ active: filterStatus === opt.value }"
                        @click="filterStatus = opt.value; handleSearch()"
                    >{{ opt.label() }}</button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-wrap" v-loading="isLoading">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>{{ $t('admin.orders.order_number') }}</th>
                        <th>{{ $t('admin.orders.customer') }}</th>
                        <th>{{ $t('admin.orders.company') }}</th>
                        <th>{{ $t('admin.orders.status') }}</th>
                        <th>{{ $t('admin.orders.date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, i) in orders"
                        :key="row.id"
                        class="order-row"
                        :style="{ animationDelay: `${i * 40}ms` }"
                        @click="goToDetail(row)"
                    >
                        <td>
                            <span class="order-num">{{ row.order_number }}</span>
                        </td>
                        <td>
                            <div class="customer-name">{{ row.customer_name }}</div>
                            <div class="customer-email">{{ row.customer_email }}</div>
                        </td>
                        <td>
                            <span :class="row.company_name ? 'company-name' : 'empty-val'">
                                {{ row.company_name || '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="status-pill" :class="`status-${row.status}`">
                                {{ statusLabel(row.status) }}
                            </span>
                        </td>
                        <td>
                            <div class="date-main">{{ row.created_at?.substring(0, 10) }}</div>
                            <div class="date-time">{{ row.created_at?.substring(11, 16) }}</div>
                        </td>
                        <td class="arrow-cell">
                            <svg class="row-arrow" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="!isLoading && orders.length === 0" class="empty-state">
                <div class="empty-icon">◎</div>
                <div class="empty-text">目前沒有訂單</div>
            </div>

            <!-- Pagination -->
            <div class="pagination-bar">
                <span class="page-info">共 <strong>{{ total }}</strong> 筆</span>
                <el-pagination
                    v-model:current-page="currentPage"
                    :page-size="pageSize"
                    :total="total"
                    layout="prev, pager, next"
                    @current-change="handlePageChange"
                    small
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.orders-page {
    padding: 32px 32px 64px;
    max-width: 1200px;
    font-family: 'Instrument Sans', sans-serif;
}

/* Header */
.page-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 32px;
}
.header-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #b45309;
    margin-bottom: 6px;
}
.page-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 32px;
    font-weight: 600;
    color: #1c1917;
    line-height: 1;
    margin: 0;
}
.total-badge {
    display: flex;
    align-items: baseline;
    gap: 6px;
    padding: 10px 20px;
    background: #1c1917;
    border-radius: 100px;
}
.total-num {
    font-family: 'Fraunces', serif;
    font-size: 22px;
    font-weight: 600;
    color: #fef3c7;
    line-height: 1;
}
.total-label {
    font-size: 12px;
    color: #78716c;
    letter-spacing: 0.05em;
}

/* Filter Bar */
.filter-bar {
    background: #ffffff;
    border: 1px solid #e7e5e4;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
}
.filter-group {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    width: 100%;
}
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.search-icon {
    position: absolute;
    left: 12px;
    width: 16px;
    height: 16px;
    color: #a8a29e;
    pointer-events: none;
}
.search-input {
    width: 260px;
    padding: 8px 36px 8px 36px;
    border: 1.5px solid #e7e5e4;
    border-radius: 8px;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 13.5px;
    color: #1c1917;
    background: #fafaf8;
    outline: none;
    transition: border-color 0.15s;
}
.search-input:focus { border-color: #b45309; }
.search-input::placeholder { color: #c4b5b0; }
.clear-btn {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    color: #a8a29e;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
    padding: 2px;
}
.status-tabs {
    display: flex;
    gap: 4px;
}
.status-tab {
    padding: 6px 14px;
    border-radius: 100px;
    border: 1.5px solid transparent;
    font-family: 'Instrument Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: #78716c;
    background: transparent;
    cursor: pointer;
    transition: all 0.15s;
}
.status-tab:hover { background: #f5f5f4; color: #1c1917; }
.status-tab.active {
    background: #1c1917;
    color: #fafaf8;
    border-color: #1c1917;
}

/* Table */
.table-wrap {
    background: #ffffff;
    border: 1px solid #e7e5e4;
    border-radius: 12px;
    overflow: hidden;
}
.orders-table {
    width: 100%;
    border-collapse: collapse;
}
.orders-table thead tr {
    border-bottom: 1.5px solid #e7e5e4;
}
.orders-table th {
    padding: 12px 20px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: #a8a29e;
}
.order-row {
    border-bottom: 1px solid #f5f5f4;
    cursor: pointer;
    transition: background 0.12s;
    animation: rowIn 0.3s ease both;
}
@keyframes rowIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.order-row td:first-child {
    border-left: 3px solid transparent;
    padding-left: 17px;
    transition: border-color 0.15s;
}
.order-row:hover { background: #fafaf8; }
.order-row:hover td:first-child { border-left-color: #b45309; }
.order-row:last-child { border-bottom: none; }
.orders-table td {
    padding: 14px 20px;
    vertical-align: middle;
}
.order-num {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    font-weight: 600;
    color: #1c1917;
    letter-spacing: 0.02em;
}
.customer-name {
    font-weight: 600;
    font-size: 14px;
    color: #1c1917;
}
.customer-email {
    font-size: 12px;
    color: #a8a29e;
    margin-top: 2px;
}
.company-name { font-size: 13.5px; color: #44403c; }
.empty-val { color: #d4ccc9; }

/* Status Pills */
.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.03em;
}
.status-pending   { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-completed  { background: #d1fae5; color: #065f46; }
.status-cancelled  { background: #f5f5f4; color: #78716c; }

.date-main { font-size: 13px; color: #44403c; }
.date-time { font-size: 11.5px; color: #a8a29e; margin-top: 2px; }

.arrow-cell { width: 48px; text-align: center; }
.row-arrow {
    width: 16px;
    height: 16px;
    color: #d4ccc9;
    transition: all 0.15s;
    display: inline-block;
}
.order-row:hover .row-arrow {
    color: #b45309;
    transform: translateX(3px);
}

/* Empty */
.empty-state {
    padding: 64px;
    text-align: center;
}
.empty-icon {
    font-size: 40px;
    color: #d4ccc9;
    margin-bottom: 12px;
}
.empty-text { font-size: 15px; color: #a8a29e; }

/* Pagination */
.pagination-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-top: 1px solid #f5f5f4;
    background: #fafaf8;
}
.page-info { font-size: 13px; color: #a8a29e; }
.page-info strong { color: #44403c; }
</style>
