<template>
    <div>
        <nav class="bar">
            <div class="logo">
                <router-link :to="{ name: 'app.main' }">ФинУчёт</router-link>
            </div>
            <ul class="links-center" v-if="token">
                <router-link :to="{ name: 'user.main' }">Главная</router-link>
            </ul>
            <ul class="links">
                <div v-if="!token">
                    <router-link :to="{ name: 'app.login' }" class="rout-links">Вход</router-link> 
                    <router-link :to="{ name: 'app.register' }" class="rout-links">Регистрация</router-link>
                </div>
                <div v-if="token">
                    <router-link :to="{ name: 'user.profile' }" class="profile-link">
                        <span class="user-name">{{ userName }}</span>
                        <span class="profile-icon">👤</span>
                    </router-link>
                    <router-link v-if="isAdmin" :to="{ name: 'admin.panel' }" class="admin-link">
                        ⚙️ Админ
                    </router-link>
                </div>
            </ul>
        </nav>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'NavBar',

    data() {
        return {
            token: null,
            user: null,
        }
    },

    computed: {
        userName() {
            if (this.user) {
                return `${this.user.first_name} ${this.user.last_name}`;
            }
            return '';
        },
        isAdmin() {
            return this.user && this.user.role && this.user.role.name === 'admin';
        }
    },

    watch: {
        $route() {
            this.getToken();
            if (this.token) {
                this.getUser();
            }
        }
    },

    mounted() {
        this.getToken();
        if (this.token) {
            this.getUser();
        }
    },

    methods: {
        getToken() {
            this.token = localStorage.getItem('token');
        },

        getUser() {
            axios.get('/api/profile').then(res => {
                this.user = res.data;
            });
        },
    }
}
</script>

<style scoped>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');
    @import url('https://rsms.me/inter/inter.css');
    .bar {
        background-color: #05080F;
        width: 100%;
        height: 65px;
        position: fixed;
        top: 0px;
        left: 0px;
        right: 0px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 208px;
        box-sizing: border-box;
    }

    .logo {
        list-style: none;
        font-size: 25px;
        font-family: 'Inter', sans-serif;
        user-select: none;
    }

    .logo a {
        text-decoration: none;
        color: #94A3B8;
    }

    .links-center {
        list-style: none;
        font-family: 'Inter', sans-serif;
        display: flex;
        gap: 40px;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        user-select: none;
    }

    .links-center a {
        text-decoration: none;
        color: #94A3B8;
        font-size: 16px;
        font-weight: 500;
        transition: color 0.2s;
    }

    .links-center a:hover {
        color: #00D4FF;
    }

    .links {
        list-style: none;
        font-family: 'Inter', sans-serif;
        user-select: none;
    }

    .rout-links {
        margin-left: 24px;
    }

    .links a {
        text-decoration: none;
        color: #94A3B8;
        font-size: 14px;
        transition: color 0.2s;
    }

    .links a:hover {
        color: #00D4FF;
    }

    .profile-link {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .user-name {
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .profile-icon {
        font-size: 18px;
    }

    .admin-link {
        color: #BE5EED !important;
        margin-left: 8px;
    }

    .admin-link:hover {
        color: #00D4FF !important;
    }
</style>