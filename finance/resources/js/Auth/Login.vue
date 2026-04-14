<<template>
    <div>
        <div class="rectangle">
            <h1 class="desc">С возвращением</h1>
            <p class="desc2">Войдите в свой аккаунт ФинУчёт, чтобы продолжить мониторинг расходов, планирование целей и анализ вашего финансового здоровья в реальном времени.</p>
            <div class="gradient-box"></div>
            <div class="gradient-box2"></div>
            <div class="form">
                <input type="email" class="email" v-model="email" placeholder="user@gmail.com"></input>
                <input type="password" class="passw" v-model="password" placeholder="••••••••"></input>
                <button @click="login" class="login_button">Войти в систему</button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "Login",

    data() {
        return {
            email: null,
            password: null,
        }
    },

    methods: {
        login() {
            axios.get('/sanctum/csrf-cookie').then(response =>{
                axios.post('/api/login', 
                {
                    email: this.email,
                    password: this.password,
                })
                .then( res => {
                    localStorage.setItem('token', res.data['token'])
                    this.$router.push({name: 'user.main'})                    
                })
            })
        }
    }
    
}
</script>

<style scoped>
    .rectangle{
        position: absolute;
        top: 161px;
        left: 50%;
        transform: translate(-50%);
        width: 1152px;
        height: 538.75px;
        border-radius: 24px;
        background: rgba(15, 23, 41, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(24px);
        overflow: hidden;
    }
    .form{
        width: 558.83px;
        height: 241px;
        position: absolute;
        left: 536.16px;
        top: 240.75px;
    }
    .email, .passw{
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
        width: 269.41px;
        height: 36px;
    }
    .email::placeholder, .passw::placeholder{
        color: rgba(148, 163, 184, 0.3);
        position: absolute;
    }
    .email{
        top: 22px;
    }
    .passw{
        top: 22px;
        left: 289.4px;
    }
    .login_button{
        box-sizing: border-box;
        border: none;
        width: 200px;
        height: 44px;
        position: absolute;
        top: 197px;
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
    .login_button:active{
        box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
        transform: translateY(3px);
    }
    .login_button:hover{
        box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
    }
    .img{
        position: absolute;
        left: 39px;
        z-index: 1;
        user-select: none
    }
    .gradient-box{
        width: 256px;
        height: 256px;
        background: rgba(0, 212, 255, 0.2);
        border-radius: 50%;
        position: absolute;
        top: -127px;
        left: 980.33px;
        filter: blur(100px);
    }
    .gradient-box2{
        width: 320px;
        height: 320px;
        background: rgba(190, 94, 237, 0.15);
        border-radius: 50%;
        position: absolute;
        top: 324.42px;
        left: -79px;
        z-index: 0;
        filter: blur(120px);
    }
    .desc{
        font-size: 48px;
        font-family: 'Inter', sans-serif;
        color: #F8FAFC;
        font-weight: bold;
        position: absolute;
        top: 57px;
        left: 536.16px;
        letter-spacing: -3.36px;
        user-select: none
    }
    .desc2{
        font-size: 18px;
        font-family: 'Inter', sans-serif;
        color: #94A3B8;
        position: absolute;
        top: 126px;
        left: 536.16px;
        letter-spacing: -0.32px;
        width: 556.5px;
        user-select: none
    }
</style>