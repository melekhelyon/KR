<template>
    <div>
        <div class="admin-panel">
            <h1 class="title">Админ панель</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value">{{ stats.users_count }}</div>
                    <div class="stat-label">Пользователей</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💳</div>
                    <div class="stat-value">{{ stats.accounts_count }}</div>
                    <div class="stat-label">Счетов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📂</div>
                    <div class="stat-value">{{ stats.categories_count }}</div>
                    <div class="stat-label">Категорий</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value">{{ stats.operations_count }}</div>
                    <div class="stat-label">Операций</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-value">{{ stats.goals_count }}</div>
                    <div class="stat-label">Целей</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-value">{{ formatMoney(stats.total_balance) }}</div>
                    <div class="stat-label">Общий баланс</div>
                </div>
            </div>
            <div class="charts-row">
                <div class="chart-box">
                    <h3>Новые пользователи</h3>
                    <apexchart type="line" :options="usersChartOptions" :series="usersChartSeries" height="250" />
                </div>
                <div class="chart-box">
                    <h3>Доходы/Расходы</h3>
                    <apexchart type="line" :options="operationsChartOptions" :series="operationsChartSeries" height="250" />
                </div>
            </div>
            <div class="users-section">
                <h2>Управление пользователями</h2>
                <div class="filters">
                    <input type="text" class="search-input" v-model="searchQuery" placeholder="Поиск..." @input="searchUsers">
                </div>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Роль</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id">
                            <td>{{ user.id }}</td>
                            <td>{{ user.first_name }} {{ user.last_name }}</td>
                            <td>{{ user.email }}</td>
                            <td>{{ user.phone || '—' }}</td>
                            <td>
                                <select v-model="user.role_id" @change="updateRole(user)" class="role-select">
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </td>
                            <td>{{ formatDate(user.created_at) }}</td>
                            <td>
                                <button @click="deleteUser(user.id)" class="delete-btn" :disabled="user.id === currentUserId">
                                    🗑️
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="pagination">
                    <button @click="prevPage" :disabled="currentPage === 1">←</button>
                    <span>{{ currentPage }} / {{ totalPages }}</span>
                    <button @click="nextPage" :disabled="currentPage >= totalPages">→</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import VueApexCharts from "vue3-apexcharts";

export default {
    name: 'AdminPanel',

    components: { apexchart: VueApexCharts },

    data() {
        return {
            stats: {
                users_count: 0,
                accounts_count: 0,
                categories_count: 0,
                operations_count: 0,
                goals_count: 0,
                budgets_count: 0,
                total_balance: 0,
                users_by_date: [],
                operations_by_date: [],
            },
            users: [],
            roles: [],
            currentUserId: null,
            searchQuery: '',
            currentPage: 1,
            totalPages: 1,
            searchTimeout: null,
        }
    },
    
    computed: {
        usersChartOptions() {
            return {
                chart: { 
                    background: 'transparent', 
                    toolbar: { show: false }, 
                    foreColor: '#94A3B8' 
                },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#00D4FF'],
                xaxis: { 
                    categories: this.stats.users_by_date?.map(d => d.date) || [],
                    labels: { style: { colors: '#94A3B8' } }
                },
                yaxis: {
                    labels: { style: { colors: '#94A3B8' } }
                },
                theme: { mode: 'dark' },
                tooltip: { theme: 'dark' }
            };
        },

        usersChartSeries() {
            return [{
                name: 'Пользователи',
                data: this.stats.users_by_date?.map(d => d.count) || []
            }];
        },

        operationsChartOptions() {
            return {
                chart: { 
                    background: 'transparent', 
                    toolbar: { show: false }, 
                    foreColor: '#94A3B8' 
                },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#28a745', '#dc3545'],
                xaxis: { 
                    categories: this.stats.operations_by_date?.map(d => d.date) || [],
                    labels: { style: { colors: '#94A3B8' } }
                },
                yaxis: {
                    labels: { style: { colors: '#94A3B8' } }
                },
                theme: { mode: 'dark' },
                tooltip: { theme: 'dark' }
            };
        },
        
        operationsChartSeries() {
            return [
                { name: 'Доходы', data: this.stats.operations_by_date?.map(d => parseFloat(d.income) || 0) || [] },
                { name: 'Расходы', data: this.stats.operations_by_date?.map(d => parseFloat(d.expense) || 0) || [] }
            ];
        }
    },
    
    mounted() {
        this.getCurrentUser();
        this.getRoles();
        this.getStats();
        this.getUsers();
    },
    
    methods: {
        getCurrentUser() {
            axios.get('/api/profile').then(res => {
                this.currentUserId = res.data.id;
            });
        },
        
        getStats() {
            axios.get('/api/admin/stats').then(res => {
                this.stats = res.data;
            });
        },
        
        getRoles() {
            axios.get('/api/admin/roles').then(res => {
                this.roles = res.data;
            });
        },
        
        getUsers(page = 1) {
            axios.get('/api/admin/users', {
                params: { page, search: this.searchQuery }
            }).then(res => {
                this.users = res.data.data || [];
                this.currentPage = res.data.current_page || 1;
                this.totalPages = res.data.last_page || 1;
            });
        },
        
        searchUsers() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.getUsers(1);
            }, 300);
        },
        
        updateRole(user) {
            axios.put(`/api/admin/users/${user.id}/role`, {
                role_id: user.role_id
            }).then(() => {
                alert('Роль обновлена');
            });
        },
        
        deleteUser(id) {
            if (!confirm('Удалить пользователя? Все его данные будут потеряны!')) return;
            
            axios.delete(`/api/admin/users/${id}`).then(() => {
                this.getUsers(this.currentPage);
                this.getStats();
            });
        },
        
        prevPage() {
            if (this.currentPage > 1) {
                this.getUsers(this.currentPage - 1);
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.getUsers(this.currentPage + 1);
            }
        },
        
        formatMoney(value) {
            return parseFloat(value || 0).toFixed(2) + ' ₽';
        },
        
        formatDate(date) {
            return new Date(date).toLocaleDateString('ru-RU');
        }
    }
}
</script>

<style scoped>
    .admin-panel {
        padding: 100px 40px 40px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .title {
        font-size: 36px;
        font-family: 'Inter', sans-serif;
        color: #F8FAFC;
        margin-bottom: 32px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: rgba(15, 23, 41, 0.6);
        backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
    }

    .stat-icon {
        font-size: 32px;
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 28px;
        font-family: 'JetBrains Mono', monospace;
        color: #F8FAFC;
        font-weight: bold;
    }

    .stat-label {
        font-size: 14px;
        color: #94A3B8;
        font-family: 'Inter', sans-serif;
    }

    .charts-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 32px;
    }

    .chart-box {
        background: rgba(15, 23, 41, 0.6);
        backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 20px;
    }

    .chart-box h3 {
        color: #94A3B8;
        font-family: 'Inter', sans-serif;
        margin-bottom: 16px;
    }

    .users-section {
        background: rgba(15, 23, 41, 0.6);
        backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 24px;
    }

    .users-section h2 {
        color: #F8FAFC;
        font-family: 'Inter', sans-serif;
        margin-bottom: 20px;
    }

    .filters {
        margin-bottom: 20px;
    }

    .search-input {
        width: 300px;
        padding: 10px 13px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 14px;
        border-radius: 10px;
        background: rgba(22, 33, 60, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        outline: none;
        color: white;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        color: #F8FAFC;
        font-family: 'Inter', sans-serif;
    }

    .users-table th,
    .users-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .users-table th {
        color: #94A3B8;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .role-select {
        padding: 6px 10px;
        background: rgba(22, 33, 60, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        color: white;
        font-family: 'Inter', sans-serif;
    }

    .delete-btn {
        background: transparent;
        border: 1px solid rgba(220, 53, 69, 0.5);
        border-radius: 6px;
        padding: 6px 10px;
        cursor: pointer;
        font-size: 14px;
    }

    .delete-btn:hover:not(:disabled) {
        background: rgba(220, 53, 69, 0.2);
    }

    .delete-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        margin-top: 24px;
    }

    .pagination button {
        background: rgba(22, 33, 60, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        padding: 8px 16px;
        color: white;
        cursor: pointer;
    }

    .pagination button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>