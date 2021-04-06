/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')

import Vue from 'vue'
import { BootstrapVue, IconsPlugin, LayoutPlugin, ModalPlugin, CardPlugin, VBScrollspyPlugin, DropdownPlugin, TablePlugin } from 'bootstrap-vue'
import VueSweetalert2 from 'vue-sweetalert2'
import VueRouter from 'vue-router'
import VueBootstrapTypeahead from 'vue-bootstrap-typeahead'
import VueMask from 'v-mask'

Vue.use(BootstrapVue)
Vue.use(IconsPlugin)
Vue.use(LayoutPlugin)
Vue.use(ModalPlugin)
Vue.use(CardPlugin)
Vue.use(DropdownPlugin)
Vue.use(TablePlugin)

Vue.use(VueSweetalert2)

Vue.use(VBScrollspyPlugin)

Vue.use(VueRouter)

Vue.use(VueMask)

Vue.use(VueBootstrapTypeahead)

import Api from './services/api'
import Auth from './services/auth'
import store from "./store"
import routes from './router'
import VueMq from 'vue-mq'
import Multiselect from 'vue-multiselect'

Vue.use(VueMq, {
    breakpoints: {
        sm: 768,
        md: 992,
        lg: 1200,
        xl: Infinity,
    }
})

window.api = new Api()
window.auth = new Auth()

window.Event = new Vue // Global event manager in VUE instance

const moment = require('moment')
require('moment/locale/ru')

Vue.use(require('vue-moment'), {
    moment
})

Vue.prototype.moment = moment

Vue.component('vue-app', require('./views/App.vue').default)
Vue.component('navigation', require('./components/templates/Header.vue').default)
Vue.component('app-footer', require('./components/templates/Footer.vue').default)
Vue.component('sidebar-navigation', require('./components/templates/Sidebar.vue').default)
Vue.component('app-table', require('./components/tables/Table.vue').default)
Vue.component('vue-bootstrap-typeahead', VueBootstrapTypeahead)

// Selectors
Vue.component('multiselect', Multiselect)
Vue.component('select-of-properties', require('./components/references/SelectOfPropertiesByReference.vue').default)
Vue.component('select-user', require('./components/references/SelectOfUsersByRole.vue').default)

// Documents
Vue.component('create-documents', require('./components/documents/CreateDocuments.vue').default)
Vue.component('show-edit-documents', require('./components/documents/ShowOrEditDocuments.vue').default)

// ContextMenus
Vue.component('context-menu-headers', require('./components/contextmenu/ContextMenuHeaders.vue').default)

// Errors
Vue.component('validation-errors', require('./services/validationErrors.vue').default)

Vue.component('v-mask', VueMask)

Vue.filter('formattedDate', function (date) {
    let formatDate = moment(date).format('DD.MM.YYYY')

    if (formatDate == 'Invalid date') return "Не указана"
    return formatDate
})
Vue.filter('formattedDateTime', function (date) {
    let formatDate = moment(date).format('DD.MM.YYYY HH:mm')

    if (formatDate == 'Invalid date') return "Не указана"
    return formatDate
})

Vue.filter('toCurrency', function (value) {
    if (typeof value !== "number") {
        return value
    }
    let formatter = new Intl.NumberFormat('ru-RU', {
        minimumFractionDigits: 0
    })
    return formatter.format(value)
})

const app = new Vue({
    el: '#app',
    store,
    router: routes,
})
