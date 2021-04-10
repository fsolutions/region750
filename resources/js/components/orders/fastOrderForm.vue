<template>
  <div>
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
        no-close-on-backdrop
        @change="closeSidePanelCallback()"
    >
        <div class="d-block">      
            <validation-errors :errors="validationErrors" v-if="validationErrors"></validation-errors>
            <form class="needs-validation mb-4" novalidate id="fastOrderForm">
                <div class="row">
                    <div class="col-sm-12">
                        <h5 class="mb-3">Заполните данные по обращению</h5>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Выберите тему обращения</label>
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
                            required
                            v-model="editedItem.order_description"
                            placeholder=""
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
                    <button class="btn btn-primary text-right" type="submit" @click="submitForm($event)">Отправить нам<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                </div>
            </div>
        </template>
    </b-sidebar>
  </div>
</template>

<script>
    import {API_ORDERS} from "../../constants"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
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
        },
        mixins: [
            defaultDataItems,
            actionShowItem,
            actionCreateOrUpdateItem,
            actionDeleteItem,
        ],
        props: {
            openForm: { type: Boolean, required: false, default: false },
            contract_id: { type: Number|String, required: false },
            prescription_id: { type: Number|String, required: false },
        },
        data() {
            return {
                changeLoad: false,
                tableApiUrl: API_ORDERS,
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
            openForm(value){
                this.isSidebarOpen = value
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
            // calls from mixin before sidebaropened
            onCreateOrUpdateItemCallback() {
                this.initialAssign()
            },
            // calls from mixin on item save
            onCreatedOrUpdatedCallback() {
                this.isSidebarOpen = false
            },
            onSidePanelCallback() {
                this.editedItem = initialEditedItem()
                this.$emit("sended")
            },
            submitForm(event) {
                event.preventDefault()
                let form = document.getElementById("fastOrderForm")
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
            }
        }
    }
</script>
