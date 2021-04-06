import {API_REFERENCE_PROPERTIES} from "../constants";

const state = {
    list : null,
    grouped : null,
}
const mutations = {
    SET_REFERENCE_PROPERTIES : (state, payload) => {
        state.list = payload
    },
    SET_GROUPED_REFERENCE_PROPERTIES : (state, payload) => {
        state.grouped = payload
    }
}
const getters = {
    REFERENCE_PROPERTIES : state => {
        return state.list;
    },
    GROUPED_REFERENCE_PROPERTIES : state => {
        return state.grouped;
    }
}
const actions = {
    GET_REFERENCE_PROPERTIES : async (context, payload) => {
        if (!state.list) {
            const response = await api.call('get', API_REFERENCE_PROPERTIES + `?all_references=true`)
            context.commit('SET_REFERENCE_PROPERTIES', response.data)
        }
    },
    GET_GROUPED_REFERENCE_PROPERTIES : async (context, payload) => {
        if (!state.grouped) {
            const response = await api.call('get', API_REFERENCE_PROPERTIES + `?all_references_group_by_reference_id=true`)
            context.commit('SET_GROUPED_REFERENCE_PROPERTIES', response.data)
        }
    }
}

export default {
    state, getters, mutations, actions
}
