// Запрос к бэку на получение списка ролей
import {
    API_LIST_OF_ROLES
} from "../constants"

export const constantsUserRoles = {
    data() {
        return {
            rolesList: [],
            user: auth.user,
        }
    },
    mounted() {
        this.getRolesList()
    },
    methods: {
        getRolesList: async function() {
            const response = await api.call('get', API_LIST_OF_ROLES)
            this.rolesList = response.data
            if (!this.user.role.includes("administrator")) {
                delete this.rolesList[1]
                delete this.rolesList[3]
                delete this.rolesList[4]
                delete this.rolesList[5]
            }
        },
    }
}

export const typeOfCompany = {
    data() {
        return {
            typeList: [
                { text: 'Выберите тип компании', value: null, disabled: true },
                { text: 'Юридическое лицо', value: 'LEGAL' },
                { text: 'Индивидуальный предприниматель', value: 'INDIVIDUAL' },
            ],
        }
    },
    methods: {
    }
}

export const constantsAllReferenceProperties = {
    data() {
        return {
            referencePropertiesList: [],
        }
    },
    mounted() {
        this.getReferencePropertiesList()
    },
    methods: {
        getReferencePropertiesList: async function() {
            const response = await this.$store.dispatch('GET_REFERENCE_PROPERTIES')
            this.referencePropertiesList = this.$store.getters.REFERENCE_PROPERTIES
        },
    }
}
