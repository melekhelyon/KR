<template>
    <div>
        <button class="add" @click="isModalVisible = true">Добавить</button> 
        <modal :visible="isModalVisible" @close="isModalVisible = false">
            <div class="mb-3">
                <input type="text" class="form-control" v-model="name" placeholder="Название счета">
            </div>
            <div class="mb-3">
                <select class="form-control" v-model="type">
                    <option value="debit">debit (Дебетовый)</option>
                    <option value="credit">credit (Кредитный)</option>
                    <option value="savings">savings (Сберегательный)</option>
                    <option value="salary">salary (Зарплатный)</option>
                </select>
            </div>
            <div class="mb-3">
                <input type="text" class="form-control" v-model="currency" placeholder="Валюта (напр. RUB)">
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" v-model="balance" placeholder="Начальный баланс">
            </div>
            <div class="mb-3">
                <input @click.prevent="addAccount" type="submit" class="btn btn-primary" value="добавить">
            </div>
        </modal>
        <div class="rec">
            <div class="filters">
                <input type="text" class="search-input" v-model="searchQuery" placeholder="Поиск по названию...">
                <select class="filter-select" v-model="filterType">
                    <option value="">Все типы</option>
                    <option value="debit">Дебетовый</option>
                    <option value="credit">Кредитный</option>
                    <option value="savings">Сберегательный</option>
                    <option value="salary">Зарплатный</option>
                </select>
                <select class="filter-select" v-model="filterCurrency">
                    <option value="">Все валюты</option>
                    <option v-for="curr in uniqueCurrencies" :key="curr" :value="curr">{{ curr }}</option>
                </select>
            </div>
            <div class="chart-container" v-show="filteredAccounts.length > 0">
                <apexchart 
                    type="bar" 
                    :options="chartOptions" 
                    :series="chartSeries"
                    height="200"
                />
            </div>
            <div v-show="filteredAccounts.length === 0" class="no-data">
                Нет данных для отображения
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Тип</th>
                        <th>Валюта</th>
                        <th>Баланс</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="account in paginatedAccounts" :key="account.id">
                        <template v-if="!account.is_editing">
                            <td class="td">{{ account.name }}</td>
                            <td class="td">{{ getTypeName(account.type) }}</td>
                            <td class="td">{{ account.currency }}</td>
                            <td class="td" :style="{ color: account.balance < 0 ? '#dc3545' : 'white' }">
                                {{ formatBalance(account.balance) }}
                            </td>
                            <td>
                                <button @click="account.is_editing = true">✏️</button>
                                <button @click="deleteAccount(account.id)" style="color: red; margin-left: 10px;">🗑️</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="td"><input v-model="account.name" class="form-control"></td>
                            <td class="td">
                                <select v-model="account.type" class="form-control">
                                    <option value="debit">debit</option>
                                    <option value="credit">credit</option>
                                    <option value="savings">savings</option>
                                    <option value="salary">salary</option>
                                </select>
                            </td>
                            <td class="td"><input v-model="account.currency" class="form-control"></td>
                            <td class="td"><input type="number" v-model="account.balance" class="form-control"></td>
                            <td>
                                <button @click="updateAccount(account)">💾</button>
                                <button @click="account.is_editing = false" style="margin-left: 10px;">❌</button>
                            </td>
                        </template>
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
    name: 'Accounts',
    components: { 
        Modal,
        apexchart: VueApexCharts
    },
    data() {
        return {
            name: null,
            type: 'debit',
            currency: 'RUB',
            balance: 0,
            Accounts: [],
            isModalVisible: false,
            currentPage: 1,
            itemsPerPage: 5,
            searchQuery: '',
            filterType: '',
            filterCurrency: '',
        }
    },
    computed: {
        filteredAccounts() {
            return this.Accounts.filter(acc => {
                const matchesSearch = acc.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesType = !this.filterType || acc.type === this.filterType;
                const matchesCurrency = !this.filterCurrency || acc.currency === this.filterCurrency;
                return matchesSearch && matchesType && matchesCurrency;
            });
        },

        paginatedAccounts() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredAccounts.slice(start, start + this.itemsPerPage);
        },

        totalPages() {
            return Math.ceil(this.filteredAccounts.length / this.itemsPerPage) || 1;
        },

        uniqueCurrencies() {
            return [...new Set(this.Accounts.map(acc => acc.currency))];
        },

        chartOptions() {
            return {
                chart: {
                    type: 'bar',
                    background: 'transparent',
                    toolbar: { show: false },
                    foreColor: '#94A3B8'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '60%',
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(2) + ' ₽',
                    offsetY: -20,
                    style: { fontSize: '12px', colors: ['#94A3B8'] }
                },
                xaxis: {
                    categories: this.filteredAccounts.map(a => a.name),
                    labels: { style: { colors: '#94A3B8' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: '#94A3B8' } },
                    grid: { borderColor: 'rgba(255, 255, 255, 0.1)' }
                },
                grid: { borderColor: 'rgba(255, 255, 255, 0.1)' },
                theme: { mode: 'dark' },
                tooltip: { theme: 'dark' }
            };
        },

        chartSeries() {
            return [{
                name: 'Баланс',
                data: this.filteredAccounts.map(a => parseFloat(a.balance) || 0)
            }];
        }
    },

    watch: {
        searchQuery() { this.currentPage = 1; },

        filterType() { this.currentPage = 1; },

        filterCurrency() { this.currentPage = 1; }
    },

    mounted() {
        this.getAccounts();
    },

    methods: {
        getAccounts() {
            axios.get('/api/account').then(res => {
                this.Accounts = res.data.map(acc => {
                    return { ...acc, is_editing: false }
                });
            });
        },

        getTypeName(type) {
            const types = {
                'debit': 'Дебетовый',
                'credit': 'Кредитный',
                'savings': 'Сберегательный',
                'salary': 'Зарплатный'
            };
            return types[type] || type;
        },

        formatBalance(balance) {
            return parseFloat(balance).toFixed(2);
        },

        addAccount() {
            axios.post('/api/account/create', {
                name: this.name,
                type: this.type,
                currency: this.currency,
                balance: this.balance,
            }).then(() => {
                this.isModalVisible = false;
                this.getAccounts();
                this.name = null;
                this.type = 'debit';
                this.currency = 'RUB';
                this.balance = 0;
            });
        },

        updateAccount(account) {
            axios.put(`/api/account/update/${account.id}`, {
                name: account.name,
                type: account.type,
                currency: account.currency,
                balance: account.balance,
            }).then(() => {
                account.is_editing = false;
            });
        },
        
        deleteAccount(id) {
            if (confirm('Удалить этот счет?')) {
                axios.delete(`/api/account/delete/${id}`).then(() => {
                    this.Accounts = this.Accounts.filter(acc => acc.id !== id);
                });
            }
        },
    }
}
</script>

<style scoped>
    .add {
    position: absolute;
    top: 193px;
    left: 76%;
    transform: translate(-50%);
    width: 160px;
    height: 44px;
    box-sizing: border-box;
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

    .search-input, .filter-select {
    font-family: 'JetBrains Mono', monospace;
    font-size: 14px;
    border-radius: 10px;
    background: rgba(22, 33, 60, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    outline: none;
    padding: 10px 13px;
    color: white;
    width: 200px;
    }

    .search-input::placeholder {
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

    .table button {
    background: transparent;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    padding: 6px 12px;
    margin: 0 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.1s ease;
    color: white;
    font-family: 'Inter', sans-serif;
    }

    .table button:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(0, 212, 255, 0.5);
    }

    .table .form-control {
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    border-radius: 8px;
    background: rgba(22, 33, 60, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    outline: none;
    padding: 8px 12px;
    color: white;
    width: 100%;
    box-sizing: border-box;
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
    box-sizing: border-box;
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
</style>