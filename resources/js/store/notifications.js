import {API_GET_NOTIFICATIONS_UNREAD} from "../constants";

const state = {
    notifications : []
}
const mutations = {
    SET_NOTIFICATIONS : (state, payload) => {
        state.notifications = payload
    }
}
const getters = {
    NOTIFICATIONS : state => {
        return state.notifications;
    }
}
const actions = {
    GET_NOTIFICATIONS :  async (context, payload) => {
        const response = await api.call('get', API_GET_NOTIFICATIONS_UNREAD)
        context.commit('SET_NOTIFICATIONS', response.data)
    }
}

export default {
    state, getters, mutations, actions
}
