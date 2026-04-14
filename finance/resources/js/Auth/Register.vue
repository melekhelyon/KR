<template>
    <div>
        <div class="gradient-box"></div>
        <div class="rectangle">
            <h1 class="desc">Создайте профиль</h1>
            <p class="desc2">Присоединяйтесь к ФинУчёт для полного контроля над вашим капиталом</p>
            <div class="tr-shape"></div>
            <div class="tl-shape"></div>
            <div class="br-shape"></div>
            <div class="bl-shape"></div>
            <div class="form">
                <p class="desc3">NAME</p>
                <input type="name" class="inputbox-name" v-model="name" placeholder="Иван"></input>
                <p class="desc8">Как к вам обращаться в системе.</p>
                <p class="desc4">SURNAME</p>
                <input type="surname" class="inputbox-surname" v-model="last_name" placeholder="Иванов"></input>
                <p class="desc9">Уникальный идентификатор для входа.</p>
                <p class="desc5">SECURITY KEY</p>
                <input type="password" class="inputbox-pass" v-model="password" placeholder="••••••••"></input>
                <p class="desc6">EMAIL ADDRESS</p>
                <input type="email" class="inputbox-email" v-model="email" placeholder="ivan@gmail.com"></input>
                <p class="desc7">PHONE NUMBER</p>
                <input type="tel" class="inputbox-phone" v-model="phone" @input="formatPhone" placeholder="+7 (999) 000-00-00" maxlength="18">
                <div class="error-message" v-if="error">{{ error }}</div>
                <button class="register_button" @click="register">Создать аккаунт</button>
            </div>
            <div class="dd-shape"></div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "Register",

    data(){
        return{
            name: '',
            surname: '',
            password: '',
            email: '',
            phone: '',
            error: '',
        }
    },

    methods: {
        formatPhone() {
            let value = this.phone.replace(/\D/g, '');
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            if (value.length > 0) {
                if (value.length <= 1) {
                    value = '+7';
                } else {
                    if (value[0] !== '7') {
                        value = '7' + value;
                    }
                    let formatted = '+7';
                    if (value.length > 1) {
                        formatted += ' (' + value.substring(1, Math.min(4, value.length));
                    }
                    if (value.length >= 4) {
                        formatted += ') ' + value.substring(4, Math.min(7, value.length));
                    }
                    if (value.length >= 7) {
                        formatted += '-' + value.substring(7, Math.min(9, value.length));
                    }
                    if (value.length >= 9) {
                        formatted += '-' + value.substring(9, 11);
                    }
                    value = formatted;
                }
            }   
            this.phone = value;
        },

        validateForm() {
            this.error = '';
            
            if (!this.name || this.name.length < 2) {
                this.error = 'Имя должно содержать минимум 2 символа';
                return false;
            }
            
            if (!this.last_name || this.last_name.length < 2) {
                this.error = 'Фамилия должна содержать минимум 2 символа';
                return false;
            }
            
            if (!this.password || this.password.length < 8) {
                this.error = 'Пароль должен содержать минимум 8 символов';
                return false;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!this.email || !emailRegex.test(this.email)) {
                this.error = 'Введите корректный email адрес';
                return false;
            }
            
            if (!this.phone || this.phone.length < 10) {
                this.error = 'Введите корректный номер телефона';
                return false;
            }
            
            return true;
        },

        register(){
            if (!this.validateForm()) {
                return;
            }
            
            
            axios.get('/sanctum/csrf-cookie').then(response => {
                axios.post('/api/register', {
                    first_name: this.name,
                    last_name: this.last_name,
                    password: this.password,  
                    email: this.email, 
                    phone: this.phone,
                })
                .then(res => {
                    localStorage.setItem('token', res.data['token'])
                    this.$router.push({name: 'user.main'})
                })
                .catch(err => {
                    if (err.response && err.response.status === 422) {
                        this.error = 'Пользователь с таким email уже существует';
                    } else {
                        this.error = 'Ошибка регистрации. Попробуйте позже';
                    }
                })
            }).catch(() => {
                this.error = 'Ошибка соединения с сервером';
            });
        }
    }
}
</script>

<style scoped>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');
    @import url('https://rsms.me/inter/inter.css');
    .rectangle{
        background-color: rgba(15, 23, 41, 0.3);
        backdrop-filter: blur(24px);
        width: 672px;
        height: 762px;
        position: absolute;
        top: 227px;
        left: 50%;
        transform: translate(-50%);
        border-radius: 16px;
        z-index: 1;
    }
    .desc{
        font-size: 36px;
        font-family: 'Inter', sans-serif;
        color: #F8FAFC;
        font-weight: bold;
        position: absolute;
        top: 134px;
        left: 184.73px;
        letter-spacing: -2.52px;
        user-select: none
    }
    .desc2{
        font-size: 16px;
        font-family: 'Inter', sans-serif;
        color: #94A3B8;
        position: absolute;
        top: 190px;
        left: 56px;
        letter-spacing: -0.32px;
        user-select: none
    }
    .desc3, .desc4, .desc5, .desc6, .desc7{
        font-size: 12px;
        font-family: 'JetBrains Mono', monospace;
        color: #94A3B8;
        position: absolute;
        letter-spacing: 0.36px;
        user-select: none
    }
    .desc3{
        top: -16px;
    }
    .desc4{
        top: 92px;
    }
    .desc5{
        top: 200px;
    }
    .desc6{
        top: 284px;
    }
    .desc7{
        top: 284px;
        left: 324px;
    }
    .desc8, .desc9{
        font-size: 12px;
        font-family: 'Inter', sans-serif;
        color: #94A3B8;
        opacity: 70%;
        position: absolute;
        letter-spacing: -0.32px;
        user-select: none
    }
    .desc8{
        top: 52px;
    }
    .desc9{
        top: 160px;
    }
    .tr-shape{
        width: 64px;
        height: 64px;
        border-right: 2px solid rgba(0, 212, 255, 0.4);
        border-top: 2px solid rgba(0, 212, 255, 0.4);
        border-top-right-radius: 16px;
        opacity: 50%;
        position: absolute;
        top: 0px;
        left: 605px;
    }
    .tl-shape{
        width: 64px;
        height: 64px;
        border-left: 2px solid rgba(0, 212, 255, 0.4);
        border-top: 2px solid rgba(0, 212, 255, 0.4);
        border-top-left-radius: 16px;
        opacity: 50%;
        position: absolute;
        top: 0px;
        left: 0px;
    }
    .br-shape{
        width: 64px;
        height: 64px;
        border-right: 2px solid rgba(0, 212, 255, 0.4);
        border-bottom: 2px solid rgba(0, 212, 255, 0.4);
        border-bottom-right-radius: 16px;
        opacity: 50%;
        position: absolute;
        top: 696px;
        left: 605px;
    }
    .bl-shape{
        width: 64px;
        height: 64px;
        border-left: 2px solid rgba(0, 212, 255, 0.4);
        border-bottom: 2px solid rgba(0, 212, 255, 0.4);
        border-bottom-left-radius: 16px;
        opacity: 50%;
        position: absolute;
        top: 696px;
        left: 0px;
    }
    .dd-shape{
        width: 64px;
        height: 64px;
        border-right: 3px solid #00D4FF;
        border-top: 3px solid #00D4FF;
        border-top-right-radius: 16px;
        opacity: 50%;
        position: absolute;
        top: 2000px;
        left: 605px;
    }
    .gradient-box{
        width: 1920px;
        height: 1200px;
        background: radial-gradient(circle at top, rgba(190, 94, 237, 1) 0%, rgba(190, 94, 237, 0) 50%);
        border-radius: 0 0 100px 100px;
        position: absolute;
        top: 65px;
        left: 50%;
        transform: translate(-50%);
        opacity: 15%;
    }
    .form{
        width: 624px;
        height: 464px;
        position: absolute;
        top: 268px;
        left: 24px;
    }
    .inputbox-name, .inputbox-surname, .inputbox-pass, .inputbox-email, .inputbox-phone{
        font-family: 'JetBrains Mono', monospace;
        font-size: 14px;
        border-radius: 10px;
        background: rgba(22, 33, 60, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        outline: none;
        padding-left: 13px;
        letter-spacing: 1.12px;
        box-sizing: border-box;
        position: absolute;
        color: white;
    }
    .inputbox-name::placeholder, .inputbox-surname::placeholder, .inputbox-pass::placeholder, .inputbox-email::placeholder, .inputbox-phone::placeholder{
        color: rgba(148, 163, 184, 0.3);
        position: absolute;
    }
    .inputbox-name, .inputbox-surname, .inputbox-pass{
        width: 100%;
        height: 36px;
    }
    .inputbox-name{
        top: 24px;
    }
    .inputbox-surname{
        top: 132px;
    }
    .inputbox-pass{
        top: 240px;
    }
    .inputbox-email, .inputbox-phone{
        width: 300px;
        height: 36px;
    }
    .inputbox-email{
        left: 0;
        top: 324px;
    }
    .inputbox-phone{
        left: 324px;
        top: 324px;
    }
    .register_button{
        box-sizing: border-box;
        border: none;
        width: 100%;
        height: 56px;
        position: absolute;
        top: 408px;
        background: linear-gradient(to right, rgba(0, 212, 255, 1) 0%, rgba(190, 94, 237, 1) 100%);
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(6, 182, 212, 0.3);
        transition: all 0.1s ease;
        font-size: 18px;
        font-family: 'Inter', sans-serif;
        line-height: 28px;
        letter-spacing: -0.32;
        font-weight: bold;
    }
    .register_button:active{
        box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
        transform: translateY(3px);
    }
    .register_button:hover{
        box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
    }
    .error-message{
        position: absolute;
        top: 370px;
        width: 100%;
        color: #dc3545;
        font-size: 13px;
        font-family: 'Inter', sans-serif;
        text-align: center;
    }
</style>