import VueRouter from "vue-router"

import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Registration from '../views/Registration.vue'
import Dashboard from '../views/Dashboard.vue'
import User from '../views/User.vue'
import Order from '../views/Order.vue'
import Profile from '../views/Profile.vue'
import Contract from '../views/Contract.vue'
import Prescription from '../views/Prescription.vue'
import Flat from '../views/Flat.vue'
import History from '../views/History.vue'
import Calendar from '../views/Calendar.vue'
import PasswordReset from '../views/PasswordReset.vue'
import TOVDGO from '../views/TOVDGO.vue'
import TOVentilation from '../views/TOVentilation.vue'
import Equipment from '../views/Equipment.vue'

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
            path: '/reset-password',
            name: 'passwordReset',
            component: PasswordReset
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
        // {
        //     path: '/orders/:order_id',
        //     name: 'orderPage',
        //     component: OrderPage,
        //     meta: { middlewareAuth: true }
        // },
        {
            path: '/contracts',
            name: 'contracts',
            component: Contract,
            meta: { title: 'Договоры', middlewareAuth: true }
        },
        {
            path: '/prescriptions',
            name: 'prescriptions',
            component: Prescription,
            meta: { title: 'Предписания', middlewareAuth: true }
        },
        {
            path: '/to-vdgo',
            name: 'to-vdgo',
            component: TOVDGO,
            meta: { title: 'ТО-ВДГО', middlewareAuth: true }
        },
        {
            path: '/to-ventilation',
            name: 'to-ventilation',
            component: TOVentilation,
            meta: { title: 'ТО вентканалов и дымоходов', middlewareAuth: true }
        },
        {
            path: '/addresses',
            name: 'addresses',
            component: Flat,
            meta: { title: 'Адреса', middlewareAuth: true }
        },
        {
            path: '/equipment',
            name: 'equipment',
            component: Equipment,
            meta: { title: 'Оборудование', middlewareAuth: true }
        },
        {
            path: '/history',
            name: 'history',
            component: History,
            meta: { title: 'История', middlewareAuth: true }
        },
        {
            path: '/calendar',
            name: 'calendar',
            component: Calendar,
            meta: { title: 'Календарь', middlewareAuth: true }
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
