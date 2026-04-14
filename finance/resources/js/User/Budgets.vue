<template>
    <div>
        <button class="add" @click="isModalVisible = true">Установить лимит</button>   
        <modal :visible="isModalVisible" @close="isModalVisible = false">
            <div class="mb-3">
                <select class="form-control" v-model="category_id">
                    <option :value="null" disabled>Выберите категорию</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" v-model="planned_amount" placeholder="Планируемая сумма">
            </div>
            <div class="mb-3">
                <input type="date" class="form-control" v-model="period_start">
                <small style="color: #94A3B8;">Начало периода</small>
            </div>
            <div class="mb-3">
                <input type="date" class="form-control" v-model="period_end">
                <small style="color: #94A3B8;">Конец периода</small>
            </div>
            <div class="mb-3">
                <input @click.prevent="addBudget" type="submit" class="btn btn-primary" value="Сохранить">
            </div>
        </modal>
        <div class="rec">
            <div class="filters">
                <input type="text" class="search-input" v-model="searchQuery" placeholder="Поиск по категории...">
            </div>
            <div class="chart-container" v-show="filteredBudgets.length > 0">
                <apexchart 
                    type="bar" 
                    :options="chartOptions" 
                    :series="chartSeries"
                    height="200"
                />
            </div>
            <div v-show="filteredBudgets.length === 0" class="no-data">
                Нет данных для отображения
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Категория</th>
                        <th>Лимит / Потрачено</th>
                        <th>Период</th>
                        <th>Остаток</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="budget in paginatedBudgets" :key="budget.id">
                        <template v-if="!budget.is_editing">
                            <td class="td">{{ budget.category ? budget.category.name : 'Удалена' }}</td>
                            <td class="td">
                                {{ budget.planned_amount }} / 
                                <span :style="{ color: budget.spent_amount > budget.planned_amount ? '#dc3545' : 'white' }">
                                    {{ budget.spent_amount }}
                                </span>
                            </td>
                            <td class="td">{{ budget.period_start }} — {{ budget.period_end }}</td>
                            <td class="td">
                                <strong :style="{ color: (budget.planned_amount - budget.spent_amount) < 0 ? '#dc3545' : '#28a745' }">
                                    {{ (budget.planned_amount - budget.spent_amount).toFixed(2) }}
                                </strong>
                            </td>
                            <td>
                                <button @click="budget.is_editing = true">✏️</button>
                                <button @click="deleteBudget(budget.id)" style="color: red;">🗑️</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="td">{{ budget.category.name }}</td>
                            <td class="td">
                                <input type="number" v-model="budget.planned_amount" class="form-control">
                                <input type="number" v-model="budget.spent_amount" class="form-control mt-1">
                            </td>
                            <td class="td">
                                <input type="date" v-model="budget.period_end" class="form-control">
                            </td>
                            <td></td>
                            <td>
                                <button @click="saveUpdate(budget)">💾</button>
                                <button @click="budget.is_editing = false">❌</button>
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
    name: 'Budgets',

    components: { 
        Modal,
        apexchart: VueApexCharts
    },

    data() {
        return {
            category_id: null,
            planned_amount: null,
            period_start: new Date().toISOString().substr(0, 7) + '-01',
            period_end: '',
            budgets: [],
            categories: [],
            isModalVisible: false,
            currentPage: 1,
            itemsPerPage: 5,
            searchQuery: '',
        }
    },

    computed: {
        filteredBudgets() {
            return this.budgets.filter(b => {
                const categoryName = b.category ? b.category.name : '';
                return categoryName.toLowerCase().includes(this.searchQuery.toLowerCase());
            });
        },

        paginatedBudgets() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredBudgets.slice(start, start + this.itemsPerPage);
        },

        totalPages() {
            return Math.ceil(this.filteredBudgets.length / this.itemsPerPage) || 1;
        },

        chartOptions() {
            return {
                chart: {
                    type: 'bar',
                    background: 'transparent',
                    toolbar: { show: false },
                    foreColor: '#94A3B8',
                    stacked: false
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '60%',
                        dataLabels: { position: 'top' }
                    }
                },
                colors: ['#00D4FF', '#dc3545'],
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(0) + ' ₽',
                    style: { fontSize: '11px', colors: ['#94A3B8'] }
                },
                xaxis: {
                    categories: this.filteredBudgets.map(b => b.category ? 
                        (b.category.name.length > 15 ? b.category.name.substring(0, 15) + '...' : b.category.name) 
                        : 'Удалена'),
                    labels: { 
                        style: { colors: '#94A3B8' },
                        rotate: -45
                    },
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
                tooltip: { 
                    theme: 'dark',
                    y: { formatter: (val) => val.toFixed(2) + ' ₽' }
                }
            };
        },

        chartSeries() {
            return [
                {
                    name: 'Лимит',
                    data: this.filteredBudgets.map(b => parseFloat(b.planned_amount) || 0)
                },
                {
                    name: 'Потрачено',
                    data: this.filteredBudgets.map(b => parseFloat(b.spent_amount) || 0)
                }
            ];
        }
    },

    watch: {
        searchQuery() { this.currentPage = 1; }
    },

    mounted() {
        this.getBudgets();
        this.getCategories();
    },

    methods: {
        getBudgets() {
            axios.get('/api/budgets').then(res => {
                this.budgets = res.data.map(b => ({ ...b, is_editing: false }));
            });
        },

        getCategories() {
            axios.get('/api/category').then(res => { 
                this.categories = res.data.filter(c => c.type === 'expense');
            });
        },

        addBudget() {
            axios.post('/api/budgets/create', {
                category_id: this.category_id,
                planned_amount: this.planned_amount,
                period_start: this.period_start,
                period_end: this.period_end
            }).then(() => {
                this.isModalVisible = false;
                this.getBudgets();
                this.resetForm();
            });
        },

        saveUpdate(budget) {
            axios.put(`/api/budgets/update/${budget.id}`, budget).then(() => {
                budget.is_editing = false;
            });
        },

        deleteBudget(id) {
            if (confirm('Удалить лимит?')) {
                axios.delete(`/api/budgets/delete/${id}`).then(() => {
                    this.budgets = this.budgets.filter(b => b.id !== id);
                });
            }
        },

        resetForm() {
            this.category_id = null;
            this.planned_amount = null;
            this.period_end = '';
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
    }

    .search-input {
    font-family: 'JetBrains Mono', monospace;
    font-size: 14px;
    border-radius: 10px;
    background: rgba(22, 33, 60, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    outline: none;
    padding: 10px 13px;
    color: white;
    width: 300px;
    }

    .search-input::placeholder {
    color: rgba(148, 163, 184, 0.5);
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
    padding: 6px 10px;
    margin: 0 4px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.1s ease;
    color: white;
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

    .mt-1 {
    margin-top: 8px;
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