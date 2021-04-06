import {API_GET_USER} from "../constants";

const state = {
    user : {
        id: '',
        name: '',
        email: '',
        phone: '',
        role: []
    }
}
const mutations = {
    SET_USER : (state, payload) => {
        state.user = payload
    }
}
const getters = {
    USER : state => {
        return state.user;
    }
}
const actions = {
    GET_USER :  async (context, payload) => {
        const response = await api.call('get', API_GET_USER)
        context.commit('SET_USER', response.data)
    },
    UPDATE_USER(context, payload) {
        context.commit('SET_USER', payload)
    }
}

export default {
    state, getters, mutations, actions
}
