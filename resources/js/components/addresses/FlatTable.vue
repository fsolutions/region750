<template>
    <div>
        <app-table
            :name="'flat'"
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
                <form class="needs-validation mb-4" novalidate :id="(editIndex == -1) ? 'FlatForm' : 'HouseEditForm'">
                    <div v-if="editIndex == -1">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="mb-3">Заполните данные по адресу</h5>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="address_region">Выберите область</label>
                                <select-address-structure
                                    id="address_region"
                                    :structure="`regions`"
                                    :mainInputPlaceholder="'Название области'"
                                    :needNullElement="true"
                                    :needAddButton="true"
                                    :selected="editedItem.region_id"
                                    :selectedStructure="editedItem.region"
                                    @set="setRegion"
                                ></select-address-structure>
                            </div>
                        </div>
                        <div class="form-row" v-if="editedItem.region_id">
                            <div class="form-group col-md-8">
                                <label for="address_city">Выберите город</label>
                                <select-address-structure
                                    id="address_city"
                                    :structure="`cities`"
                                    :mainInputPlaceholder="'Название города'"
                                    :needNullElement="true"
                                    :needAddButton="true"
                                    :selected="editedItem.city_id"
                                    :selectedStructure="editedItem.city"
                                    :region_id="editedItem.region_id"
                                    @set="setCity"
                                ></select-address-structure>
                            </div>
                        </div>
                        <div class="form-row" v-if="(editedItem.region_id && editedItem.city_id)">
                            <div class="form-group col-md-8">
                                <label for="address_street">Выберите улицу</label>
                                <select-address-structure
                                    id="address_street"
                                    :structure="`streets`"
                                    :mainInputPlaceholder="'Название улицы'"
                                    :needNullElement="true"
                                    :needAddButton="true"
                                    :selected="editedItem.street_id"
                                    :selectedStructure="editedItem.street"
                                    :region_id="editedItem.region_id"
                                    :city_id="editedItem.city_id"
                                    @set="setStreet"
                                ></select-address-structure>
                            </div>
                        </div>
                        <div class="form-row" v-if="(editedItem.region_id && editedItem.city_id && editedItem.street_id)">
                            <div class="form-group col-md-8">
                                <label for="address_house">Выберите дом</label>
                                <select-address-structure
                                    id="address_house"
                                    :structure="`houses`"
                                    :mainInputPlaceholder="'Номер дома'"
                                    :needNullElement="true"
                                    :needAddButton="true"
                                    :selected="editedItem.house_id"
                                    :selectedStructure="editedItem.house"
                                    :region_id="editedItem.region_id"
                                    :city_id="editedItem.city_id"
                                    :street_id="editedItem.street_id"
                                    :needZipCode="true"
                                    :needBuildYearCode="true"
                                    @set="setHouse"
                                ></select-address-structure>
                            </div>
                        </div>
                        <div class="form-row" v-if="(editedItem.region_id && editedItem.city_id && editedItem.street_id && editedItem.house_id)">
                            <div class="form-group col-md-3">
                                <label for="name">Номер квартиры (поставьте -, если номера квартиры нет)</label>
                                <input v-model="editedItem.name" type="text" class="form-control" id="name">
                            </div>

                        </div>
                    </div>
                    <div v-if="editIndex != -1">
                        <div class="row">
                            <div class="col-sm-12">
                                <h5 class="mb-3">Редактирование информации по дому №{{editedItem.house.name}}</h5>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-3">
                                <b-form-input id="newZipCode" v-model="editedItem.house.zip" placeholder="Индекс"></b-form-input>
                            </div>
                            <div class="col-3">
                                <b-form-input id="newBuildYear" v-model="editedItem.house.build_year" placeholder="Год постройки"></b-form-input>
                            </div>
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
    import {API_FLATS, API_HOUSES} from "../../constants"
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
        flat: {},
        house: {}
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
            typeOfTableFilter: { type: String, required: false, default: 'flats' },      // Type of filter for output: orders, services
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
                let editHouse = false
                let form = document.getElementById("FlatForm")
                if (!form) {
                    form = document.getElementById("HouseEditForm")
                    editHouse = true
                }
                if (form.checkValidity() === false) {
                    event.preventDefault()
                    event.stopPropagation()
                    form.classList.add('was-validated')
                    return false
                }

                if (!editHouse) {
                    this.createOrUpdateItem()
                } else {
                    this.updateHouseItem()
                }
            },
            updateHouseItem() {
                this.savingProcess = true
                if (typeof this.onCreateOrUpdateItemCallback === 'function') {
                    this.onCreateOrUpdateItemCallback()
                }
                api.call("put", `${API_HOUSES}/${this.editedItem.house_id}`, this.editedItem.house).then(({data}) => {
                    // this.items.data[this.editIndex] = { ...data }
                    Object.assign(this.items.data[this.editIndex].house, data)
                    if (typeof this.onCreatedOrUpdatedCallback === 'function') {
                        this.onCreatedOrUpdatedCallback(data)
                    }
                    this.editIndex = -1,
                    this.savingProcess = false
                    this.validationErrors = ''
                    this.closeSidePanelCallback(),
                    this.makeToast('success')
                }).catch((response) => {
                    if (response.status == 422){
                        this.validationErrors = response.data.error
                    }
                })

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
                this.editedItem.city_id = ''
                this.editedItem.street_id = ''
                this.editedItem.house_id = ''
                this.editedItem.name = ''

                this.editedItem.city = Object.assign({})
                this.editedItem.street = Object.assign({})
                this.editedItem.house = Object.assign({})
                
                this.editedItem.region_id = value
            },
            setCity(value) {
                this.editedItem.street_id = ''
                this.editedItem.house_id = ''
                this.editedItem.name = ''

                this.editedItem.street = Object.assign({})
                this.editedItem.house = Object.assign({})

                this.editedItem.city_id = value
            },
            setStreet(value) {
                this.editedItem.house_id = ''
                this.editedItem.name = ''

                this.editedItem.house = Object.assign({})

                this.editedItem.street_id = value
            },
            setHouse(value) {
                this.editedItem.name = ''
                this.editedItem.house_id = value
            },
            updateParentData() {
                this.$emit("updateParentData")
            }
        }
    }
</script>
