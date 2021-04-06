const state = {
    filterSettings: {
        contractTable: localStorage.getItem('filterSettings.contractTable') || 1,
    },
    tableHeaderSettings: {
        contractTable: JSON.parse(localStorage.getItem('tableHeaderSettings.contractTable')) || {},
    }
}
const mutations = {
    SET_FILTER_SETTINGS : (state, payload) => {
        let setting = payload
        localStorage.setItem('filterSettings.' + setting.name, setting.value)
        state.filterSettings[setting.name] = setting.value
    },
    SET_TABLE_HEADER_SETTINGS : (state, payload) => {
        let setting = payload

        Object.assign(state.tableHeaderSettings[setting.name], setting.value)

        localStorage.setItem('tableHeaderSettings.' + setting.name, JSON.stringify(state.tableHeaderSettings[setting.name]))
    }
}
const getters = {
    FILTER_SETTINGS : state => {
        return state.filterSettings
    },
    TABLE_HEADER_SETTINGS : state => {
        return state.tableHeaderSettings
    }
}
const actions = {
    SET_FILTER_SETTINGS : (context, payload) => {
        context.commit('SET_FILTER_SETTINGS', payload)
    },
    SET_TABLE_HEADER_SETTINGS : (context, payload) => {
        context.commit('SET_TABLE_HEADER_SETTINGS', payload)
    },
}

export default {
    state, getters, mutations, actions
}
