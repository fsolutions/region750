<template>
    <div>
        <app-table
            :key="componentRefreshKey"
            :api="tableApiUrl"
            :items.sync="items"
            :isNeedSearch="true"
            :additionalGetParameter="additionalGetParameter"
            :isNeedCreate="isNeedCreate"
            :typeOfTableFilter="typeOfTableFilter"
            :customAddButtonName="'Добавить ТО'"
            :onDataEmptyMessage="'Зарегистрированные ТО отсутствуют. Наметьте следующую дату ТО или отметьте предыдущие.'"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
        />

        <b-sidebar
            v-if="isSidebarOpen && contractForTO"
            v-model="isSidebarOpen"
            id="sidebar-right"
            right
            backdrop
            shadow
            width="65em"
            backdrop-variant="dark"
            ref="editItem"
            no-close-on-backdrop
            @change="closeSidePanelCallback()"
        >
            <template #title>
                <div>
                    <strong id="sidebar-right___title__" v-html="modalTitle()"></strong>
                </div>
            </template>
            <div class="d-block">
                <validation-errors :errors="validationErrors" v-if="validationErrors"></validation-errors>
                <form class="needs-validation mb-4" novalidate id="contractTOForm">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Заполните данные по ТО</h5>
                        </div>
                    </div>
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
                        <div class="form-group col-md-12">
                            <label for="contract_on_user_id">Мастер на выполнение ТО</label>
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
                </form>
            </div>
            <template #footer>
                <div class="row align-items-center">
                    <div class="col d-flex flex-row-reverse">
                        <button class="btn btn-primary text-right" type="submit" @click="submitForm($event)">Сохранить<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                    </div>
                </div>
            </template>
        </b-sidebar>

        <b-sidebar
            v-model="isSidebarOpenDetail"
            id="sidebar-right"
            title="Детальная информация"
            right
            backdrop
            shadow
            width="65em"
            backdrop-variant="dark"
            ref="showItem"
        >
            <div class="d-block">
                <contract-to-details
                    :detailedItem="detailedItem"
                ></contract-to-details>
            </div>
        </b-sidebar>        
    </div>
</template>

<script>
    import {API_CONTRACTS_TO} from "../../constants"
    import ContractTODetails from "./ContractTODetails"

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
        to_no_access_times: 0,     
        master: {},
        to_sms_sended: '',
        to_email_sended: ''
    })

    export default {
        components: {
            "contract-to-details": ContractTODetails
        },
        mixins: [
            defaultDataItems,
            actionShowItem,
            actionCreateOrUpdateItem,
            actionDeleteItem,
            actionAutoTOItem
        ],
        props: {
            contract_id: { type: Number|String, required: false },
            isNeedCreate: { type: Boolean, required: false, default: true },        // If table needs create new element button
            additionalGetParameter: { type: String, required: false, default: '' }, // If we want to add something, for example, &order_id=10
            typeOfTableFilter: { type: String, required: false, default: '' },      // Type of filter for output: orders, services
            contractForTO: {type: Object, required: false},                          // Contract object
            contractForTOIndex: {type: Number, required: false},                     // Index of Contract object in table
        },
        data() {
            return {
                changeLoad: false,
                tableApiUrl: API_CONTRACTS_TO,
                componentRefreshKey: 0,
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
        },
        mounted() {
            this.editedItem = initialEditedItem()
            this.initialAssign()
        },
        methods: {
            initialAssign() {
                if (this.contract_id) {
                    this.editedItem.to_contract_id = this.contract_id
                }
            },
            // calls from mixin before sidebaropened
            beforeSidebarOpenedCallback() {
                this.editedItem = initialEditedItem()
                this.initialAssign()
            },
            // calls from mixin on item edit
            onItemEditModalCallback() {
                if (this.editedItem.to_start_datetime) {
                    let dateArray = this.editedItem.to_start_datetime.split(" ")
                    this.to_start_datetime_date = dateArray[0]
                    this.to_start_datetime_time = dateArray[1]
                }
            },
            onCreateOrUpdateItemCallback() {
                if (this.to_no_access_1) {
                    this.editedItem.to_no_access_times = 1
                }
                if (this.to_no_access_2) {
                    this.editedItem.to_no_access_times = 2
                }
            },
            // calls from mixin on item save
            onCreatedOrUpdatedCallback() {
                if (this.editIndex == -1) {
                    this.editedItem.to_contract_id = this.contract_id
                }

                if (this.needAutoTO) {
                    this.autoTOItem(this.editIndex)
                }

                this.isSidebarOpen = false
            },
            submitForm(event) {
                event.preventDefault()
                let form = document.getElementById("contractTOForm")
                if (form.checkValidity() === false) {
                    event.preventDefault()
                    event.stopPropagation()
                    form.classList.add('was-validated')
                    return false
                }
                this.createOrUpdateItem()
            },
            onDeletedCallback() {
                // this.componentRefreshKey++
                this.updateParentData()
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
                    title = 'Редактирование ТО #' + this.editedItem.id
                }

                return title
            },
            setUserOfContractTO(value) {
                this.editedItem.to_master_user_id = value
            },
            updateParentData() {
                this.$emit("updateParentData")
            }
        }
    }
</script>
