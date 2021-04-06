import Vue from "vue"
import Vuex from "vuex"
import user from "./user"
import referencePropertiesList from "./referencePropertiesList"
import notifications from "./notifications"
import userLocalSettings from "./userLocalSettings"
Vue.use(Vuex)

export default new Vuex.Store({
    state: {},
    mutations: {},
    actions: {},
    modules: {
        user,
        userLocalSettings,
        referencePropertiesList,
        notifications
    }
})
