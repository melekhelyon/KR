<template>
    <div>
        <button class="add" @click="isModalVisible = true">Добавить цель</button>
        <modal :visible="isModalVisible" @close="isModalVisible = false">
            <div class="mb-3">
                <input type="text" class="form-control" v-model="name" placeholder="Название цели">
            </div>
            <div class="mb-3">
                <textarea class="form-control" v-model="description" placeholder="Описание"></textarea>
            </div>
            <div class="mb-3">
                <input type="number" class="form-control" v-model="target_amount" placeholder="Целевая сумма">
            </div>
            <div class="mb-3">
                <input type="date" class="form-control" v-model="start_date" placeholder="Дата начала">
            </div>
            <div class="mb-3">
                <input type="date" class="form-control" v-model="target_date" placeholder="Дата завершения (необязательно)">
            </div>
            <div class="mb-3">
                <input @click.prevent="addGoal" type="submit" class="btn btn-primary" value="Добавить">
            </div>
        </modal>
        <div class="rec">
            <div class="filters">
                <input type="text" class="search-input" v-model="searchQuery" placeholder="Поиск по названию...">
                <select class="filter-select" v-model="filterStatus">
                    <option value="">Все статусы</option>
                    <option value="in_progress">В процессе</option>
                    <option value="achieved">Достигнута</option>
                    <option value="failed">Провалена</option>
                </select>
            </div>
            <div class="chart-container" v-show="filteredGoals.length > 0">
                <apexchart 
                    type="bar" 
                    :options="chartOptions" 
                    :series="chartSeries"
                    height="200"
                />
            </div>
            <div v-show="filteredGoals.length === 0" class="no-data">
                Нет данных для отображения
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Прогресс</th>
                        <th>Срок</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="goal in paginatedGoals" :key="goal.id" :class="{ 'row-failed': goal.status === 'failed' }">
                        <template v-if="!goal.is_editing">
                            <td class="td">{{ goal.name }}</td>
                            <td class="td">
                                <div class="progress-container">
                                    <button 
                                        class="btn-prog" 
                                        :disabled="goal.status === 'failed'" 
                                        @click="updateProgress(goal, -100)"
                                    >-</button>
                                    <span class="amount-text">{{ goal.current_amount }} / {{ goal.target_amount }}</span>
                                    <button 
                                        class="btn-prog" 
                                        :disabled="goal.status === 'failed'" 
                                        @click="updateProgress(goal, 100)"
                                    >+</button>
                                    <span class="percent-tag" :class="{ 'completed': getPercent(goal) >= 100 }">
                                        {{ getPercent(goal) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="td">{{ goal.target_date || 'Бессрочно' }}</td>
                            <td class="td">
                                <span :class="'status-' + goal.status">{{ getStatusName(goal.status) }}</span>
                            </td>
                            <td>
                                <button @click="goal.is_editing = true">✏️</button>
                                <button @click="deleteGoal(goal.id)" style="color: red;">🗑️</button>
                            </td>
                        </template>
                        <template v-else>
                            <td class="td"><input v-model="goal.name" class="form-control"></td>
                            <td class="td"><input type="number" v-model="goal.target_amount" class="form-control"></td>
                            <td class="td"><input type="date" v-model="goal.target_date" class="form-control"></td>
                            <td class="td">
                                <select v-model="goal.status" class="form-control">
                                    <option value="in_progress">in_progress</option>
                                    <option value="achieved">achieved</option>
                                    <option value="failed">failed</option>
                                </select>
                            </td>
                            <td>
                                <button @click="saveUpdate(goal)">💾</button>
                                <button @click="goal.is_editing = false">❌</button>
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
    name: 'Goals',

    components: { 
        Modal,
        apexchart: VueApexCharts
    },

    data() {
        return {
            name: null,
            description: '',
            target_amount: null,
            start_date: new Date().toISOString().substr(0, 10),
            target_date: null,
            goals: [],
            isModalVisible: false,
            currentPage: 1,
            itemsPerPage: 5,
            searchQuery: '',
            filterStatus: '',
        }
    },

    computed: {
        filteredGoals() {
            return this.goals.filter(g => {
                const matchesSearch = g.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesStatus = !this.filterStatus || g.status === this.filterStatus;
                return matchesSearch && matchesStatus;
            });
        },

        paginatedGoals() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredGoals.slice(start, start + this.itemsPerPage);
        },

        totalPages() {
            return Math.ceil(this.filteredGoals.length / this.itemsPerPage) || 1;
        },

        chartOptions() {
            return {
                chart: {
                    type: 'bar',
                    background: 'transparent',
                    toolbar: { show: false },
                    foreColor: '#94A3B8',
                    stacked: true
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 4,
                        columnWidth: '60%',
                        dataLabels: { position: 'top' }
                    }
                },
                colors: ['#00D4FF', '#BE5EED'],
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(0) + ' ₽',
                    style: { fontSize: '11px', colors: ['#94A3B8'] }
                },
                xaxis: {
                    categories: this.filteredGoals.map(g => g.name.length > 15 ? g.name.substring(0, 15) + '...' : g.name),
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
                    name: 'Текущий прогресс',
                    data: this.filteredGoals.map(g => parseFloat(g.current_amount) || 0)
                },
                {
                    name: 'Осталось',
                    data: this.filteredGoals.map(g => {
                        const remaining = parseFloat(g.target_amount) - parseFloat(g.current_amount);
                        return remaining > 0 ? remaining : 0;
                    })
                }
            ];
        }
    },

    watch: {
        searchQuery() { this.currentPage = 1; },
        filterStatus() { this.currentPage = 1; }
    },

    mounted() {
        this.getGoals();
    },

    methods: {
        getPercent(goal) {
            if (!goal.target_amount || goal.target_amount <= 0) return 0;
            const res = (goal.current_amount / goal.target_amount) * 100;
            return Math.min(Math.round(res), 100);
        },

        getStatusName(status) {
            const names = {
                'in_progress': 'В процессе',
                'achieved': 'Достигнута',
                'failed': 'Провалена'
            };
            return names[status] || status;
        },

        getGoals() {
            axios.get('/api/goals').then(res => {
                this.goals = res.data.map(g => ({ ...g, is_editing: false }));
            });
        },

        addGoal() {
            axios.post('/api/goals/create', {
                name: this.name,
                description: this.description,
                target_amount: this.target_amount,
                start_date: this.start_date,
                target_date: this.target_date
            }).then(() => {
                this.isModalVisible = false;
                this.getGoals();
                this.resetForm();
            });
        },

        updateProgress(goal, defaultStep) {
            if (goal.status === 'failed') return;
            const val = prompt("На сколько изменить сумму?", defaultStep);
            if (val === null || val === "") return;
            axios.post(`/api/goals/${goal.id}/progress`, { amount: parseFloat(val) })
                .then(res => {
                    goal.current_amount = res.data.current_amount;
                    goal.status = res.data.status;
                }).catch(err => {
                    if(err.response && err.response.status === 403) {
                        alert(err.response.data.message);
                    }
                });
        },

        saveUpdate(goal) {
            axios.put(`/api/goals/update/${goal.id}`, goal).then(() => {
                goal.is_editing = false;
            });
        },

        deleteGoal(id) {
            if (confirm('Удалить цель?')) {
                axios.delete(`/api/goals/delete/${id}`).then(() => {
                    this.goals = this.goals.filter(g => g.id !== id);
                });
            }
        },
        
        resetForm() {
            this.name = this.description = this.target_amount = this.target_date = null;
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

    .table tbody tr.row-failed {
    opacity: 0.6;
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

    .table button:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(0, 212, 255, 0.5);
    }

    .table button:disabled {
    opacity: 0.3;
    cursor: not-allowed;
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

    .progress-container {
    display: flex;
    align-items: center;
    gap: 10px;
    }

    .btn-prog {
    background: rgba(22, 33, 60, 0.5) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: white !important;
    cursor: pointer;
    padding: 4px 12px !important;
    border-radius: 6px !important;
    font-size: 18px !important;
    font-weight: bold;
    min-width: 36px;
    }

    .btn-prog:hover:not(:disabled) {
    background: rgba(0, 212, 255, 0.2) !important;
    border-color: rgba(0, 212, 255, 0.5) !important;
    }

    .amount-text {
    min-width: 100px;
    text-align: center;
    font-family: 'JetBrains Mono', monospace;
    font-size: 14px;
    }

    .percent-tag {
    font-weight: bold;
    margin-left: 8px;
    color: #ffc107;
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
    padding: 2px 8px;
    background: rgba(255, 193, 7, 0.1);
    border-radius: 20px;
    }

    .percent-tag.completed {
    color: #28a745;
    background: rgba(40, 167, 69, 0.1);
    }

    .status-achieved {
    color: #28a745;
    font-weight: bold;
    padding: 4px 10px;
    background: rgba(40, 167, 69, 0.1);
    border-radius: 20px;
    font-size: 12px;
    text-transform: uppercase;
    }

    .status-failed {
    color: #dc3545;
    font-weight: bold;
    padding: 4px 10px;
    background: rgba(220, 53, 69, 0.1);
    border-radius: 20px;
    font-size: 12px;
    text-transform: uppercase;
    }

    .status-in_progress {
    color: #00D4FF;
    font-weight: bold;
    padding: 4px 10px;
    background: rgba(0, 212, 255, 0.1);
    border-radius: 20px;
    font-size: 12px;
    text-transform: uppercase;
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