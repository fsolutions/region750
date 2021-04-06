<template>
    <div>
        <app-table
            v-if="onlyForBackOffice()"
            :api="tableApiUrl"
            :items.sync="items"
            :isNeedSearch="true"
            :customAddButtonName="`Добавить договор`"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
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
            width="35em"
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
                        </template>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Сохранить<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                    </div>
                </form>
            </div>
        </b-sidebar>
        <b-sidebar
            v-model="isSidebarOpenDetail"
            id="sidebar-right"
            title="Детальная информация"
            right
            backdrop
            shadow
            width="35em"
            backdrop-variant="dark"
            ref="showItem"
        >
            <div class="d-block">
                <pre>{{detailedItem}}</pre>
            </div>
        </b-sidebar>
  </div>
</template>

<script>
    import {API_CONTRACTS} from "../constants"

    import ContractCards from "../components/contracts/Cards"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
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
    },
    mixins: [
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
    ],
    data() {
        return {
            tableApiUrl: API_CONTRACTS,
            user: auth.user,
        }
    },
    mounted() {
        this.editedItem = initialEditedItem()
    },
    watch: {
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
    }
  }

</script>
