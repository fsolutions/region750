// Проверка возможности одной из операций из action
export const checkActionAllow = {
    methods: {
        checkActionAllow(operation) {
            if ((this.items && this.items.actionAllows && this.items.actionAllows.includes(operation))
            || (this.actionAllows && this.actionAllows.includes(operation))) {
                return true
            }
            return false
        },
    }
}

// Action создания и редактирования элемента в объекте items
// Необходимо наличие v-model="isSidebarOpen" ; this.tableApiUrl
export const actionCreateOrUpdateItem = {
    data() {
        return {
            editedItem: {},
            editIndex: -1,
            isSidebarOpen: false,
            validationErrors: '',
            savingProcess: false
        }
    },
    watch: {
        isSidebarOpen(value) {
            if (value == false) {
                this.closeSidePanelCallback()
            }
        }
    },
    mounted() {
    },
    methods: {
        createOrEditItemModal(index = -1) {
            // Before modal opened
            if (typeof this.beforeSidebarOpenedCallback === 'function') {
                this.beforeSidebarOpenedCallback()
            }

            if (index != -1) {
                this.editIndex = index
                this.editedItem = { ...this.items.data[index] }

                if (typeof this.onItemEditModalCallback === 'function') {
                    this.onItemEditModalCallback()
                }
            }

            this.isSidebarOpen = true

            // After sidebar opened
            if (typeof this.afterSidebarOpenedCallback === 'function') {
                this.afterSidebarOpenedCallback()
            }
        },
        createOrUpdateItem() {
            this.savingProcess = true
            if (typeof this.onCreateOrUpdateItemCallback === 'function') {
                this.onCreateOrUpdateItemCallback()
            }
            if (this.editIndex == -1) {
                api.call("post", this.tableApiUrl, this.editedItem).then(({data}) => {
                    this.items.data.unshift(data)

                    if (typeof this.onCreatedOrUpdatedCallback === 'function') {
                        this.onCreatedOrUpdatedCallback(data)
                    }
                    if (typeof this.updateParentData === 'function') {
                        this.updateParentData()
                    }

                    this.validationErrors = ''
                    this.savingProcess = false
                    this.closeSidePanelCallback()
                    this.makeToast('success')
                }).catch((response) => {
                    if (response.status == 422){
                        this.validationErrors = response.data.error
                    }
                })
            } else {
                api.call("put", `${this.tableApiUrl}/${this.editedItem.id}`, this.editedItem).then(({data}) => {
                    // this.items.data[this.editIndex] = { ...data }
                    Object.assign(this.items.data[this.editIndex], data)
                    if (typeof this.onCreatedOrUpdatedCallback === 'function') {
                        this.onCreatedOrUpdatedCallback(data)
                    }
                    this.editIndex = -1,
                    this.savingProcess = false
                    this.validationErrors = ''
                    this.closeSidePanelCallback(),
                    this.makeToast('success')
                }).catch((response) => {
                    if (response.status == 422){
                        this.validationErrors = response.data.error
                    }
                })
            }
        },
        closeSidePanelCallback() {
            if (!this.isSidebarOpen) {
                this.editIndex = -1
                if (typeof this.onSidePanelCallback === 'function') {
                    this.onSidePanelCallback() // clear custom fields of form
                }
            }
        },
        stopLoaders() {
            this.savingProcess = false
        }
    }
}
// Action удаления элемента из объекта items
// Необходимо наличие this.tableApiUrl
export const actionDeleteItem = {
    methods: {
        deleteItem(index) {
            let id = this.items.data[index].id

            this.$swal({
                title: 'Подтвердите удаление',
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: 'Нет',
                confirmButtonText: 'Да',
            }).then((result) => {
                if(result.isConfirmed){
                    api.call("delete", `${this.tableApiUrl}/${id}`).then(({data}) => {
                        this.items.data.splice(index, 1)

                        if (typeof this.onDeletedCallback === 'function') {
                            this.onDeletedCallback()
                        }
                    })
                }
            }).finally(() => {
                this.makeToast('danger')
            })
        },
    }
}

// Action копирования элемента из объекта items
// Необходимо наличие this.tableApiUrl
export const actionCopyItem = {
    methods: {
        copyItem(index) {
            let id = this.items.data[index].id

            this.$swal({
                title: 'Подтверждаете желание скопировать',
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: 'Нет',
                confirmButtonText: 'Да',
            }).then((result) => {
                if(result.isConfirmed){
                    api.call("get", `${this.tableApiUrl}/copy/${id}`).then(({data}) => {
                        this.items.data.unshift(data)

                        this.closeSidePanelCallback()
                    })
                }
            }).finally(() => {
                this.makeToast('success')
            })
        },
    }
}

// Action создание автоматического ТО события
// Необходимо наличие this.tableApiUrl
export const actionAutoTOItem = {
    data() {
        return {
            needAutoTO: false
        }
    },
    methods: {
        autoTOItem(index) {
            let id = this.items.data[index].id

            this.$swal({
                title: 'Подтверждаете желание создать автоматическое событие на следующее ТО',
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: 'Нет',
                confirmButtonText: 'Да',
            }).then((result) => {
                if(result.isConfirmed){
                    api.call("get", `${this.tableApiUrl}/auto-to/${id}`).then(({data}) => {
                        this.items.data.unshift(data)

                        this.closeSidePanelCallback()
                    })
                }
            }).finally(() => {
                this.needAutoTO = false
                this.makeToast('success')
            })
        },
    }
}

// Action просмотра карточки подробностей
// Необходимо наличие v-model="isSidebarOpenDetail" ; this.tableApiUrl
export const actionShowItem = {
    data() {
        return {
            detailedItem: {
                creator: {},
                type: {},
                contract_on_user: {},
                source: {},
                company: {},
                company_employee: {},
                client: {},
                status: {},
                order: {},
                service: {},
                service_for_partner: {},
                services_list: {},
                user: {},
                close_reason: {},
                documents: {},
                teo_manager: {},
                to_manager: {},
                optional_sales_manager: {},
                finance_substatuses: [],
                order_user: {},
                master: {},
                order_contract: {},
                order_prescription: {},
                prescription_contract: {},
                prescription_order: {},
                creator: {},
                contract_on_user: {},
                contract_to: {},
                contract_to_last: {},
                orders: {},
                prescriptions: {},
                to_contract: {},
                to_contract_for_user: {},
                region: {},
                city: {},
                street: {},
                flat: {}        
            },
            isSidebarOpenDetail: false,
            detailedItemIndex: -1
        }
    },
    methods: {
        showItem(index, tabSelector = '') {
            let id = this.items.data[index].id
            api.call("get", `${this.tableApiUrl}/${id}`).then(({data}) => {
                this.detailedItem = data
                this.isSidebarOpenDetail = true
                this.detailedItemIndex = index
                // If this element exist in table lets update
                if (typeof this.items !== 'undefined' && this.items.data[index]) {
                    this.items.data[index] = Object.assign(this.items.data[index], data)
                }
            }).finally(() => {
                if (tabSelector != '') {
                    // $(tabSelector).tab('show')
                    $(tabSelector)[0].click()
                }
            })
        },
    }
}

// Action обновления элемента по индексу
// Необходимо наличие v-model="isSidebarOpenDetail" ; this.tableApiUrl
export const actionRefreshItemByIndex = {
    data() {
        return {
        }
    },
    methods: {
        refreshItemByIndex(index) {
            let id = this.items.data[index].id
            api.call("get", `${this.tableApiUrl}/${id}`).then(({data}) => {
                // If this element exist in table lets update
                if (typeof this.items !== 'undefined' && this.items.data[index]) {
                    this.items.data[index] = Object.assign(this.items.data[index], data)
                }
            })
        },
    }
}

// Action обновления полей элемента по индексу
// Необходимо наличие this.tableApiUrl
export const actionUpdateItemByIndex = {
    data() {
        return {
        }
    },
    methods: {
        updateItemByIndex(index, updateData, apiUrl = '') {
            if (apiUrl == '') {
                apiUrl = this.tableApiUrl
            }
            let id = this.items.data[index].id
            api.call("put", `${apiUrl}/${id}`, updateData).then(({data}) => {
                this.items.data[index] = Object.assign(this.items.data[index], data)
            })
        },
    }
}
