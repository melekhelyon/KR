<template>
    <div>
        <button class="add" @click="isModalVisible = true">Новая операция</button>        
        <modal :visible="isModalVisible" @close="isModalVisible = false">
            <div class="mb-3">
                <select class="form-control" v-model="operation_type">
                    <option value="regular">Обычная операция</option>
                    <option value="transfer">Перевод между счетами</option>
                </select>
            </div>          
            <template v-if="operation_type === 'regular'">
                <div class="mb-3">
                    <select class="form-control" v-model="account_id">
                        <option :value="null">Выберите счет</option>
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                            {{ acc.name }} ({{ acc.balance }} {{ acc.currency }})
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <select class="form-control" v-model="category_id">
                        <option :value="null">Выберите категорию</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }} ({{ cat.type === 'income' ? 'Доход' : 'Расход' }})
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="number" class="form-control" v-model="amount" placeholder="Сумма" min="0" step="0.01">
                    <small v-if="selectedCategory" style="color: #94A3B8;">
                        {{ selectedCategory.type === 'income' ? 'Сумма будет добавлена' : 'Сумма будет вычтена' }}
                    </small>
                </div>
            </template>            
            <template v-if="operation_type === 'transfer'">
                <div class="mb-3">
                    <select class="form-control" v-model="account_id">
                        <option :value="null">Счет списания</option>
                        <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                            {{ acc.name }} ({{ acc.balance }} {{ acc.currency }})
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <select class="form-control" v-model="transfer_account_id">
                        <option :value="null">Счет зачисления</option>
                        <option v-for="acc in filteredAccountsForTransfer" :key="acc.id" :value="acc.id">
                            {{ acc.name }} ({{ acc.balance }} {{ acc.currency }})
                        </option>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="number" class="form-control" v-model="amount" placeholder="Сумма перевода" min="0.01" step="0.01">
                </div>
            </template>           
            <div class="mb-3">
                <input type="date" class="form-control" v-model="operation_date">
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" v-model="description" placeholder="Описание">
            </div>
            <div class="mb-3">
                <input @click.prevent="addOperation" type="submit" class="btn btn-primary" value="Ок">
            </div>
        </modal>
        <div class="rec">
            <div class="filters">
                <input type="text" class="search-input" v-model="searchQuery" placeholder="Поиск по описанию...">
                <select class="filter-select" v-model="filterType">
                    <option value="">Все типы</option>
                    <option value="income">Доходы</option>
                    <option value="expense">Расходы</option>
                    <option value="transfer">Переводы</option>
                </select>
                <select class="filter-select" v-model="filterAccount">
                    <option value="">Все счета</option>
                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
                <input type="date" class="filter-date" v-model="dateFrom" placeholder="С">
                <input type="date" class="filter-date" v-model="dateTo" placeholder="По">
            </div>
            <div class="chart-container" v-show="filteredOperations.length > 0">
                <apexchart 
                    type="line" 
                    :options="chartOptions" 
                    :series="chartSeries"
                    height="200"
                />
            </div>
            <div v-show="filteredOperations.length === 0" class="no-data">
                Нет данных для отображения
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Тип</th>
                        <th>Счет</th>
                        <th>Категория/Счет зачисления</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="op in paginatedOperations" :key="op.id">
                        <td class="td">{{ op.operation_date }}</td>
                        <td class="td">
                            <span v-if="op.transfer_account_id" style="color: #00D4FF;">🔄 Перевод</span>
                            <span v-else-if="op.category && op.category.type === 'income'" style="color: #28a745;">📈 Доход</span>
                            <span v-else style="color: #dc3545;">📉 Расход</span>
                        </td>
                        <td class="td">{{ op.account ? op.account.name : '—' }}</td>
                        <td class="td">
                            <span v-if="op.transfer_account_id">
                                → {{ getAccountName(op.transfer_account_id) }}
                            </span>
                            <span v-else>
                                {{ op.category ? op.category.name : '—' }}
                            </span>
                        </td>
                        <td class="td">
                            <span v-if="op.transfer_account_id" style="color: #00D4FF; font-weight: bold;">
                                {{ Math.abs(op.amount) }}
                            </span>
                            <span v-else-if="op.category && op.category.type === 'income'" style="color: #28a745; font-weight: bold;">
                                ↑ {{ op.amount }}
                            </span>
                            <span v-else style="color: #dc3545; font-weight: bold;">
                                ↓ {{ Math.abs(op.amount) }}
                            </span>
                        </td>
                        <td class="td">
                            <span :class="'status-' + op.status">
                                {{ op.status === 'accepted' ? '✅ Принят' : '⏳ В обработке' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="pagination-controls">
                <button :disabled="currentPage === 1" @click="currentPage--">Назад</button>
                <span> Страница {{ currentPage }} из {{ totalPages }} </span>
                <button :disabled="currentPage >= totalPages" @click="currentPage++">Вперед</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import Modal from './Modal.vue';
import VueApexCharts from "vue3-apexcharts";

export default {
    name: 'Operations',

    components: { 
        Modal,
        apexchart: VueApexCharts
    },

    data() {
        return {
            operation_type: 'regular',
            account_id: null,
            transfer_account_id: null,
            category_id: null,
            amount: null,
            description: '',
            operation_date: new Date().toISOString().substr(0, 10),
            operations: [],
            accounts: [],
            categories: [],
            isModalVisible: false,
            currentPage: 1,
            itemsPerPage: 8,
            searchQuery: '',
            filterType: '',
            filterAccount: '',
            dateFrom: '',
            dateTo: '',
        }
    },

    computed: {
        filteredOperations() {
            return this.operations.filter(op => {
                const matchesSearch = (op.description || '').toLowerCase().includes(this.searchQuery.toLowerCase());
                let matchesType = true;
                if (this.filterType === 'income') {
                    matchesType = op.category && op.category.type === 'income' && !op.transfer_account_id;
                } else if (this.filterType === 'expense') {
                    matchesType = op.category && op.category.type === 'expense' && !op.transfer_account_id;
                } else if (this.filterType === 'transfer') {
                    matchesType = !!op.transfer_account_id;
                }
                const matchesAccount = !this.filterAccount || op.account_id == this.filterAccount;
                const opDate = new Date(op.operation_date);
                const fromDate = this.dateFrom ? new Date(this.dateFrom) : null;
                const toDate = this.dateTo ? new Date(this.dateTo) : null;
                let matchesDate = true;
                if (fromDate) matchesDate = matchesDate && opDate >= fromDate;
                if (toDate) matchesDate = matchesDate && opDate <= toDate;
                return matchesSearch && matchesType && matchesAccount && matchesDate;
            });
        },

        paginatedOperations() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredOperations.slice(start, start + this.itemsPerPage);
        },

        totalPages() {
            return Math.ceil(this.filteredOperations.length / this.itemsPerPage) || 1;
        },

        selectedCategory() {
            return this.categories.find(cat => cat.id === this.category_id);
        },

        filteredAccountsForTransfer() {
            return this.accounts.filter(acc => acc.id !== this.account_id);
        },

        chartOptions() {
            return {
                chart: {
                    type: 'line',
                    background: 'transparent',
                    toolbar: { show: false },
                    foreColor: '#94A3B8'
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                colors: ['#28a745', '#dc3545'],
                xaxis: {
                    categories: this.getChartDates(),
                    labels: { style: { colors: '#94A3B8' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#94A3B8' } },
                    grid: { borderColor: 'rgba(255, 255, 255, 0.1)' }
                },
                grid: { borderColor: 'rgba(255, 255, 255, 0.1)' },
                legend: {
                    labels: { colors: '#94A3B8' },
                    position: 'top'
                },
                theme: { mode: 'dark' },
                tooltip: { theme: 'dark' }
            };
        },

        chartSeries() {
            const dateGroups = {};
            this.filteredOperations.forEach(op => {
                const date = op.operation_date;
                if (!dateGroups[date]) {
                    dateGroups[date] = { income: 0, expense: 0 };
                }
                if (op.category && !op.transfer_account_id) {
                    if (op.category.type === 'income') {
                        dateGroups[date].income += parseFloat(op.amount) || 0;
                    } else {
                        dateGroups[date].expense += Math.abs(parseFloat(op.amount)) || 0;
                    }
                }
            });
            const dates = Object.keys(dateGroups).sort();
            return [
                {
                    name: 'Доходы',
                    data: dates.map(d => dateGroups[d].income)
                },
                {
                    name: 'Расходы',
                    data: dates.map(d => dateGroups[d].expense)
                }
            ];
        }
    },

    watch: {
        searchQuery() { this.currentPage = 1; },
        filterType() { this.currentPage = 1; },
        filterAccount() { this.currentPage = 1; },
        dateFrom() { this.currentPage = 1; },
        dateTo() { this.currentPage = 1; }
    },

    mounted() {
        this.getOperations();
        this.getAccounts();
        this.getCategories();
    },

    methods: {
        getChartDates() {
            const dateGroups = {};
            this.filteredOperations.forEach(op => {
                const date = op.operation_date;
                if (!dateGroups[date]) {
                    dateGroups[date] = { income: 0, expense: 0 };
                }
                if (op.category && !op.transfer_account_id) {
                    if (op.category.type === 'income') {
                        dateGroups[date].income += parseFloat(op.amount) || 0;
                    } else {
                        dateGroups[date].expense += Math.abs(parseFloat(op.amount)) || 0;
                    }
                }
            });
            return Object.keys(dateGroups).sort();
        },

        getOperations() {
            axios.get('/api/operations').then(res => {
                this.operations = res.data;
            });
        },

        getAccounts() {
            axios.get('/api/account').then(res => {
                this.accounts = res.data;
            });
        },

        getCategories() {
            axios.get('/api/category').then(res => {
                this.categories = res.data;
            });
        },

        getAccountName(accountId) {
            const account = this.accounts.find(acc => acc.id === accountId);
            return account ? account.name : '—';
        },

        addOperation() {
            if (this.operation_type === 'transfer') {
                this.addTransfer();
            } else {
                this.addRegularOperation();
            }
        },

        addRegularOperation() {
            let finalAmount = parseFloat(this.amount);
            if (this.selectedCategory) {
                if (this.selectedCategory.type === 'expense' && finalAmount > 0) {
                    finalAmount = -Math.abs(finalAmount);
                } else if (this.selectedCategory.type === 'income' && finalAmount < 0) {
                    finalAmount = Math.abs(finalAmount);
                }
            }
            axios.post('/api/operations/create', {
                account_id: this.account_id,
                category_id: this.category_id,
                amount: finalAmount,
                operation_date: this.operation_date,
                description: this.description
            }).then(() => {
                this.isModalVisible = false;
                this.getOperations();
                this.resetForm();
            });
        },

        addTransfer() {
            const transferAmount = parseFloat(this.amount);
            
            axios.post('/api/operations/transfer', {
                from_account_id: this.account_id,
                to_account_id: this.transfer_account_id,
                amount: transferAmount,
                operation_date: this.operation_date,
                description: this.description || 'Перевод между счетами'
            }).then(() => {
                this.isModalVisible = false;
                this.getOperations();
                this.getAccounts();
                this.resetForm();
            });
        },
        
        resetForm() {
            this.operation_type = 'regular';
            this.account_id = null;
            this.transfer_account_id = null;
            this.category_id = null;
            this.amount = null;
            this.description = '';
        }
    }
}
</script>

<style scoped>
    .add {
    position: absolute;
    top: 193px;
    left: 76%;
    transform: translate(-50%);
    width: 180px;
    height: 44px;
    border: none;
    background: linear-gradient(to right, rgba(0, 212, 255, 1) 0%, rgba(190, 94, 237, 1) 100%);
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(6, 182, 212, 0.3);
    transition: all 0.1s ease;
    font-size: 16px;
    font-family: 'Inter', sans-serif;
    font-weight: bold;
    color: white;
    cursor: pointer;
    z-index: 10;
    }

    .add:active {
    box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
    transform: translate(-50%, 3px);
    }

    .rec {
    width: 1232px;
    min-height: 572.5px;
    background: rgba(15, 23, 41, 0.6);
    backdrop-filter: blur(24px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    position: absolute;
    top: 261px;
    left: 50%;
    transform: translate(-50%);
    color: white;
    padding: 24px;
    box-sizing: border-box;
    overflow-x: auto;
    display: flex;
    flex-direction: column;
    }

    .filters {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    }

    .search-input, .filter-select, .filter-date {
    font-family: 'JetBrains Mono', monospace;
    font-size: 14px;
    border-radius: 10px;
    background: rgba(22, 33, 60, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    outline: none;
    padding: 10px 13px;
    color: white;
    width: 180px;
    }

    .search-input::placeholder, .filter-date::placeholder {
    color: rgba(148, 163, 184, 0.5);
    }

    .filter-select option {
    background: rgba(15, 23, 41, 0.95);
    color: white;
    }

    .chart-container {
    margin-bottom: 20px;
    }

    .no-data {
    height: 200px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94A3B8;
    font-family: 'Inter', sans-serif;
    border: 1px dashed rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    }

    .table {
    width: 100%;
    text-align: left;
    border-collapse: collapse;
    border-spacing: 0;
    font-family: 'Inter', sans-serif;
    flex: 1;
    }

    .table thead tr {
    border-bottom: 2px solid rgba(0, 212, 255, 0.3);
    }

    .table th {
    padding: 16px 12px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94A3B8;
    font-family: 'JetBrains Mono', monospace;
    }

    .table td {
    padding: 16px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    color: #F8FAFC;
    }

    .table tbody tr:hover {
    background: rgba(255, 255, 255, 0.03);
    }

    .status-accepted {
    color: #28a745;
    font-weight: bold;
    padding: 4px 10px;
    background: rgba(40, 167, 69, 0.1);
    border-radius: 20px;
    font-size: 12px;
    text-transform: uppercase;
    display: inline-block;
    }

    .status-in_processing {
    color: #ffc107;
    padding: 4px 10px;
    background: rgba(255, 193, 7, 0.1);
    border-radius: 20px;
    font-size: 12px;
    text-transform: uppercase;
    display: inline-block;
    }

    .pagination-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: auto;
    padding-top: 24px;
    gap: 16px;
    font-family: 'Inter', sans-serif;
    color: #94A3B8;
    }

    .pagination-controls button {
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(22, 33, 60, 0.5);
    border-radius: 8px;
    padding: 8px 20px;
    cursor: pointer;
    font-size: 14px;
    color: white;
    transition: all 0.1s ease;
    }

    .pagination-controls button:hover:not(:disabled) {
    background: rgba(0, 212, 255, 0.2);
    border-color: rgba(0, 212, 255, 0.5);
    }

    .pagination-controls button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    }

    small {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    font-family: 'Inter', sans-serif;
    }
</style>