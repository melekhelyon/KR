<template>
    <div>
        <button class="add" @click="isModalVisible = true">Добавить</button>
        <modal :visible="isModalVisible" @close="isModalVisible = false">
            <div class="mb-3">
                <input type="text" class="form-control" v-model="name" placeholder="Название">
            </div>
            <div class="mb-3">
                <select class="form-control" v-model="type">
                    <option :value="null" disabled>Выберите тип</option>
                    <option value="income">income</option>
                    <option value="expense">expense</option>
                </select>
            </div>
            <div class="mb-3">
                <select class="form-control" v-model="parent_id">
                    <option :value="null">Без родителя</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                        {{ cat.name }}
                    </option>
                </select>
            </div>
            <div class="mb-3">
                <input @click.prevent="addCategory" type="submit" class="btn btn-primary" value="добавить">
            </div>
        </modal>
        <div class="rec">
            <div class="filters">
                <input type="text" class="search-input" v-model="searchQuery" placeholder="Поиск по названию...">
                <select class="filter-select" v-model="filterType">
                    <option value="">Все типы</option>
                    <option value="income">Доходы</option>
                    <option value="expense">Расходы</option>
                </select>
            </div>
            <div class="chart-container" v-show="filteredCategories.length > 0">
                <apexchart 
                    type="donut" 
                    :options="chartOptions" 
                    :series="chartSeries"
                    height="200"
                />
            </div>
            <div v-show="filteredCategories.length === 0" class="no-data">
                Нет данных для отображения
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Тип</th>
                        <th>Родитель</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in paginatedCategories" :key="category.id">
                        <template v-if="!category.is_editing">
                            <td class="td">{{ category.name }}</td>
                            <td class="td">{{ category.type === 'income' ? '📈 Доход' : '📉 Расход' }}</td>
                            <td class="td">{{ getParentName(category.parent_id) }}</td>
                            <td>
                                <button @click="category.is_editing = true">✏️</button>
                                <button @click="deleteCategory(category.id)" style="color: red;">🗑️</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="td"><input v-model="category.name" class="form-control"></td>
                            <td class="td">
                                <select v-model="category.type" class="form-control">
                                    <option value="income">income</option>
                                    <option value="expense">expense</option>
                                </select>
                            </td>
                            <td class="td">
                                <select v-model="category.parent_id" class="form-control">
                                    <option :value="null">Без родителя</option>
                                    <option v-for="cat in getAvailableParents(category)" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </td>
                            <td>
                                <button @click="updateCategory(category)">💾</button>
                                <button @click="category.is_editing = false">❌</button>
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
    name: 'Categories',

    components: { 
        Modal,
        apexchart: VueApexCharts
    },

    data() {
        return {
            name: null,
            type: null,
            parent_id: null,
            categories: [],
            isModalVisible: false,
            currentPage: 1,
            itemsPerPage: 5,
            searchQuery: '',
            filterType: '',
        }
    },

    computed: {
        filteredCategories() {
            return this.categories.filter(cat => {
                const matchesSearch = cat.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesType = !this.filterType || cat.type === this.filterType;
                return matchesSearch && matchesType;
            });
        },

        paginatedCategories() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredCategories.slice(start, start + this.itemsPerPage);
        },

        totalPages() {
            return Math.ceil(this.filteredCategories.length / this.itemsPerPage) || 1;
        },

        chartOptions() {
            return {
                chart: {
                    type: 'donut',
                    background: 'transparent',
                    foreColor: '#94A3B8'
                },
                labels: ['Доходы', 'Расходы'],
                colors: ['#28a745', '#dc3545'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Всего',
                                    color: '#94A3B8'
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: { colors: ['#fff'] }
                },
                legend: {
                    labels: { colors: '#94A3B8' },
                    position: 'bottom'
                },
                theme: { mode: 'dark' },
                tooltip: { theme: 'dark' }
            };
        },

        chartSeries() {
            const incomeCount = this.filteredCategories.filter(c => c.type === 'income').length;
            const expenseCount = this.filteredCategories.filter(c => c.type === 'expense').length;
            return [incomeCount, expenseCount];
        }
    },

    watch: {
        searchQuery() { this.currentPage = 1; },
        filterType() { this.currentPage = 1; }
    },

    mounted() {
        this.getCategories();
    },

    methods: {
        getAvailableParents(currentCategory) {
            const forbiddenIds = new Set();
            const findChildren = (parentId) => {
                forbiddenIds.add(parentId);
                this.categories.forEach(cat => {
                    if (cat.parent_id === parentId) {
                        findChildren(cat.id);
                    }
                });
            };

            findChildren(currentCategory.id);
            return this.categories.filter(cat => !forbiddenIds.has(cat.id));
        },

        getParentName(parentId) {
            if (!parentId) return '—';
            const parent = this.categories.find(c => c.id === parentId);
            return parent ? parent.name : '—';
        },

        addCategory() {
            axios.post('/api/category/create', {
                name: this.name,
                type: this.type,
                parent_id: this.parent_id,
            }).then(() => {
                this.isModalVisible = false;
                this.getCategories();
                this.name = this.type = this.parent_id = null;
            });
        },

        getCategories() {
            axios.get('/api/category').then(res => {
                this.categories = res.data.map(cat => { return { ...cat, is_editing: false }});
            });
        },

        updateCategory(category) {
            axios.put(`/api/category/update/${category.id}`, {
                name: category.name,
                type: category.type,
                parent_id: category.parent_id,
            }).then(() => {
                category.is_editing = false;
            });
        },

        deleteCategory(id) {
            if (confirm('Удалить эту категорию?')) {
                axios.delete(`/api/category/delete/${id}`).then(() => {
                    this.categories = this.categories.filter(cat => cat.id !== id);
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

    .search-input, .filter-select {
    font-family: 'JetBrains Mono', monospace;
    font-size: 14px;
    border-radius: 10px;
    background: rgba(22, 33, 60, 0.5);
    border: 1px solid rgba(255, 255, 255, 0.1);
    outline: none;
    padding: 10px 13px;
    color: white;
    width: 250px;
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
</style>