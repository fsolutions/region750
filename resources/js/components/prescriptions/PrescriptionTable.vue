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
            :customAddButtonName="'Создать новое'"
            :onDataEmptyMessage="user.role.includes('client') ? 'Зарегистрированные предписания отсутствуют.':'Зарегистрированные предписания отсутствуют. Выберите договор и создайте новое предписание.'"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
        />

        <b-sidebar
            v-if="isSidebarOpen && (contractForTO || editedItem.prescription_contract_id)"
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
                <form class="needs-validation mb-4" novalidate id="PrescriptionForm">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Заполните данные по предписанию</h5>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12" v-if="!contractForTO && editedItem.prescription_contract_id">
                            <label for="contract_number">Номер договора</label>
                            <input disabled type="text" class="form-control" id="contract_number" :value="outputContractNumber()">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="prescription_number">Номер предисания (из бумаг)</label>
                            <input v-model="editedItem.prescription_number" type="text" class="form-control" id="prescription_number">
                        </div>
                        
                        <div class="form-group col-md-12">
                            <label>Содержимое предписания (видно клиенту)</label>
                            <b-form-textarea
                                id="prescription_comment"
                                v-model="editedItem.prescription_comment"
                                placeholder="Например, Необходимо заменить кабель."
                                rows="3"
                                max-rows="16"
                            ></b-form-textarea>
                        </div>                            
                        <div class="form-group col-md-12">
                            <label for="prescription_start_datetime">Дата необходимая для исполнения предписания</label>
                            <!-- <input v-model="editedItem.prescription_start_datetime" required type="date" class="form-control" id="prescription_start_datetime"> -->
                            <b-form-datepicker 
                                id="prescription_start_datetime" 
                                placeholder="Выберите дату" 
                                locale="ru"
                                label-help="Используйте клавиши для передвижения по календарю"
                                label-no-date-selected="Выберите дату"
                                v-model="editedItem.prescription_start_datetime"
                            ></b-form-datepicker>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="prescription_master_user_id">Сотрудник, назначивший предписание</label>
                            <select-user
                                id="contract_on_user_id"
                                :roles="`administrator||master||intern||manager`"
                                :needNullElement="true"
                                :selected="editedItem.prescription_master_user_id"
                                :selectedUser="editedItem.master"
                                @set="setUserOfPrescription"
                            ></select-user>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="role">Статус</label>
                            <b-form-select v-model="editedItem.prescription_status" required :options="statusList" id="prescription_status"></b-form-select>
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
                <prescription-details
                    :detailedItem="detailedItem"
                ></prescription-details>
            </div>
        </b-sidebar>        
    </div>
</template>

<script>
    import {API_PRESCRIPTIONS} from "../../constants"
    import PrescriptionDetails from "./PrescriptionDetails"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
    } from '../../mixins'

    const initialEditedItem = () => ({
        id: '',
        prescription_number: '',
        prescription_contract_id: '',
        prescription_master_user_id: '',
        prescription_start_datetime: '',
        prescription_comment: '',
        prescription_status: 'Запланировано',
        master: {},
        prescription_contract: {},
        prescription_order: {}
    })

    export default {
        components: {
            "prescription-details": PrescriptionDetails
        },
        mixins: [
            defaultDataItems,
            actionShowItem,
            actionCreateOrUpdateItem,
            actionDeleteItem,
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
                tableApiUrl: API_PRESCRIPTIONS,
                componentRefreshKey: 0,
                user: auth.user,
                statusList: [
                    'Запланировано',
                    'Проведено',
                    'Отменено',
                    'Перенесено'
                ]
            }
        },
        watch: {
            "editedItem.prescription_start_datetime": function(value) {
                this.editedItem.prescription_start_datetime = value ?
                    this.$moment(value).format('YYYY-MM-DD') :
                    ''
            },
        },
        mounted() {
            this.editedItem = initialEditedItem()
            this.initialAssign()
        },
        methods: {
            initialAssign() {
                if (this.contract_id) {
                    this.editedItem.prescription_contract_id = this.contract_id
                }
            },
            outputContractNumber() {
                if (this.contractForTO) {
                    return this.contractForTO.contract_number
                }
                if (this.editedItem.prescription_contract_id) {
                    return this.editedItem.prescription_contract_id
                }

                return "-";
            },
            // calls from mixin before sidebaropened
            beforeSidebarOpenedCallback() {
                this.editedItem = initialEditedItem()
                this.initialAssign()
            },
            // calls from mixin on item save
            onCreatedOrUpdatedCallback() {
                if (this.editIndex == -1) {
                    this.editedItem.prescription_contract_id = this.contract_id
                }

                this.isSidebarOpen = false
            },
            submitForm(event) {
                event.preventDefault()
                let form = document.getElementById("PrescriptionForm")
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
                this.$bvToast.toast(variant === 'success' ? 'Предписание сохранено' : 'Предписание удалено', {
                    title: `Оповещение`,
                    variant: variant,
                    solid: true
                })
            },
            modalTitle() {
                let title = 'Добавление предписания'
                if (this.editIndex > -1) {
                    title = 'Редактирование предписания #' + this.editedItem.id
                }

                return title
            },
            setUserOfPrescription(value) {
                this.editedItem.prescription_master_user_id = value
            },
            updateParentData() {
                this.$emit("updateParentData")
            }
        }
    }
</script>
