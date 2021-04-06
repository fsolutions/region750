import VueRouter from "vue-router"

import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Registration from '../views/Registration.vue'
import Dashboard from '../views/Dashboard.vue'
import User from '../views/User.vue'
import Order from '../views/Order.vue'
import OrderPage from '../views/OrderPage.vue'
import Profile from '../views/Profile.vue'
import Contract from '../views/Contract.vue'
import Prescriptions from '../views/Prescriptions.vue'
import History from '../views/History.vue'

let routes = [
        {
            path: '/',
            name: 'home',
            component: Home,
        },
        {
            path: '/login',
            name: 'login',
            component: Login
        },
        {
            path: '/register',
            name: 'register',
            component: Registration
        },
        {
            path: '/logout',
            name: 'logout',
            meta: { logout: true }
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: Dashboard,
            meta: { title: 'Панель информации', middlewareAuth: true }
        },
        {
            path: '/users',
            name: 'users',
            component: User,
            meta: { title: 'Пользователи', middlewareAuth: true }
        },
        {
            path: '/profile',
            name: 'profile',
            component: Profile,
            meta: { title: 'Профиль', middlewareAuth: true }
        },
        {
            path: '/orders',
            name: 'orders',
            component: Order,
            meta: { title: 'Обращения', middlewareAuth: true }
        },
        {
            path: '/orders/:order_id',
            name: 'orderPage',
            component: OrderPage,
            meta: { middlewareAuth: true }
        },
        {
            path: '/contracts',
            name: 'contracts',
            component: Contract,
            meta: { title: 'Договоры', middlewareAuth: true }
        },
        {
            path: '/prescriptions',
            name: 'prescriptions',
            component: Prescriptions,
            meta: { title: 'Предписания', middlewareAuth: true }
        },
        {
            path: '/history',
            name: 'history',
            component: History,
            meta: { title: 'История', middlewareAuth: true }
        },
        // {
        //     path: '/contracts/:order_id',
        //     name: 'contractPage',
        //     component: ContractPage,
        //     meta: { middlewareAuth: true }
        // },
    ]

const router = new VueRouter({
    mode: 'history',
    routes
})

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.logout)) {
        auth.logout()
    }

    if (to.matched.some(record => record.meta.middlewareAuth)) {
        if (!auth.check()) {
            next({
                path: '/login',
                query: { redirect: to.fullPath }
            })
            return
        }
    }

    next()
})

export default router
