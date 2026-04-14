<template>
    <div>
        <div class="rec">
            <h1 class="title">Личный кабинет</h1>
            <div class="profile-content">
                <div class="section">
                    <h2 class="section-title">Личные данные</h2>
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" class="form-control" v-model="form.first_name">
                    </div>
                    <div class="form-group">
                        <label>Фамилия</label>
                        <input type="text" class="form-control" v-model="form.last_name">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" v-model="form.email" disabled>
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="text" class="form-control" v-model="form.phone">
                    </div>
                    <div class="form-group">
                        <label>Валюта по умолчанию</label>
                        <select class="form-control" v-model="form.default_currency">
                            <option value="RUB">RUB (Рубль)</option>
                            <option value="USD">USD (Доллар)</option>
                            <option value="EUR">EUR (Евро)</option>
                        </select>
                    </div>
                    <button class="save-btn" @click="updateProfile">Сохранить изменения</button>
                </div>
                <div class="section">
                    <h2 class="section-title">Сменить пароль</h2>
                    <div class="form-group">
                        <label>Текущий пароль</label>
                        <input type="password" class="form-control" v-model="passwordForm.current_password">
                    </div> 
                    <div class="form-group">
                        <label>Новый пароль</label>
                        <input type="password" class="form-control" v-model="passwordForm.new_password">
                    </div>   
                    <div class="form-group">
                        <label>Подтвердите новый пароль</label>
                        <input type="password" class="form-control" v-model="passwordForm.new_password_confirmation">
                    </div>
                    <button class="save-btn" @click="changePassword">Сменить пароль</button>
                </div>
                <div class="section">
                    <button class="logout-btn" @click="logout">Выйти из аккаунта</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'Profile',
    
    data() {
        return {
            form: {
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                default_currency: 'RUB',
            },
            passwordForm: {
                current_password: '',
                new_password: '',
                new_password_confirmation: '',
            }
        }
    },
    
    mounted() {
        this.getProfile();
    },
    
    methods: {
        getProfile() {
            axios.get('/api/profile').then(res => {
                this.form = {
                    first_name: res.data.first_name,
                    last_name: res.data.last_name,
                    email: res.data.email,
                    phone: res.data.phone || '',
                    default_currency: res.data.default_currency || 'RUB',
                };
            });
        },
        
        updateProfile() {
            axios.put('/api/profile', this.form).then(() => {
                alert('Профиль обновлен');
            });
        },
        
        changePassword() {
            if (this.passwordForm.new_password !== this.passwordForm.new_password_confirmation) {
                alert('Пароли не совпадают');
                return;
            }
            
            axios.post('/api/change-password', this.passwordForm).then(() => {
                alert('Пароль изменен');
                this.passwordForm = {
                    current_password: '',
                    new_password: '',
                    new_password_confirmation: '',
                };
            });
        },
        
        logout() {
            axios.post('/api/logout').then(() => {
                localStorage.removeItem('token');
                this.$router.push({ name: 'app.login' });
            });
        }
    }
}
</script>

<style scoped>
    .rec {
        width: 800px;
        min-height: 600px;
        background: rgba(15, 23, 41, 0.6);
        backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        position: absolute;
        top: 161px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        padding: 32px;
        box-sizing: border-box;
    }

    .title {
        font-size: 32px;
        font-family: 'Inter', sans-serif;
        color: #F8FAFC;
        font-weight: bold;
        margin-bottom: 32px;
        letter-spacing: -1px;
    }

    .profile-content {
        display: flex;
        flex-direction: column;
        gap: 32px;
    }

    .section {
        padding: 24px;
        background: rgba(22, 33, 60, 0.3);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .section-title {
        font-size: 18px;
        font-family: 'Inter', sans-serif;
        color: #94A3B8;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 12px;
        font-family: 'JetBrains Mono', monospace;
        color: #94A3B8;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-control {
        width: 100%;
        padding: 10px 13px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 14px;
        border-radius: 10px;
        background: rgba(22, 33, 60, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.1);
        outline: none;
        color: white;
        box-sizing: border-box;
    }

    .form-control:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .save-btn {
        width: 100%;
        height: 44px;
        margin-top: 16px;
        background: linear-gradient(to right, rgba(0, 212, 255, 1) 0%, rgba(190, 94, 237, 1) 100%);
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-family: 'Inter', sans-serif;
        font-weight: bold;
        color: white;
        cursor: pointer;
        transition: all 0.1s ease;
    }

    .save-btn:hover {
        box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
    }

    .logout-btn {
        width: 100%;
        height: 44px;
        background: rgba(220, 53, 69, 0.2);
        border: 1px solid rgba(220, 53, 69, 0.5);
        border-radius: 10px;
        font-size: 16px;
        font-family: 'Inter', sans-serif;
        font-weight: bold;
        color: #dc3545;
        cursor: pointer;
        transition: all 0.1s ease;
    }

    .logout-btn:hover {
        background: rgba(220, 53, 69, 0.3);
    }
</style>