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
            :onDataEmptyMessage="'Сохраненные ТО отсутствуют. Создайте новое событие.'"
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
                <form class="needs-validation mb-4" novalidate id="TOVentilationForm">
                    <!-- <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Заполните данные по ТО вентканалов и дымоходов</h5>
                        </div>
                    </div> -->
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="address_region">Выберите область</label>
                            <select-address-structure
                                id="address_region"
                                :structure="`regions`"
                                :mainInputPlaceholder="'Название области'"
                                :needNullElement="true"
                                :needAddButton="true"
                                :selected="editedItem.ventilation_region_id"
                                :selectedStructure="editedItem.ventilation_region"
                                @set="setRegion"
                            ></select-address-structure>
                        </div>
                    </div>
                    <div class="form-row" v-if="editedItem.ventilation_region_id">
                        <div class="form-group col-md-6">
                            <label for="address_city">Выберите город</label>
                            <select-address-structure
                                id="address_city"
                                :structure="`cities`"
                                :mainInputPlaceholder="'Название города'"
                                :needNullElement="true"
                                :needAddButton="true"
                                :selected="editedItem.ventilation_city_id"
                                :selectedStructure="editedItem.ventilation_city"
                                :region_id="editedItem.ventilation_region_id"
                                @set="setCity"
                            ></select-address-structure>
                        </div>
                    </div>
                    <div class="form-row" v-if="(editedItem.ventilation_region_id && editedItem.ventilation_city_id)">
                        <div class="form-group col-md-6">
                            <label for="address_street">Выберите улицу</label>
                            <select-address-structure
                                id="address_street"
                                :structure="`streets`"
                                :mainInputPlaceholder="'Название улицы'"
                                :needNullElement="true"
                                :needAddButton="true"
                                :selected="editedItem.ventilation_street_id"
                                :selectedStructure="editedItem.ventilation_street"
                                :region_id="editedItem.ventilation_region_id"
                                :city_id="editedItem.ventilation_city_id"
                                @set="setStreet"
                            ></select-address-structure>
                        </div>
                    </div>
                    <div class="form-row" v-if="(editedItem.ventilation_region_id && editedItem.ventilation_city_id && editedItem.ventilation_street_id)">
                        <div class="form-group col-md-6">
                            <label for="address_house">Выберите дом</label>
                            <select-address-structure
                                id="address_house"
                                :structure="`houses`"
                                :mainInputPlaceholder="'Номер дома'"
                                :needNullElement="true"
                                :needAddButton="true"
                                :selected="editedItem.ventilation_house_id"
                                :selectedStructure="editedItem.ventilation_house"
                                :region_id="editedItem.ventilation_region_id"
                                :city_id="editedItem.ventilation_city_id"
                                :street_id="editedItem.ventilation_street_id"
                                :needZipCode="true"
                                @set="setHouse"
                            ></select-address-structure>
                        </div>
                    </div>
                    <div class="form-row" v-if="(editedItem.ventilation_region_id && editedItem.ventilation_city_id && editedItem.ventilation_street_id && editedItem.ventilation_house_id)">
                        <div class="form-group col-md-4">
                            <label for="name">Дата проведения работ</label>
                            <b-form-datepicker 
                                id="ventilation_date_of_work" 
                                placeholder="Выберите дату" 
                                locale="ru"
                                label-help="Используйте клавиши для передвижения по календарю"
                                label-no-date-selected="Выберите дату"
                                v-model="editedItem.ventilation_date_of_work"
                            ></b-form-datepicker>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="role">Статус</label>
                            <b-form-select v-model="editedItem.ventilation_status" required :options="statusList" id="ventilation_status"></b-form-select>
                        </div>
                        <div class="form-group col-md-8">
                            <label for="ventilation_master_user_id">Мастер на выполнение ТО</label>
                            <select-user
                                id="ventilation_master_user_id"
                                :roles="`administrator||master||intern`"
                                :needNullElement="true"
                                :selected="editedItem.ventilation_master_user_id"
                                :selectedUser="editedItem.ventilation_master"
                                @set="setMasterOfTO"
                            ></select-user>
                        </div>
                        <div class="form-group col-md-8">
                            <label for="ventilation_comment">Комментарий по ТО</label>
                            <label>Комментарий (виден только коллегам)</label>
                            <b-form-textarea
                                id="ventilation_comment"
                                v-model="editedItem.ventilation_comment"
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
                <prescription-details
                    :detailedItem="detailedItem"
                ></prescription-details>
            </div>
        </b-sidebar>         -->
    </div>
</template>

<script>
    import {API_TO_VENTILATION} from "../../constants"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
    } from '../../mixins'

    const initialEditedItem = () => ({
        id: '',
        ventilation_region_id: '',
        ventilation_city_id: '',
        ventilation_street_id: '',
        ventilation_house_id: '',
        ventilation_master_user_id: '',
        ventilation_region: {},
        ventilation_city: {},
        ventilation_street: {},
        ventilation_house: {},
        ventilation_master: {},
        ventilation_comment: '',
        ventilation_status: 'Запланировано',
        ventilation_date_of_work: ''
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
            isNeedCreate: { type: Boolean, required: false, default: true },        // If table needs create new element button
            additionalGetParameter: { type: String, required: false, default: '' }, // If we want to add something, for example, &order_id=10
            typeOfTableFilter: { type: String, required: false, default: 'flats' },      // Type of filter for output: orders, services
        },
        data() {
            return {
                changeLoad: false,
                tableApiUrl: API_TO_VENTILATION,
                componentRefreshKey: 0,
                user: auth.user,
                statusList: [
                    'Запланировано',
                    'Проведено',
                    'Отменено',
                    'Перенесено'
                ],
            }
        },
        watch: {
        },
        mounted() {
            this.editedItem = initialEditedItem()
        },
        methods: {
            // calls from mixin before sidebaropened
            beforeSidebarOpenedCallback() {
                this.editedItem = initialEditedItem()
            },
            // calls from mixin on item save
            onCreatedOrUpdatedCallback() {
                if (this.editIndex == -1) {
                }

                this.isSidebarOpen = false
            },
            submitForm(event) {
                event.preventDefault()
                let form = document.getElementById("TOVentilationForm")
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
                this.$bvToast.toast(variant === 'success' ? 'ТО сохранена' : 'ТО удалено', {
                    title: `Оповещение`,
                    variant: variant,
                    solid: true
                })
            },
            modalTitle() {
                let title = 'Добавление ТО вентканалов и дымоходов'
                if (this.editIndex > -1) {
                    title = 'Редактирование ТО #' + this.editedItem.id
                }

                return title
            },
            setRegion(value) {
                this.editedItem.ventilation_region_id = value
                this.editedItem.ventilation_city_id = ''
                this.editedItem.ventilation_street_id = ''
                this.editedItem.ventilation_house_id = ''
            },
            setCity(value) {
                this.editedItem.ventilation_city_id = value
                this.editedItem.ventilation_street_id = ''
                this.editedItem.ventilation_house_id = ''
            },
            setStreet(value) {
                this.editedItem.ventilation_street_id = value
                this.editedItem.ventilation_house_id = ''
            },
            setHouse(value) {
                this.editedItem.ventilation_house_id = value
            },
            setMasterOfTO(value) {
                this.editedItem.ventilation_master_user_id = value
            },
            updateParentData() {
                this.$emit("updateParentData")
            }
        }
    }
</script>
