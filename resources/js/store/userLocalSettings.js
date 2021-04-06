const state = {
    filterSettings: {
        companyTable: localStorage.getItem('filterSettings.companyTable') || 1,
        orderTable: localStorage.getItem('filterSettings.orderTable') || '3,0',
        orderServiceTable: localStorage.getItem('filterSettings.orderServiceTable') || 1,
        financeTable: localStorage.getItem('filterSettings.financeTable') || 2,
        financeRequestsTable: localStorage.getItem('filterSettings.financeRequestsTable') || 4
    },
    tableHeaderSettings: {
        companyTable: JSON.parse(localStorage.getItem('tableHeaderSettings.companyTable')) || {},
        orderTable: JSON.parse(localStorage.getItem('tableHeaderSettings.orderTable')) || {},
        financeTable: JSON.parse(localStorage.getItem('tableHeaderSettings.financeTable')) || {},
        financeRequestsTable: JSON.parse(localStorage.getItem('tableHeaderSettings.financeRequestsTable')) || {},
        orderServiceTable_22: JSON.parse(localStorage.getItem('tableHeaderSettings.orderServiceTable_22')) || {},
        orderServiceTable_23: JSON.parse(localStorage.getItem('tableHeaderSettings.orderServiceTable_23')) || {},
        orderServiceTable_24: JSON.parse(localStorage.getItem('tableHeaderSettings.orderServiceTable_24')) || {},
        orderServiceTable_25: JSON.parse(localStorage.getItem('tableHeaderSettings.orderServiceTable_25')) || {},
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
