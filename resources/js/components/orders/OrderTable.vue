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
            :onDataEmptyMessage="'Зарегистрированные обращения отсутствуют. Выберите договор и создайте новое обращение.'"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
            @reject="rejectItem"
        />

        <b-sidebar
            v-if="isSidebarOpen && (contractForTO || editedItem.order_contract_id)"
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
                <form class="needs-validation mb-4" novalidate id="orderForm">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Заполните данные по обращению</h5>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12" v-if="!contractForTO && editedItem.order_contract_id">
                            <label for="contract_number">Номер договора</label>
                            <input disabled type="text" class="form-control" id="contract_number" :value="outputContractNumber()">
                        </div>
                        <div class="form-group col-md-12">
                            <label>Выберите услугу обращения</label>
                            <select-of-properties
                                :reference_id="1"
                                :needNullElement="false"
                                :needMultipleSelect="false"
                                :defaultSelectName="`Выберите нужные услуги`"
                                :selected="editedItem.order_reference_service_id"
                                @set="setServiceOfOrder"
                            ></select-of-properties>
                        </div>                            
                        <div class="form-group col-md-12">
                            <label>Опишите подробности обращения</label>
                            <b-form-textarea
                                id="order_description"
                                v-model="editedItem.order_description"
                                placeholder=""
                                rows="3"
                                max-rows="16"
                            ></b-form-textarea>
                        </div>                            
                        <div class="form-group col-md-12">
                            <label for="order_start_datetime">Планируемая дата исполнения обращения</label>
                            <!-- <input v-model="editedItem.order_start_datetime" type="date" class="form-control" id="order_start_datetime"> -->
                            <b-form-datepicker 
                                id="order_start_datetime" 
                                placeholder="Выберите дату" 
                                locale="ru"
                                label-help="Используйте клавиши для передвижения по календарю"
                                label-no-date-selected="Выберите дату"
                                v-model="editedItem.order_start_datetime"
                            ></b-form-datepicker>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="contract_on_user_id">Мастер на выполнение обращения</label>
                            <select-user
                                id="contract_on_user_id"
                                :roles="`administrator||master||intern`"
                                :needNullElement="true"
                                :selected="editedItem.order_master_user_id"
                                :selectedUser="editedItem.master"
                                @set="setUserOfOrder"
                            ></select-user>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="role">Статус</label>
                            <b-form-select v-model="editedItem.order_status" required :options="statusList" id="order_status"></b-form-select>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Резюме по работе (виден клиентам)</label>
                            <b-form-textarea
                                id="order_comment_for_user"
                                v-model="editedItem.order_comment_for_user"
                                placeholder="Напишите резюме для клиента и истории."
                                rows="3"
                                max-rows="16"
                            ></b-form-textarea>
                        </div>                            
                        <div class="form-group col-md-12">
                            <label>Комментарий по обращению (виден только коллегам)</label>
                            <b-form-textarea
                                id="order_comment"
                                v-model="editedItem.order_comment"
                                placeholder="Если есть что отметить по обращению, отметьте."
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
                <order-details
                    :detailedItem="detailedItem"
                ></order-details>
            </div>
        </b-sidebar>        
    </div>
</template>

<script>
    import {API_ORDERS, API_ORDERS_UPDATE_EASY} from "../../constants"
    import OrderDetails from "./OrderDetails"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        actionUpdateItemByIndex
    } from '../../mixins'

    const initialEditedItem = () => ({
        id: '',
        order_user_id: '',
        order_reference_service_id: 8,
        order_contract_id: '',
        order_description: '',
        order_prescription_id: '',
        order_master_user_id: '',
        order_start_datetime: '',
        order_comment: '',
        order_comment_for_user: '',
        order_status: 'В обработке',
        order_user: {},
        order_service: {},
        master: {},
        order_contract: {},
        order_prescription: {},
    })

    export default {
        components: {
            "order-details": OrderDetails
        },
        mixins: [
            defaultDataItems,
            actionShowItem,
            actionCreateOrUpdateItem,
            actionDeleteItem,
            actionUpdateItemByIndex
        ],
        props: {
            contract_id: { type: Number|String, required: false },
            prescription_id: { type: Number|String, required: false },
            isNeedCreate: { type: Boolean, required: false, default: true },        // If table needs create new element button
            additionalGetParameter: { type: String, required: false, default: '' }, // If we want to add something, for example, &order_id=10
            typeOfTableFilter: { type: String, required: false, default: '' },      // Type of filter for output: orders, services
            contractForTO: {type: Object, required: false},                          // Contract object
            contractForTOIndex: {type: Number, required: false},                     // Index of Contract object in table
        },
        data() {
            return {
                changeLoad: false,
                tableApiUrl: API_ORDERS,
                componentRefreshKey: 0,
                user: auth.user,
                statusList: [
                    'В обработке',
                    'Запланировано исполнение',
                    'Исполнено',
                    'Отменено'
                ]
            }
        },
        watch: {
            "editedItem.order_start_datetime": function(value) {
                this.editedItem.order_start_datetime = value ?
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
                    this.editedItem.order_contract_id = this.contract_id
                }
                if (this.prescription_id) {
                    this.editedItem.order_prescription_id = this.prescription_id
                }
            },
            outputContractNumber() {
                if (this.contractForTO) {
                    return this.contractForTO.contract_number
                }
                if (this.editedItem.order_contract_id) {
                    return this.editedItem.order_contract_id
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
                    this.editedItem.order_contract_id = this.contract_id
                }

                this.isSidebarOpen = false
            },
            submitForm(event) {
                event.preventDefault()
                let form = document.getElementById("orderForm")
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
                this.$bvToast.toast(variant === 'success' ? 'Обращение сохранено' : 'Обращение удалено', {
                    title: `Оповещение`,
                    variant: variant,
                    solid: true
                })
            },
            modalTitle() {
                let title = 'Добавление обращения'
                if (this.editIndex > -1) {
                    title = 'Редактирование обращения #' + this.editedItem.id
                }

                return title
            },
            setServiceOfOrder(value) {
                this.editedItem.order_reference_service_id = value
            },
            setUserOfOrder(value) {
                this.editedItem.order_master_user_id = value
            },
            updateParentData() {
                this.$emit("updateParentData")
            },
            rejectItem(index){
                this.updateItemByIndex(index, {
                    'order_status': 'Отменено'
                }, API_ORDERS_UPDATE_EASY)
            }
        }
    }
</script>
