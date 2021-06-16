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
            :customAddButtonName="'Добавить новое'"
            :onDataEmptyMessage="'Зарегистрированные приборы отсутствуют. Выберите договор и добавьте оборудование.'"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
        />

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
                <form class="needs-validation mb-4" novalidate id="EquipmentForm">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Заполните данные по оборудованию</h5>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12 col-md-8">
                            <b-form-select 
                                v-model="editedItem.equip_type_reference_id" 
                                required 
                                :options="optionsEquipment" 
                                id="equip_type_reference_id"
                            ></b-form-select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-4">
                            <b-form-input v-model="editedItem.equip_passport" placeholder="Номер паспорта"></b-form-input>
                        </div>
                        <div class="col-4">
                            <b-form-input v-model="editedItem.equip_mark" placeholder="Марка прибора"></b-form-input>
                        </div>
                        <div class="col-4">
                            <b-form-datepicker 
                                :id="`equip_date_of_release`"                                
                                placeholder="Дата выпуска" 
                                locale="ru"
                                label-help="Используйте клавиши для передвижения по календарю"
                                label-no-date-selected="Дата выпуска"
                                :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
                                v-model="editedItem.equip_date_of_release"
                            ></b-form-datepicker>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-12">
                            <label>Комментарий</label>
                            <b-form-textarea
                                id="equip_comment"
                                v-model="editedItem.equip_comment"
                                placeholder="Если есть что отметить по прибору, отметьте."
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

        <!-- <b-sidebar
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
            </div>
        </b-sidebar>         -->
    </div>
</template>

<script>
    import {API_EQUIPMENT} from "../../constants"
    // import PrescriptionDetails from "./PrescriptionDetails"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
    } from '../../mixins'

    const initialEditedItem = () => ({
        id: '',
        equip_user_id: '',
        equip_contract_id: '',
        equip_type_reference_id: '',

        equip_mark: '',
        equip_date_of_release: '',
        equip_passport: '',
        equip_comment: '',
        equip_user: {},
        equip_contract: {},
        equip_type: {},
    })

    export default {
        components: {
            // "prescription-details": PrescriptionDetails
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
        },
        data() {
            return {
                changeLoad: false,
                tableApiUrl: API_EQUIPMENT,
                componentRefreshKey: 0,
                user: auth.user,
                optionsEquipment: [
                    { text: 'Варочная панель ВП-2 (2 конфорки)', value: 9 },
                    { text: 'Варочная панель ВП-3 (3 конфорки)', value: 10 },
                    { text: 'Варочная панель ВП-4 (4 конфорки)', value: 11 },
                    { text: 'Газовая плита ПГ-2 (2 конфорки)', value: 12 },
                    { text: 'Газовая плита ПГ-3 (3 конфорки)', value: 13 },
                    { text: 'Газовая плита ПГ-4 (4 конфорки)', value: 14 },
                    { text: 'Проточный водонагреватель ПВ', value: 15 },
                    { text: 'Газовый котел', value: 16 }
                ],
            }
        },
        watch: {
            "editedItem.equip_date_of_release": function(value) {
                this.editedItem.equip_date_of_release = value ?
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
                    this.editedItem.equip_contract_id = this.contract_id
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
                    this.editedItem.equip_contract_id = this.contract_id
                }

                this.isSidebarOpen = false
            },
            submitForm(event) {
                event.preventDefault()
                let form = document.getElementById("EquipmentForm")
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
                this.$bvToast.toast(variant === 'success' ? 'Оборудование сохранено' : 'Оборудование удалено', {
                    title: `Оповещение`,
                    variant: variant,
                    solid: true
                })
            },
            modalTitle() {
                let title = 'Добавление оборудования'
                if (this.editIndex > -1) {
                    title = 'Редактирование оборудования #' + this.editedItem.id
                }

                return title
            },
            updateParentData() {
                this.$emit("updateParentData")
            }
        }
    }
</script>
