<template>
    <div>
        <app-table
            v-if="onlyForBackOffice()"
            :name="'contract'"
            :typeOfTableFilter="'contracts'"
            :api="tableApiUrl"
            :items.sync="items"
            :isNeedSearch="true"
            :customAddButtonName="`Добавить договор`"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
            @openContractTOCreateModal="openContractTOCreateModal"
        ></app-table>

        <contract-cards
            v-if="!onlyForBackOffice()"
            :items.sync="items"
            :api="tableApiUrl"
            @show="showItem"
            @edit="createOrEditItemModal"
        ></contract-cards>

        <b-sidebar
            v-if="isSidebarOpen"
            v-model="isSidebarOpen"
            id="sidebar-right"
            right
            backdrop
            shadow
            width="85em"
            backdrop-variant="dark"
            ref="editItem"
            :title="modalTitle()"
            @hidden="closeSidePanelCallback()"
        >
            <div class="d-block">
                <validation-errors :errors="validationErrors" v-if="validationErrors"></validation-errors>

                <form @submit.prevent="createOrUpdateItem()" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="contract_number">Номер договора</label>
                            <input v-model="editedItem.contract_number" required type="text" class="form-control" id="contract_number">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="contract_address">Адрес объекта из договора</label>
                            <input v-model="editedItem.contract_address" required type="text" class="form-control" id="contract_address">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="contract_start_datetime">Дата заключения договора</label>
                            <input v-model="editedItem.contract_start_datetime" required type="date" class="form-control" id="contract_start_datetime">
                        </div>

                        <template v-if="onlyForBackOffice()">
                            <div class="form-group col-md-12">
                                <label>Пользователь, на которого зарегистрирован договор</label>
                                <select-user
                                    id="contract_on_user_id"
                                    :roles="`client`"
                                    :needNullElement="true"
                                    :selected="editedItem.contract_on_user_id"
                                    :selectedUser="editedItem.contract_on_user"
                                    @set="setUserOfContract"
                                ></select-user>
                            </div>       
                            <div class="form-group col-md-12">
                                <label for="role">Статус</label>
                                <b-form-select v-model="editedItem.status" required :options="statusList" id="status"></b-form-select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Комментарий (виден только коллегам)</label>
                                <b-form-textarea
                                    id="contract_comment"
                                    v-model="editedItem.contract_comment"
                                    placeholder="Если есть что отметить по контракту, отметьте."
                                    rows="3"
                                    max-rows="16"
                                ></b-form-textarea>
                            </div>                            
                        </template>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Сохранить<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                    </div>
                </form>
            </div>
        </b-sidebar>
        <b-sidebar
            v-if="isSidebarOpenDetail"
            v-model="isSidebarOpenDetail"
            id="sidebar-right"
            title="Детальная информация"
            right
            backdrop
            shadow
            width="85em"
            backdrop-variant="dark"
            no-close-on-backdrop
        >
            <contract-show-one
                :detailedItemIndex="detailedItemIndex"
                :detailedItem="detailedItem"
                @updateParentData="updateParentData"
            ></contract-show-one>
        </b-sidebar>
        <contract-to-create-edit
            v-if="operationForTO"
            :contractForTO="contractForTO"
            :contractForTOIndex="contractForTOIndex"
            :operationForTO="operationForTO"
            @clear="clearTOData"
        ></contract-to-create-edit>
  </div>
</template>

<script>
    import {API_CONTRACTS} from "../constants"

    import ContractCards from "../components/contracts/Cards"
    import ContractTOCreateOrEdit from "../components/contracts/ContractTOCreateOrEdit"
    import ContractShowOne from "../components/contracts/ContractShowOne"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        actionRefreshItemByIndex
    } from '../mixins'

    const initialEditedItem = () => ({
        id: '',
        creator_user_id: '',
        contract_on_user_id: '',
        contract_address: '',
        contract_number: '',
        status: 'В обработке',
        contract_start_datetime: '',
        contract_comment: ''
    })

    export default {
    components: {
        "contract-cards": ContractCards,
        "contract-to-create-edit": ContractTOCreateOrEdit,
        "contract-show-one": ContractShowOne
    },
    mixins: [
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        actionRefreshItemByIndex
    ],
    data() {
        return {
            tableApiUrl: API_CONTRACTS,
            user: auth.user,
            statusList: [
                'В обработке',
                'Есть бумажный договор',
                'Нет бумажного договора',
                'Договор расторгнут'
            ],
            contractForTO: {},
            contractForTOIndex: -1,
            operationForTO: ""
        }
    },
    watch: {
        "editedItem.contract_start_datetime": function(value) {
            this.editedItem.contract_start_datetime = value ?
                this.$moment(value).format('YYYY-MM-DD') :
                ''
        },
    },
    mounted() {
        this.editedItem = initialEditedItem()
    },
    methods: {
        makeToast(variant = null) {
            this.$bvToast.toast(variant === 'success' ? 'Договор сохранен' : 'Договор удален', {
                title: `Оповещение`,
                variant: variant,
                solid: true
            })
        },
        modalTitle() {
            let title = 'Добавление договора'
            if (this.editIndex > -1) {
                title = 'Договор #' + this.editedItem.id
            }

            return title
        },
        // calls from mixin on item edit
        onItemEditModalCallback() {
        },
        // calls from mixin on item save
        onCreateOrUpdateItemCallback() {
        },
        // calls from mixin on item save
        onCreatedOrUpdatedCallback() {
            this.isSidebarOpen = false
        },
        // calls from mixin on modal close
        closeSidePanelCallback() {
            this.editedItem = initialEditedItem()
        },
        setUserOfContract(value) {
            this.editedItem.contract_on_user_id = value
        },
        onlyForBackOffice() {
            if (!this.user.role.includes("client")) {
                return true
            }
            return false
        },
        openContractTOCreateModal(index) {
            let id = this.items.data[index].id
            api.call("get", `${this.tableApiUrl}/${id}`).then(({data}) => {
                this.contractForTO = data
                this.contractForTOIndex = index
                // If this element exist in table lets update
                if (typeof this.items !== 'undefined' && this.items.data[index]) {
                    this.items.data[index] = Object.assign(this.items.data[index], data)
                }
            }).finally(() => {
                this.operationForTO = "create"
            })
        },
        clearTOData() {
            this.refreshItemByIndex(this.contractForTOIndex)
            this.contractForTO = initialEditedItem()
            this.contractForTOIndex = -1
            this.operationForTO = ''
        },
        updateParentData(index) {
            this.refreshItemByIndex(index)
        }
    }
  }

</script>
