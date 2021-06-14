<template>
    <div>
        <app-table
            v-if="onlyForBackOffice()"
            :name="'contract'"
            :typeOfTableFilter="'contracts'"
            :api="tableApiUrl"
            :items.sync="items"
            :isNeedSearch="true"
            :customAddButtonName="`Добавить договор`"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
            @openContractTOCreateModal="openContractTOCreateModal"
        ></app-table>

        <contract-cards
            v-if="!onlyForBackOffice()"
            :items.sync="items"
            :api="tableApiUrl"
            @show="showItem"
            @edit="createOrEditItemModal"
        ></contract-cards>

        <b-sidebar
            v-if="isSidebarOpen"
            v-model="isSidebarOpen"
            id="sidebar-right"
            right
            backdrop
            shadow
            width="75em"
            backdrop-variant="dark"
            ref="editItem"
            :title="modalTitle()"
            @hidden="closeSidePanelCallback()"
        >
            <div class="d-block">
                <validation-errors :errors="validationErrors" v-if="validationErrors"></validation-errors>

                <form @submit.prevent="createOrUpdateItem()" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="contract_number">Номер договора</label>
                            <input v-model="editedItem.contract_number" required type="text" class="form-control" id="contract_number">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="contract_address">Адрес объекта из договора</label>
                            <multiselect
                                v-if="!editedItem.contract_address"
                                v-model="selectedAddress"
                                :placeholder="'Начните вводить адрес с города...'"
                                :required="true"
                                select-label="Выбрать"
                                label="value"
                                track-by="value"
                                :options="addresses"
                                :hide-selected="false"
                                :searchable="true"
                                :loading="loadingDadata"
                                :internal-search="false"
                                :clear-on-select="false"
                                :close-on-select="true"                                
                                @search-change="findAddress"
                                open-direction="bottom"
                                @select="onAddressSelect"
                            >
                                <span slot="noOptions">Пока ничего не найдено...</span>
                                <span slot="noResult">Ничего не найдено. Попробуйте снова...</span>
                            </multiselect>
                            <input v-if="editedItem.contract_address" v-model="editedItem.contract_address" required type="text" class="form-control" id="contract_address">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="contract_start_datetime">Дата заключения договора</label>
                            <!-- <input v-model="editedItem.contract_start_datetime" required type="date" class="form-control" id="contract_start_datetime"> -->
                            <b-form-datepicker 
                                id="contract_start_datetime" 
                                placeholder="Выберите дату" 
                                locale="ru"
                                label-help="Используйте клавиши для передвижения по календарю"
                                label-no-date-selected="Выберите дату"
                                v-model="editedItem.contract_start_datetime"
                            ></b-form-datepicker>
                        </div>
                        <div class="form-group col-md-5">
                            <label for="contract_start_datetime">Установленное оборудование</label>
                            <p class="mb-2"><small>Отметьте галочками, установленные в квартире/доме приборы.</small></p>
                            <b-form-group label="" v-slot="{ ariaDescribedby }">
                                <b-form-checkbox-group
                                    v-model="selectedEquipment"
                                    :options="optionsEquipment"
                                    :aria-describedby="ariaDescribedby"
                                    plain
                                    stacked
                                ></b-form-checkbox-group>
                            </b-form-group>                            
                        </div>
                        <div class="form-group col-md-7" v-if="selectedEquipment.length > 0">
                            <p class="mt-2 mb-2"><small>Укажите подробности о приборах, которые знаете. <br>Если не уверены - оставьте поля пустыми.</small></p>
                            <div v-for="(equip, index) in editedItem.preparedEquipment" :key="index" class="mb-2">
                                <b>{{equip.visName}}</b>
                                <div class="row mt-2">
                                    <span style="display:none;"><b-form-input v-model="equip.equip_type_reference_id" size="sm" placeholder="Номер паспорта"></b-form-input></span>
                                    <div class="col-4"><b-form-input v-model="equip.equip_passport" size="sm" placeholder="Номер паспорта"></b-form-input></div>
                                    <div class="col-4"><b-form-input v-model="equip.equip_mark" size="sm" placeholder="Марка прибора"></b-form-input></div>
                                    <div class="col-4">
                                        <b-form-datepicker 
                                            :id="`equip_date_of_release_${index}`" 
                                            size="sm"
                                            placeholder="Дата выпуска" 
                                            locale="ru"
                                            label-help="Используйте клавиши для передвижения по календарю"
                                            label-no-date-selected="Дата выпуска"
                                            :date-format-options="{ year: 'numeric', month: 'numeric', day: 'numeric' }"
                                            v-model="equip.equip_date_of_release"
                                        ></b-form-datepicker>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <template v-if="onlyForBackOffice()">
                            <div class="form-group col-md-12">
                                <label>Пользователь, на которого зарегистрирован договор</label>
                                <select-user
                                    id="contract_on_user_id"
                                    :roles="`client`"
                                    :needNullElement="true"
                                    :selected="editedItem.contract_on_user_id"
                                    :selectedUser="editedItem.contract_on_user"
                                    @set="setUserOfContract"
                                ></select-user>
                            </div>       
                            <div class="form-group col-md-12">
                                <label for="role">Статус</label>
                                <b-form-select v-model="editedItem.status" required :options="statusList" id="status"></b-form-select>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Комментарий (виден только коллегам)</label>
                                <b-form-textarea
                                    id="contract_comment"
                                    v-model="editedItem.contract_comment"
                                    placeholder="Если есть что отметить по контракту, отметьте."
                                    rows="3"
                                    max-rows="16"
                                ></b-form-textarea>
                            </div>                            
                        </template>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Сохранить<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                    </div>
                </form>
            </div>
        </b-sidebar>
        <b-sidebar
            v-if="isSidebarOpenDetail"
            v-model="isSidebarOpenDetail"
            id="sidebar-right"
            title="Детальная информация"
            right
            backdrop
            shadow
            width="85em"
            backdrop-variant="dark"
            no-close-on-backdrop
        >
            <contract-show-one
                :detailedItemIndex="detailedItemIndex"
                :detailedItem="detailedItem"
                @updateParentData="updateParentData"
            ></contract-show-one>
        </b-sidebar>
        <contract-to-create-edit
            v-if="operationForTO"
            :contractForTO="contractForTO"
            :contractForTOIndex="contractForTOIndex"
            :operationForTO="operationForTO"
            @clear="clearTOData"
        ></contract-to-create-edit>
  </div>
</template>

<script>
    import {API_CONTRACTS, API_DADATA_ADDRESS} from "../constants"
    import debounce from '../services/helpers'

    import ContractCards from "../components/contracts/Cards"
    import ContractTOCreateOrEdit from "../components/contracts/ContractTOCreateOrEdit"
    import ContractShowOne from "../components/contracts/ContractShowOne"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        actionRefreshItemByIndex
    } from '../mixins'

    const initialEditedItem = () => ({
        id: '',
        creator_user_id: '',
        contract_on_user_id: '',
        contract_address: '',
        contract_number: '',
        status: 'В обработке',
        contract_start_datetime: '',
        contract_comment: '',
        creator: {},
        contract_on_user: {},
        contract_to: {},
        contract_to_last: {},
        orders: {},
        prescriptions: {},
        equipment: [],
        preparedEquipment: []
    })

    export default {
    components: {
        "contract-cards": ContractCards,
        "contract-to-create-edit": ContractTOCreateOrEdit,
        "contract-show-one": ContractShowOne
    },
    mixins: [
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        actionRefreshItemByIndex
    ],
    data() {
        return {
            tableApiUrl: API_CONTRACTS,
            user: auth.user,
            statusList: [
                'В обработке',
                'Есть бумажный договор',
                'Нет бумажного договора',
                'Договор расторгнут',
                'Передать в ГЖИ',
                'Передано в ГЖИ'
            ],
            contractForTO: {},
            contractForTOIndex: -1,
            operationForTO: "",
            loadingDadata: false,
            daDataSearch: '',
            addresses: [],
            selectedAddress: '',
            selectedEquipment: [], 
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
            isFormingEquipmentList: false
        }
    },
    watch: {
        "editedItem.contract_start_datetime": function(value) {
            this.editedItem.contract_start_datetime = value ?
                this.$moment(value).format('YYYY-MM-DD') :
                ''
        },
        "selectedEquipment": function(value) {
            if (!this.isFormingEquipmentList) {
                this.formEquipmentList()
            }
        },
        "isSidebarOpen": function(value) {
            if (!value) {
                this.editedItem = initialEditedItem()
                this.selectedAddress = ''

                this.editedItem.preparedEquipment.splice(0, this.editedItem.preparedEquipment.length)
                this.selectedEquipment.splice(0, this.selectedEquipment.length)
            }
        },

// daDataSearch: debounce(function (value) {
        //     this.loadingDadata = true
        //     api.call('post', API_DADATA_ADDRESS, {'address': value})
        //         .then(({data}) => {
        //             this.addresses = data
        //             this.loadingDadata = false
        //         })
        // }, 700),
    },
    mounted() {
        this.editedItem = initialEditedItem()
    },
    methods: {
        formEquipmentList() {
            // Добавляем
            this.selectedEquipment.forEach((equipid, index) => {
                let ifHasElement = this.editedItem.preparedEquipment.filter(item => {
                    if (item.equip_type_reference_id == equipid) {
                        return item
                    }
                })

                if (ifHasElement.length == 0) {
                    this.editedItem.preparedEquipment.push({
                        'visName': this.takeNameOfEquipById(equipid),
                        'equip_type_reference_id': equipid,
                        'equip_passport': '',
                        'equip_mark': '',
                        'equip_date_of_release': '',
                    })
                }
            })

            // Удаляем
            this.editedItem.preparedEquipment = this.editedItem.preparedEquipment.filter(equip => {
                let tempId = equip.equip_type_reference_id
                let tempFinedArray = this.selectedEquipment.filter(itemId => {
                    if (itemId == tempId) {
                        return itemId
                    }
                })

                if (tempFinedArray.length > 0) {
                    return equip
                }
            })
        }, 
        takeNameOfEquipById(id) {
            let findedElement = this.optionsEquipment.filter((item) => {
                if (item.value == id) {
                    return item
                }
            })

            if (findedElement.length > 0) {
                return findedElement[0].text
            }
        },
        findAddress: debounce (function(query) {
            this.loadingDadata = true
            api.call('post', API_DADATA_ADDRESS, {'address': query})
                .then(({data}) => {
                    this.addresses = data
                    this.loadingDadata = false
                })
        }, 700),
        makeToast(variant = null) {
            this.$bvToast.toast(variant === 'success' ? 'Договор сохранен' : 'Договор удален', {
                title: `Оповещение`,
                variant: variant,
                solid: true
            })
        },
        modalTitle() {
            let title = 'Добавление договора'
            if (this.editIndex > -1) {
                title = 'Договор #' + this.editedItem.id
            }

            return title
        },
        selectAddress(data) {
        },
        // calls from mixin on item edit
        onItemEditModalCallback() {
            this.$set(this.editedItem, 'preparedEquipment', [])
            this.isFormingEquipmentList = true

            this.editedItem.equipment.forEach((item, index) => {
                this.selectedEquipment.push(item.equip_type_reference_id)
                this.editedItem.preparedEquipment.push({
                    'visName': this.takeNameOfEquipById(item.equip_type_reference_id),
                    'equip_type_reference_id': item.equip_type_reference_id,
                    'equip_passport': item.equip_passport ? item.equip_passport : '',
                    'equip_mark': item.equip_mark ? item.equip_mark : '',
                    'equip_date_of_release': item.equip_date_of_release ? this.$moment(item.equip_date_of_release).format('YYYY-MM-DD') : '',
                })
            })

            this.isFormingEquipmentList = false
        },
        // calls from mixin on item save
        onCreateOrUpdateItemCallback() {
            if (this.selectedAddress.value) {
                this.editedItem.contract_address = this.selectedAddress.value
            }

            this.editedItem.preparedEquipment.forEach((item, index) => {
                if (item.equip_date_of_release) {
                    item.equip_date_of_release = this.$moment(item.equip_date_of_release).format('YYYY-MM-DD')    
                }
            }) 
        },
        onAddressSelect(option) {
            if (option.value) {
                this.editedItem.contract_address = option.value
            }
        },
        // calls from mixin on item save
        onCreatedOrUpdatedCallback() {
            this.isSidebarOpen = false
            this.savingProcess = false
            this.editedItem = initialEditedItem()
            this.selectedAddress = ''
            
            this.editedItem.preparedEquipment.splice(0, this.editedItem.preparedEquipment.length)
            this.selectedEquipment.splice(0, this.selectedEquipment.length)

            if (this.editIndex != -1) {
                this.refreshItemByIndex(this.editIndex)
            }
        },
        // calls from mixin on modal close
        onSidePanelCallback() {
            this.editedItem = initialEditedItem()
            this.selectedAddress = ''

            this.editedItem.preparedEquipment.splice(0, this.editedItem.preparedEquipment.length)
            this.selectedEquipment.splice(0, this.selectedEquipment.length)
        },
        setUserOfContract(value) {
            this.editedItem.contract_on_user_id = value
        },
        onlyForBackOffice() {
            if (!this.user.role.includes("client")) {
                return true
            }
            return false
        },
        openContractTOCreateModal(index) {
            let id = this.items.data[index].id
            api.call("get", `${this.tableApiUrl}/${id}`).then(({data}) => {
                this.contractForTO = data
                this.contractForTOIndex = index
                // If this element exist in table lets update
                if (typeof this.items !== 'undefined' && this.items.data[index]) {
                    this.items.data[index] = Object.assign(this.items.data[index], data)
                }
            }).finally(() => {
                this.operationForTO = "create"
            })
        },
        clearTOData() {
            this.refreshItemByIndex(this.contractForTOIndex)
            this.contractForTO = initialEditedItem()
            this.contractForTOIndex = -1
            this.operationForTO = ''
        },
        updateParentData(index) {
            this.refreshItemByIndex(index)
        }
    }
  }

</script>
