<template>
    <div>
        <b-sidebar
            v-if="isSidebarOpen"
            v-model="isSidebarOpen"
            id="sidebar-right"
            right
            backdrop
            shadow
            width="65em"
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
                            <input v-model="contractForTO.contract_number" disabled type="text" class="form-control" id="contract_number">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="to_start_datetime">Дата и время проведения ТО (ориентировочно или точно)</label>
                            <input v-model="editedItem.to_start_datetime" required type="datetime-local" class="form-control" id="to_start_datetime">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="contract_address">Мастер на выполнение ТО</label>
                            <select-user
                                id="contract_on_user_id"
                                :roles="`administrator||master||intern`"
                                :needNullElement="true"
                                :selected="editedItem.to_master_user_id"
                                :selectedUser="editedItem.master"
                                @set="setUserOfContractTO"
                            ></select-user>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="role">Статус</label>
                            <b-form-select v-model="editedItem.to_status" required :options="statusList" id="to_status"></b-form-select>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Комментарий (виден только коллегам)</label>
                            <b-form-textarea
                                id="to_comment"
                                v-model="editedItem.to_comment"
                                placeholder="Если есть что отметить по ТО, отметьте."
                                rows="3"
                                max-rows="16"
                            ></b-form-textarea>
                        </div>                            
                        <div class="form-group col-md-12" v-if="editIndex != -1 && (editedItem.to_status == 'Проведено' || editedItem.to_status == 'Отменено')">
                            <b-form-checkbox
                                id="needAutoTO"
                                v-model="needAutoTO"
                                name="needAutoTO"
                            >
                                Назначить автоматическое ТО?
                            </b-form-checkbox>                    
                            
                        </div>
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
    import {API_CONTRACTS_TO} from "../../constants"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        actionAutoTOItem
    } from '../../mixins'

    const initialEditedItem = () => ({
        id: '',
        to_contract_id: '',
        to_master_user_id: '',
        to_start_datetime: '',
        to_comment: '',
        to_status: 'Запланировано',   
        master: {},
        to_sms_sended: '',
        to_email_sended: ''
    })

    export default {
        props: {
            contractForTO: {type: Object, required: true},              // Contract object
            contractForTOIndex: {type: Number, required: true},         // Index of Contract object in table
            operationForTO: {type: String, required: true},             // Type of operation: 'show', 'edit', 'create'
        },
        components: {
        },
        mixins: [
            defaultDataItems,
            actionShowItem,
            actionCreateOrUpdateItem,
            actionDeleteItem,
            actionAutoTOItem
        ],
        data() {
            return {
                tableApiUrl: API_CONTRACTS_TO,
                user: auth.user,
                statusList: [
                    'Запланировано',
                    'Проведено',
                    'Отменено',
                    'Перенесено'
                ],
                needAutoTO: false
            }
        },
        watch: {
            "editedItem.to_start_datetime": function(value) {
                this.editedItem.to_start_datetime = value ?
                    this.$moment(value).format('YYYY-MM-DDTHH:mm') :
                    ''
            },
            operationForTO(operation) {
                handleOperation(operation)
            },
            isSidebarOpen(value){
                if (!value) {
                    this.$emit('clear')
                }
            }
        },
        mounted() {
            this.editedItem = initialEditedItem()
            this.handleOperation(this.operationForTO)
        },
        methods: {
            handleOperation(operation) {
                if (operation == 'create') {
                    this.isSidebarOpen = true
                }
            },
            makeToast(variant = null) {
                this.$bvToast.toast(variant === 'success' ? 'ТО сохранено' : 'ТО удалено', {
                    title: `Оповещение`,
                    variant: variant,
                    solid: true
                })
            },
            modalTitle() {
                let title = 'Добавление ТО'
                if (this.editIndex > -1) {
                    title = 'ТО #' + this.editedItem.id
                }

                return title
            },
            // calls from mixin on item edit
            onItemEditModalCallback() {
            },
            // calls from mixin on item save
            onCreateOrUpdateItemCallback() {
                if (this.editIndex == -1) {
                    this.editedItem.to_contract_id = this.contractForTO.id
                }
            },
            // calls from mixin on item save
            onCreatedOrUpdatedCallback() {
                if (this.needAutoTO) {
                    this.autoTOItem(this.editIndex)
                }
                this.isSidebarOpen = false
            },
            // calls from mixin on modal close
            closeSidePanelCallback() {
                this.editedItem = initialEditedItem()
            },
            setUserOfContractTO(value) {
                this.editedItem.to_master_user_id = value
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
