<template>
    <div>
        <div class="conteiner">
            <router-link :to="{ name: 'user.accounts' }">
                <button class="func1">
                    <h1 class="name">Счета</h1>
                    <p class="desc">Управление финансовыми счетами</p>
                </button>
            </router-link>
            <router-link :to="{ name: 'user.categories' }">
                <button class="func2">
                    <h1 class="name">Категории</h1>
                    <p class="desc">Организуйте свою цифровую экосистему с помощью настраиваемых категорий</p>
                </button>
            </router-link>
            <router-link :to="{ name: 'user.operations' }">
                <button class="func3">
                    <h1 class="name">Операции</h1>
                    <p class="desc">Осуществляйте и отслеживайте финансовые операции с высокой точностью</p>
                </button>
            </router-link>
            <router-link :to="{ name: 'user.goals' }">
                <button class="func4">
                    <h1 class="name">Цели</h1>
                    <p class="desc">Определите стратегические финансовые этапы и отслеживайте прогресс</p>
                </button>
            </router-link>
            <router-link :to="{ name: 'user.budgets' }">
                <button class="func5">
                    <h1 class="name">Лимит</h1>
                    <p class="desc">Поддерживайте строгую финансовую дисциплину с помощью лимитов</p>
                </button>
            </router-link>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "UserMenu",

    mounted() {
        this.getData()
    },

    methods: {
        getData() {
            axios.get('/api/get')
            .then( res => {
                console.log(res);
            })
        }
    }
}
</script>

<style scoped>
    @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');
    @import url('https://rsms.me/inter/inter.css');
    .func1, .func2, .func3, .func4, .func5{
        position: absolute;
        width: 410.66px;
        height: 346px;
        border-radius: 16px;
        background: rgba(15, 23, 41, 0.4);
        border: 1px solid rgba(57, 62, 70, 0.4);
        backdrop-filter: blur(12px);
        overflow: hidden;
        transition: all 0.1s ease;
    }
    .func1:active, .func2:active, .func3:active, .func4:active, .func5:active{
        box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
        transform: translate(-155%, 10%);
    }
    .func1:hover, .func2:hover, .func3:hover, .func4:hover, .func5:hover{
        box-shadow: 0 0 20px rgba(6, 182, 212, 0.5);
    }
    .func1:hover .name, .func2:hover .name, .func3:hover .name, .func4:hover .name, .func5:hover .name{
        color: rgba(6, 182, 212, 0.5);
    }
    .conteiner{
        width: 1920px;
        height: 1080px;
    }
    .func1, .func4{
        left: 50%;
        transform: translate(-155%);
    }
    .func1:active, .func4:active{
        transform: translate(-155%, 10%);
    }
    .func2, .func5{
        left: 50%;
        transform: translate(-50%);
    }
    .func2:active, .func5:active{
        transform: translate(-50%, 10%);
    }
    .func3{
        left: 50%;
        transform: translate(55%);
    }
    .func3:active{
        transform: translate(55%, 10%);
    }
    .func4, .func5{
        top: 717px
    }
    .func1, .func2, .func3{
        top: 347.5px;
    }
    .name{
        font-family: 'JetBrains Mono', monospace;
        font-size: 20px;
        letter-spacing: -0.9px;
        position: absolute;
        top: 95px;
        left: 25px;
        color: #F2F2F2;
    }
    .desc{
        position: absolute;
        top: 167px;
        left: 25px;
        color: rgba(157, 163, 175, 0.7);
        font-family: 'Inter', sans-serif;
        font-size: 16px;
        letter-spacing: -0.16px;
        text-align: left;
    }
</style>