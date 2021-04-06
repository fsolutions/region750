<template>
    <div>
        <app-table
            :name="'order'"
            :api="tableApiUrlChooser()"
            :items.sync="items"
            :isNeedSearch="externalCall() ? false : true"
            :isNeedCreate="externalCall() ? false : true"
            :headerContextMenuName="headerContextMenuName"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
            @copy="copyItem"
            @openOrderDocumentsFromAction="openOrderDocumentsFromAction"
            @makeOrderSupply="makeOrderSupply"
            @onLoad="onItemsLoadCallback"
            :key="tableComponentRefreshKey"
            :onDataEmptyMessage="onDataEmptyMessage"
            :typeOfTableFilter="externalCall() ? '' : typeOfTableFilter"
            :customAddButtonName="'Создать заявку'"
            :additionalGetParameter="additionalGetParameter()"
        ></app-table>
        <b-sidebar
            v-if="isSidebarOpen"
            v-model= "isSidebarOpen"
            id="sidebar-right"
            right
            backdrop
            shadow
            width="109em"
            backdrop-variant="dark"
            no-close-on-backdrop
            ref="isSidebarOpen"
            @change="closeSidePanelCallback()"
        >
            <template #title>
                <div>
                    <strong id="sidebar-right___title__" v-html="modalTitle()"></strong>
                </div>
            </template>

            <div class="d-block">
                <div class="order_data">
                    <validation-errors :errors="validationErrors" v-if="validationErrors"></validation-errors>

                    <div class="row">
                        <div class="col-sm-6">
                            <h5 class="mb-2" v-if="!editedItem.short_order">Данные по клиенту</h5>
                        </div>
                        <div class="col-sm-6" v-if="!editedItem.company_id">
                            <div class="text-right">
                                <b-form-checkbox v-model="editedItem.short_order" name="short_order-check-button" switch>
                                    <b>Заявка без компании</b>
                                </b-form-checkbox>
                            </div>
                        </div>
                    </div>

                    <div class="form-row" v-if="editedItem.short_order">
                        <div class="form-group col-md-6">
                            <label for="temp_name">Условное наименование (метка, компания и т.д.)</label>
                            <b-form-input
                                id="temp_name"
                                v-model="editedItem.temp_name"
                            ></b-form-input>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <h5 class="mb-3">Основные данные</h5>
                        </div>
                        <div class="col-sm-6">
                            <div class="text-right">
                                <b-form-checkbox v-model="needOptionalSalesManager" name="optional-sales-manager-check-button" switch>
                                    <b>Назначить Sales менеджера на обращение</b>
                                </b-form-checkbox>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4" v-if="needOptionalSalesManager">
                            <label>Менеджер SALES</label>
                            <select-user
                                id="optional_sales_manager_user_id"
                                :roles="`sales-manager||sales-head-manager||logistics-manager||logistics-head-manager||TO-manager||TO-head-manager`"
                                :needNullElement="true"
                                :selected="editedItem.optional_sales_manager_user_id"
                                @set="setSalesManagerOfOrder"
                            ></select-user>
                        </div>
                        <div class="form-group col-md-6" v-show="editedItem.reference_order_type_id == 1">
                            <label for="receive_datetime">Дата получения заявки</label>
                            <b-form-input
                                @focus="focusReceiveDateTime"
                                id="receive_datetime"
                                :type="`datetime-local`"
                                v-model="editedItem.receive_datetime"
                            ></b-form-input>
                            <div class="mt-3">
                                <label for="processing_end_datetime">Дата отработки заявки (КП направлено/консультация оказана)</label>
                                <b-form-input
                                    id="processing_end_datetime"
                                    :type="`datetime-local`"
                                    v-model="editedItem.processing_end_datetime"
                                ></b-form-input>
                            </div>
                        </div>
                        <div :class="editedItem.reference_order_type_id == 1 ? 'form-group col-md-6':'form-group col-md-12'">
                            <label for="comment">Комментарий к заявке</label>
                            <b-form-textarea
                                id="textarea-comment"
                                placeholder="Оставьте комментарий к заявке, если есть что сообщить..."
                                rows="5"
                                max-rows="8"
                                v-model="editedItem.comment"
                            ></b-form-textarea>
                        </div>
                    </div>
                </div>
                <div class="document_view_order" v-if="editIndex > -1">
                    <h5 v-if="editedItem.documents.length > 0">Документы по обращениеу</h5>
                    <show-edit-documents
                        :orderId="editedItem.id"
                        :requestDocuments="requestDocuments"
                    />
                </div>
                <div class="document_upload" v-if="checkedLoadDocument">
                    <create-documents
                        :orderId="idCreatedOrder"
                        :needUploadButton="isShowUploadButton"
                        :uploadDocumentsNow.sync="uploadDocumentsNow"
                        :whereDocumentLoadingItemIndex="editIndex"
                        @documentLoaded="onCloseSidePanelLoadingDocument"
                        @updateEditedItemDocuments="updateEditedItemDocuments"
                    />
                </div>
            </div>

            <template #footer>
                <div class="row align-items-center">
                    <div class="col d-flex flex-row-reverse">
                        <button class="btn btn-primary text-right" @click="createOrUpdateItem()">Сохранить<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                        <b-form-checkbox v-model="checkedLoadDocument" name="check-button" switch class="mr-4" v-if="!editedItem.short_order || editedItem.reference_sources_id==23">
                            <b>Прикрепить документ</b>
                        </b-form-checkbox>
                        <b-button variant="link" size="sm" class="mr-2 mt-1" @click="openHelp=true" v-if="!editedItem.short_order">
                            <b-icon icon="question-circle-fill" scale="1.3" aria-label="Какие документы нужны?"></b-icon>
                        </b-button>
                    </div>
                </div>
            </template>
        </b-sidebar>

        <b-sidebar
            v-if="isSidebarOpenDetail"
            v-model="isSidebarOpenDetail"
            id="sidebar-right"
            title="Детальная информация"
            right
            backdrop
            shadow
            width="109em"
            backdrop-variant="dark"
            no-close-on-backdrop
        >
            <order-show-one
                :detailedItemIndex="detailedItemIndex"
                :detailedItem="detailedItem"
                @closeOrder="closeOrderByIndex"
                @closeOrderFinally="closeOrderFinallyByIndex"
            ></order-show-one>
        </b-sidebar>

        <b-modal
            id="order-documents-help-modal"
            title="Памятка по документам при оформлении обращение"
            v-model="openHelp"
            hide-footer
        >
            <p><em>Список документов дан максимально исчерпывающий, не все из перечисленных документов обязательны для оформления услуг:</em></p>
            <p><strong>Для заявок ТЭО:</strong></p>
            <ul clas="disc-lists">
                <li>Заявка на перевозку,</li>
                <li>Прочие документы (например MSDS и т.д.)<br />Транспортную накладную (AWB, B/L, CMR, ж/д накладную)</li>
            </ul>
            <p><strong>Для заявок ТО:</strong></p>
            <ul class="disc-lists">
                <li>Контракт</li>
                <li>Дополнительное соглашение</li>
                <li>Спецификация</li>
                <li>Инвойс</li>
                <li>Упаковочный лист</li>
                <li>Техническое описание товара</li>
                <li>SWIFT (п/п об оплате продавцу за товар, если условиями контракта предусмотрен авансовый платеж)</li>
                <li>Экспортная декларация</li>
                <li>Сертификация</li>
                <li>Транспортная накладная (AWB, BL, CMR, ж/д накладная)</li>
                <li>Договор транспортных услуг.</li>
                <li>Счет за транспорт.</li>
                <li>П/п на таможню пошлина.</li>
                <li>П/п на таможню аванс.</li>
                <li>Прайс-лист.</li>
                <li>Страхование.</li>
                <li>Прочие затраты, которые включаются в таможенную стоимость (pick-up до порта/аэропорта/транспортного узла или терминала, ПРР в стране отправления, проведение сюрвейерских проверок, испытание и тестирование оборудования и пр.)</li>
            </ul>
            <b-button class="mt-3 float-right" @click="$bvModal.hide('order-documents-help-modal')">Закрыть</b-button>
        </b-modal>
    </div>
</template>

<script>
    import {
        API_ORDERS,
        API_ORDERS_BY_SERVICES,
        API_POST_NOTIFICATIONS_READ_BY_SERVICE_REFERENCE_ID,
        API_POST_NOTIFICATIONS_ORDERS_READ,
    } from "../constants"

    import OrderShowOne from  "../components/orders/OrderShowOne"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        actionCopyItem,
        orderMethods
    } from '../mixins'

    const initialEditedItem = () => ({
        id: '',
        creator_user_id: '',
        creator: {},
        company_employee_id: '',
        company_employee: {
            id: '',
            lead_fio: '',
            lead_email: '',
            lead_phone: '',
        },
        reference_order_type_id: 1,
        type: {},
        temp_name: '',
        reference_status_id: 359,
        status: {},
        receive_datetime: '',
        processing_end_datetime: '',
        comment: '',
        reference_close_reason_id: '',
        close_reason: {},
        close_comment: '',
        documents: [],
        short_order: false,
        optional_sales_manager_user_id: '',
        optional_sales_manager: {},
        reference_sources_id: ''
    })

    export default {
        mixins: [
            defaultDataItems,
            actionShowItem,
            actionCreateOrUpdateItem,
            actionDeleteItem,
            actionCopyItem,
            orderMethods
        ],
        components: {
            "order-show-one": OrderShowOne,
        },
        props: {
            companyId: { type: Number | String, required: false, default: '' },         //company id for showing orders
        },
        data() {
            return {
                checkedLoadDocument: false, //do i need a document
                idCreatedOrder: '',         //id new order
                isShowUploadButton: false,  //show-hide download button
                requestDocuments: false,    //sign for document output by order
                uploadDocumentsNow: false,  // Trigger for loading new documents on save button click
                makeOrderSupplyFlag: false, // Flag for making order supply
                maskPhone: '',
                isSideBarOpenCompany: false,
                isSidebarOpen: false,
                tableApiUrl: '',
                tableComponentRefreshKey: 0,
                hotCompanyUpdate: false,
                typeOfTableFilter: 'orders',
                openHelp: false,
                headerContextMenuName: 'orderTable',
                tableApiUrlSaver: '',
                needOptionalSalesManager: false
            }
        },
        mounted() {
            this.editedItem = initialEditedItem()
            this.$root.$on('bv::collapse::state', (e) => {
                this.requestDocuments = true
            })
        },
        watch: {
            $route(to, from) {
                this.tableApiUrl = this.tableApiUrlChooser()
                this.tableComponentRefreshKey += 1
            },
            maskPhone: function (phone){
                if (phone) {
                    let freshPhone = phone.replace(/[^0-9]/g, '')
                    this.editedItem.company_employee.lead_phone = freshPhone
                }
            },
            "editedItem.processing_end_datetime": function(value) {
                this.editedItem.processing_end_datetime = value ?
                    this.$moment(value).format('YYYY-MM-DDTHH:mm') :
                    ''
            },
            "editedItem.short_order": function(status) {
                this.editedItem.short_order = (status == 1) ? true : false
            }
        },
        methods: {
            focusReceiveDateTime(){
                this.editedItem.receive_datetime = ''
            },
            tableApiUrlChooser() {
                let apiUrl = API_ORDERS
                this.onDataEmptyMessage = 'Таблица обращений пуста. Создайте новое обращение прямо сейчас.'

                if (this.tableApiUrl != apiUrl) {
                    this.tableApiUrl = apiUrl
                    this.typeOfTableFilter = 'orders'
                    this.clearOrdersNotificationList()
                }

                return apiUrl
            },
            additionalGetParameter() {
                return ''
            },
            externalCall() {
                return false
            },
            clearOrdersNotificationList: async function() {
                const response = await api.call('post', API_POST_NOTIFICATIONS_ORDERS_READ, {
                    place: 'left_menu'
                })
                this.$store.dispatch('GET_NOTIFICATIONS')
            },
            modalTitle() {
                let title = 'Добавление новой заявки'
                if (this.editIndex > -1 && this.editedItem.reference_order_type_id == 1) {
                    title = 'Редактирование заявки #' + this.editedItem.id
                } else if (this.editIndex > -1 && this.editedItem.reference_order_type_id > 1) {
                    title = 'Редактирование поставки #' + this.editedItem.id
                }
                return title
            },
            // calls from Table.vue on items load
            onItemsLoadCallback() {
            },
            // calls from mixin before modal opened
            beforeSidebarOpenedCallback() {
                this.editedItem = initialEditedItem()
                this.maskPhone = ''
            },
            // calls from mixin on item edit
            onItemEditModalCallback: async function() {
                let id = this.items.data[this.editIndex].id
                await api.call("get", `${API_ORDERS}/${id}`).then(({data}) => {
                    this.editedItem = { ...this.editedItem, ...data }
                    this.items.data[this.editIndex] = Object.assign(this.items.data[this.editIndex], data)

                    this.editedItem.receive_datetime = this.$moment(this.editedItem.receive_datetime).format('YYYY-MM-DDTHH:mm')

                    if (this.editedItem.optional_sales_manager_user_id) {
                        this.needOptionalSalesManager = true
                    }
                 })
                this.maskPhone = this.editedItem.company_employee.lead_phone
                this.idCreatedOrder = this.editedItem.id
            },
            // calls from mixin on item edit or create
            afterSidebarOpenedCallback() {
                if (this.editedItem.reference_order_type_id == 2) {
                    delete this.editedItem.receive_datetime
                    delete this.editedItem.processing_end_datetime
                }

                if(this.editIndex == -1){
                    this.editedItem.receive_datetime = this.$moment.utc().local().format('YYYY-MM-DDTHH:mm')
                }
            },
            // calls from mixin before item save
            onCreateOrUpdateItemCallback() {
                this.tableApiUrlSaver = this.tableApiUrl
                this.tableApiUrl = API_ORDERS
            },
            // calls from mixin on item save
            onCreatedOrUpdatedCallback(orderId) {
                this.idCreatedOrder = orderId.id
                if(this.checkedLoadDocument == false){
                    this.isSidebarOpen = false
                }
                this.uploadDocumentsNow = true
            },
            // calls from mixin on modal close
            onSidePanelCallback() {
                this.editedItem = initialEditedItem()
                this.checkedLoadDocument = false
                this.idCreatedOrder = ''
                this.requestDocuments = false
                this.uploadDocumentsNow = false
                this.needOptionalSalesManager = false
            },
            // closing after loading the document
            onCloseSidePanelLoadingDocument(value) {
                if(value == true){
                    this.checkedLoadDocument = false
                    this.idCreatedOrder = ''
                    this.uploadDocumentsNow = false
                    this.isSidebarOpen = false
                }
            },
            updateEditedItemDocuments(data) {
                if (data.editIndex != -1) {
                    this.items.data[data.editIndex].documents.push(data.file)
                }
            },
            setTypeOfOrder(value) {
                this.editedItem.reference_order_type_id = value
            },
            setStatusOfOrder(value) {
                this.editedItem.reference_status_id = value
            },
            openOrderDocumentsFromAction(index) {
                this.showItem(index, '#oneOrderTabs a[href="#order-documents"]')
            },
            makeOrderSupply(index) {
                let id = this.items.data[index].id
                this.items.data[index].loading = true
                let message = this.finalFormValidate(index)
                const updateFields = {
                    reference_order_type_id: 2
                }

                if (message.length == 0) {
                    this.updateAnyOrder(id, updateFields, index)
                } else {
                    this.editIndex = index
                    this.createOrEditItemModal(this.editIndex)
                    this.items.data[this.editIndex].loading = false
                    this.isSidebarOpen = true
                    const h = this.$createElement
                    const vNodesMsg = h(
                        'ul',
                        message
                    )
                    this.$bvToast.toast([vNodesMsg], {
                        title: `Невозможно перевести обращение #${this.editedItem.id} в Поставку`,
                        variant: 'warning',
                        solid: true,
                        noAutoHide: true
                    })
                }
            },
            // call update one order from action mixin
            updateOneOrder(index) {
                this.updateOneOrderByIndex(index)
            },
            finalFormValidate(index) {
                const h = this.$createElement
                let message = []

                if (!this.items.data[index].company_id) {
                    message.push(h('li', "- Выберите компанию клиента"))
                }
                if (!this.items.data[index].company_employee || (this.items.data[index].company_employee && !this.items.data[index].company_employee.lead_phone)) {
                    message.push(h('li', "- Укажите, как минимум, телефон клиента"))
                }
                if (this.items.data[index].set_categories_ids.length == 0) {
                    message.push(h('li', "- Укажите категории обращение"))
                }
                if (!this.items.data[index].receive_datetime) {
                    message.push(h('li', "- Укажите дату получения заявки"))
                }
                if (!this.items.data[index].processing_end_datetime) {
                    message.push(h('li', "- Укажите дату отработки заявки"))
                }
                if (this.items.data[index].documents_count < 1) {
                    if (!this.items.data[index].comment) {
                        message.push(h('li', "- Прикрепите к заявке документы или оставьте комментарий"))
                    }
                }
                return message
            },
            setSalesManagerOfOrder(value) {
                this.editedItem.optional_sales_manager_user_id = value
            },
        }
    }

</script>
<style>
    .disc-lists {
        list-style: disc;
        margin-left: 25px;
    }
</style>
