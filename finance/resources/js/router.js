import { createRouter, createWebHistory } from "vue-router";
import Main from './App.vue'
import Register from "./Auth/Register.vue";
import Login from "./Auth/Login.vue";
import UserMain from './User/Index.vue';
import Accounts from './User/Accounts.vue';
import Categories from './User/Categories.vue';
import Operations from './User/Operations.vue';
import Goals from './User/Goals.vue';
import Budgets from './User/Budgets.vue';
import Profile from "./User/Profile.vue";
import AdminPanel from "./Admin/AdminPanel.vue";

const routes = [
    {
        path: '/',
        component: Main,
        name: 'app.main'
    },
    {
        path: '/register',
        component: Register,
        name: 'app.register'
    },
    {
        path: '/login',
        component: Login,
        name: 'app.login'
    },
    {
        path: '/usermain',
        component: UserMain,
        name: 'user.main'
    },
    {
        path: '/accounts',
        component: Accounts,
        name: 'user.accounts'
    },
    {
        path: '/categories',
        component: Categories,
        name: 'user.categories'
    },
    {
        path: '/operations',
        component: Operations,
        name: 'user.operations'
    },
    {
        path: '/goals',
        component: Goals,
        name: 'user.goals'
    },
    {
        path: '/budgets',
        component: Budgets,
        name: 'user.budgets'
    },
    {
        path: '/admin',
        component: AdminPanel,
        name: 'admin.panel'
    },
    {
        path: '/profile',
        component: Profile,
        name: 'user.profile'
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from ) => {
    const token = localStorage.getItem('token')

    if (!token) {
        if (to.name !== 'app.login' && to.name !== 'app.register') {
            return { name: 'app.login' };
        } 
    }

    if (token) {
        if (to.name === 'app.login' || to.name === 'app.register') {
            return { name: 'user.main' };
        } 
    }

    return true;
});

export default router;