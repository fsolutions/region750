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
            :customAddButtonName="'Добавить адрес'"
            :onDataEmptyMessage="'Сохраненные адреса отсутствуют. Создайте новый адрес.'"
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
                <form class="needs-validation mb-4" novalidate id="FlatForm">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5 class="mb-3">Заполните данные по адресу</h5>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="address_region">Выберите регион</label>
                            <select-address-structure
                                id="address_region"
                                :structure="`regions`"
                                :needNullElement="true"
                                :selected="editedItem.region_id"
                                :selectedStructure="editedItem.region"
                                @setRegion="setRegion"
                            ></select-address-structure>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address_city">Выберите город</label>
                            <select-address-structure
                                id="address_city"
                                :structure="`cities`"
                                :needNullElement="true"
                                :selected="editedItem.city_id"
                                :selectedStructure="editedItem.city"
                                :disabled="editedItem.region_id ? false : true"
                                @setCity="setCity"
                            ></select-address-structure>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address_street">Выберите улицу</label>
                            <select-address-structure
                                id="address_street"
                                :structure="`streets`"
                                :needNullElement="true"
                                :selected="editedItem.street_id"
                                :selectedStructure="editedItem.street"
                                :disabled="(editedItem.region_id && editedItem.city_id) ? false : true"
                                @setStreet="setStreet"
                            ></select-address-structure>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="address_house">Выберите дом</label>
                            <select-address-structure
                                id="address_house"
                                :structure="`houses`"
                                :needNullElement="true"
                                :selected="editedItem.house_id"
                                :selectedStructure="editedItem.house"
                                :disabled="(editedItem.region_id && editedItem.city_id && editedItem.street_id) ? false : true"
                                @setHouse="setHouse"
                            ></select-address-structure>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="name">Номер квартиры</label>
                            <input v-model="editedItem.name" type="text" class="form-control" id="name">
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
    import {API_FLATS} from "../../constants"
    import PrescriptionDetails from "./PrescriptionDetails"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
    } from '../../mixins'

    const initialEditedItem = () => ({
        id: '',
        name: '',
        region_id: '',
        city_id: '',
        street_id: '',
        house_id: '',
        region: {},
        city: {},
        street: {},
        flat: {}
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
            isNeedCreate: { type: Boolean, required: false, default: true },        // If table needs create new element button
            additionalGetParameter: { type: String, required: false, default: '' }, // If we want to add something, for example, &order_id=10
            typeOfTableFilter: { type: String, required: false, default: '' },      // Type of filter for output: orders, services
        },
        data() {
            return {
                changeLoad: false,
                tableApiUrl: API_FLATS,
                componentRefreshKey: 0,
                user: auth.user,
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
                let form = document.getElementById("FlatForm")
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
                this.$bvToast.toast(variant === 'success' ? 'Адрес сохранен' : 'Адрес удален', {
                    title: `Оповещение`,
                    variant: variant,
                    solid: true
                })
            },
            modalTitle() {
                let title = 'Добавление адреса'
                if (this.editIndex > -1) {
                    title = 'Редактирование адреса #' + this.editedItem.id
                }

                return title
            },
            setRegion(value) {
                this.editedItem.region_id = value
            },
            setCity(value) {
                this.editedItem.city_id = value
            },
            setStreet(value) {
                this.editedItem.street_id = value
            },
            setHouse(value) {
                this.editedItem.house_id = value
            },
            updateParentData() {
                this.$emit("updateParentData")
            }
        }
    }
</script>
