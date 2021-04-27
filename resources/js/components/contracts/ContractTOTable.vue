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
    </div>
</template>

<script>
    import {API_CONTRACTS_TO} from "../../constants"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
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
        components: {
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
                tableApiUrl: API_CONTRACTS_TO,
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
            "editedItem.to_start_datetime": function(value) {
                this.editedItem.to_start_datetime = value ?
                    this.$moment(value).format('YYYY-MM-DDTHH:mm') :
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
                    this.editedItem.to_contract_id = this.contract_id
                }
            },
            // calls from mixin before sidebaropened
            beforeSidebarOpenedCallback() {
                this.editedItem = initialEditedItem()
                this.initialAssign()
            },
            // calls from mixin on item save
            onCreatedOrUpdatedCallback() {
                if (this.editIndex == -1) {
                    this.editedItem.to_contract_id = this.contract_id
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
