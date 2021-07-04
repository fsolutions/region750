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
                            <div class="row">
                                <div class="col-4">
                                    <b-form-datepicker 
                                        id="to_start_datetime_date" 
                                        placeholder="Выберите дату" 
                                        locale="ru"
                                        label-help="Используйте клавиши для передвижения по календарю"
                                        label-no-date-selected="Выберите дату"
                                        v-model="to_start_datetime_date"
                                    ></b-form-datepicker>
                                </div>
                                <div class="col-2">
                                    <b-form-timepicker 
                                        id="to_start_datetime_time"
                                        v-model="to_start_datetime_time" 
                                        locale="ru"
                                        label-close-button="Закрыть"
                                        label-no-time-selected="Выберите время"
                                    ></b-form-timepicker>
                                </div>
                            </div>
                            <!-- <input v-model="editedItem.to_start_datetime" required type="datetime-local" class="form-control" id="to_start_datetime"> -->
                        </div>
                        <div class="form-group col-md-6">
                            <label for="contract_on_user_id">Мастера на выполнение ТО</label>
                            <select-user
                                id="contract_on_user_id"
                                :roles="`administrator||master||intern`"
                                :needNullElement="true"
                                :selected="editedItem.to_master_user_id"
                                :selectedUser="editedItem.master"
                                @set="setUserOfContractTO"
                            ></select-user>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="contract_on_user_id_2">&nbsp;</label>
                            <select-user
                                id="contract_on_user_id_2"
                                :roles="`administrator||master||intern`"
                                :needNullElement="true"
                                :selected="editedItem.to_master_user_id_2"
                                :selectedUser="editedItem.master_2"
                                @set="setUserOfContractTO2"
                            ></select-user>
                        </div>
                        <div class="form-group col-md-6">
                            <select-user
                                id="contract_on_user_id_3"
                                :roles="`administrator||master||intern`"
                                :needNullElement="true"
                                :selected="editedItem.to_master_user_id_3"
                                :selectedUser="editedItem.master_3"
                                @set="setUserOfContractTO3"
                            ></select-user>
                        </div>
                        <div class="form-group col-md-6">
                            <select-user
                                id="contract_on_user_id_4"
                                :roles="`administrator||master||intern`"
                                :needNullElement="true"
                                :selected="editedItem.to_master_user_id_4"
                                :selectedUser="editedItem.master_4"
                                @set="setUserOfContractTO4"
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
                        <div class="form-group col-md-12" v-if="editedItem.to_status != 'Проведено'">
                            <p class="my-2"><b>Клиент не обеспечил доступ</b></p>
                            <b-form-checkbox
                                id="to_no_access_1"
                                v-model="to_no_access_1"
                                name="to_no_access_1"
                            >
                                Первый раз
                            </b-form-checkbox>                    
                            <b-form-checkbox
                                id="to_no_access_2"
                                v-model="to_no_access_2"
                                name="to_no_access_2"
                            >
                                Второй раз
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
        to_master_user_id_2: '',
        to_master_user_id_3: '',
        to_master_user_id_4: '',
        to_start_datetime: '',
        to_comment: '',
        to_status: 'Запланировано',
        to_no_access_times: 0,   
        masters: '',  
        master: {},
        master_2: {},
        master_3: {},
        master_4: {},
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
                to_start_datetime_date: '',
                to_start_datetime_time: '12:00',
                to_no_access_1: false,
                to_no_access_2: false
            }
        },
        watch: {
            "to_start_datetime_date": function(value) {
                this.editedItem.to_start_datetime = this.to_start_datetime_date + ' ' + this.to_start_datetime_time
            },
            "to_start_datetime_time": function(value) {
                this.editedItem.to_start_datetime = this.to_start_datetime_date + ' ' + this.to_start_datetime_time
            },
            "editedItem.to_no_access_times": function(value) {
                if (value == 0) {
                    this.to_no_access_1 = false
                    this.to_no_access_2 = false
                } else if (value == 1) {
                    this.to_no_access_1 = true
                    this.to_no_access_2 = false
                } else if (value == 2) {
                    this.to_no_access_1 = true
                    this.to_no_access_2 = true
                }
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
                if (this.editedItem.to_start_datetime) {
                    let dateArray = this.editedItem.to_start_datetime.split(" ")
                    this.to_start_datetime_date = dateArray[0]
                    this.to_start_datetime_time = dateArray[1]
                }
            },
            // calls from mixin on item save
            onCreateOrUpdateItemCallback() {
                if (this.editIndex == -1) {
                    this.editedItem.to_contract_id = this.contractForTO.id
                }
                if (this.to_no_access_1) {
                    this.editedItem.to_no_access_times = 1
                }
                if (this.to_no_access_2) {
                    this.editedItem.to_no_access_times = 2
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
            setUserOfContractTO2(value) {
                this.editedItem.to_master_user_id_2 = value
            },
            setUserOfContractTO3(value) {
                this.editedItem.to_master_user_id_3 = value
            },
            setUserOfContractTO4(value) {
                this.editedItem.to_master_user_id_4 = value
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
