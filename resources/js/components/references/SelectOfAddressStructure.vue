<template>
    <div class="row">
        <div :class="needAddButton ? 'col-10 pr-0':'col-12'">
            <multiselect
                v-model="selectedPrepare"
                id="ajax"
                label="name"
                track-by="id"
                placeholder="Начните вводить название..."
                open-direction="bottom"
                :options="options"
                :multiple="false"
                :searchable="true"
                :loading="loading"
                :internal-search="false"
                :clear-on-select="false"
                :close-on-select="true"
                :hide-selected="false"
                :select-label="'Выбрать'"
                :selected-label="'Выбран'"
                :deselect-label="'Отменить'"
                :disabled="disabled"
                :limit-text="limitText"
                :max-height="600"
                :show-no-results="true"
                @search-change="asyncFind"
            >
                <template
                    slot="option"
                    slot-scope="{ option }">
                    <span class="custom__tag">
                        <span>{{ option.name }}</span>
                    </span>
                </template>
                <span slot="noResult">Такого значения не найдено. Попробуйте изменить параметры поиска или заведите новую единицу.</span>
                <span slot="noOptions">Список пуст. Введите что-нибудь.</span>
            </multiselect>
        </div>
        <div class="col-2 pl-0 pt-0" style="display:flex;" v-if="needAddButton && !selectedLocal">
            <b-button v-if="!addElement" @click="addElement=true" variant="secondary" style="width: 50px;"><i class="fas fa-plus"></i></b-button>
            <b-button v-if="addElement" @click="addElement=false" variant="secondary" style="width: 50px;"><i class="fas fa-minus"></i></b-button>
        </div>
        <div class="col-12 mt-2" v-if="addElement">
            <div class="row">
                <div :class="needZipCode ? 'col-4 pr-0' : 'col-10 pr-0'">
                    <b-form-input id="newElementValue" v-model="newElementValue" :placeholder="mainInputPlaceholder"></b-form-input>
                </div>
                <div class="col-3 pr-0" v-if="needZipCode">
                    <b-form-input id="newZipCode" v-model="newZipCode" placeholder="Индекс"></b-form-input>
                </div>
                <div class="col-3 pr-0" v-if="needBuildYearCode">
                    <b-form-input id="newBuildYear" v-model="newBuildYear" placeholder="Год постройки"></b-form-input>
                </div>
                <div class="col-2 pl-0" style="display:flex;">
                    <b-button 
                        @click="saveNewElemnt()" 
                        variant="success" 
                        style="width: 50px;"
                        :disabled="newElementValue ? false:true"
                    >
                        <i class="fas fa-check" v-if="!savingProcess"></i>
                        <i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i>
                    </b-button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {
        API_REGIONS,
        API_CITIES,
        API_STREETS,
        API_HOUSES,
        API_FLATS
    } from '../../constants'
    import debounce from '../../services/helpers'

    export default {
        props: {
            structure: { type: String, required: false, default: 'regions' }, // What address structure to load
            defaultSelectName: {
                type: String,
                required: false,
                default: 'Выберите значение'
            },
            selected: { type: String|Number, required: false },            // What to output in default select
            selectedStructure: { type: Object, required: false },          // What to output in default select
            hotUpdate: { type: Boolean, required: false },                 // If we want to update selectedPrepare
            disabled: { type: Boolean, required: false, default: false },  // If we want to disable but show
            needAddButton: { type: Boolean, required: false, default: false },  // If we want to add new
            needZipCode: { type: Boolean, required: false, default: false },  // If we want to add zip
            needBuildYearCode: { type: Boolean, required: false, default: false },  // If we want to add build year
            mainInputPlaceholder: { type: String, required: false, default: 'Введите значение' },  // If we want to change input placeholder
            region_id: { type: String|Number, required: false, default: '' }, 
            city_id: { type: String|Number, required: false, default: '' }, 
            street_id: { type: String|Number, required: false, default: '' }, 
            house_id: { type: String|Number, required: false, default: '' }, 
            flat_id: { type: String|Number, required: false, default: '' }, 
        },
        data() {
            return {
                options: [],
                selectedLocal: '',
                selectedPrepare: {},
                search: '',
                loading: false,
                structures: [],
                addElement: false,
                newElementValue: '',
                savingProcess: false,
                newZipCode: '',
                newBuildYear: ''
            }
        },
        watch: {
            selectedPrepare(element) {
                this.selectedLocal = (element == null || element == '') ? '' : element.id
                this.$emit('set', this.selectedLocal)
            },
            hotUpdate(value) {
                if (this.selectedStructure && this.selectedStructure.id) {
                    this.options.push(this.selectedStructure)
                    this.selectedPrepare = this.selectedStructure
                }
            },
            region_id(value) {
                this.selectedLocal = ''
                this.getDataHandler()
            },
            city_id(value) {
                this.selectedLocal = ''
                this.getDataHandler()
            },
            street_id(value) {
                this.selectedLocal = ''
                this.getDataHandler()
            },
            house_id(value) {
                this.selectedLocal = ''
                this.getDataHandler()
            }
        },
        mounted() {
            this.structures = {
                'regions': API_REGIONS,
                'cities': API_CITIES,
                'streets': API_STREETS,
                'houses': API_HOUSES,
                'flats': API_FLATS
            }

            if (this.selectedStructure && this.selectedStructure.id) {
                this.options.push(this.selectedStructure)
                this.selectedPrepare = this.selectedStructure
            } else {
                this.getDataHandler()
            }
        },
        methods: {
            getDataHandler: async function() {
                let search = this.search ? this.search : ''
                let finalGet = `?q=${search}&region_id=${this.region_id}&city_id=${this.city_id}&street_id=${this.street_id}&house_id=${this.house_id}&house_id=${this.flat_id}`
                const response = await api.call('get', this.structures[this.structure] + `${finalGet}`)
                this.options = response.data.data
                this.prepareSelectedLocal()
                this.loading = false
            },
            asyncFind: debounce(function (searchQuery) {
                this.loading = true
                this.search = searchQuery
                this.getDataHandler()
            }, 500),
            prepareSelectedLocal() {
                this.options.forEach((element) => {
                    if (this.selected == element.id) {
                        this.selectedPrepare = element
                    }
                })
            },
            saveNewElemnt() {
                this.savingProcess = true

                let editedItem = {
                    name: this.newElementValue,
                }

                if (this.region_id) {
                    editedItem.region_id = this.region_id
                }
                if (this.city_id) {
                    editedItem.city_id = this.city_id
                }
                if (this.street_id) {
                    editedItem.street_id = this.street_id
                }
                if (this.house_id) {
                    editedItem.house_id = this.house_id
                }
                if (this.flat_id) {
                    editedItem.flat_id = this.flat_id
                }
                if (this.needZipCode) {
                    editedItem.zip = this.newZipCode
                }
                if (this.needBuildYearCode) {
                    editedItem.build_year = this.newBuildYear
                }

                api.call("post", this.structures[this.structure], editedItem).then(({data}) => {
                    this.options.push(data)                    
                    this.savingProcess = false
                    this.addElement = false
                })
            },
            limitText (count) {
                return `и ${count} других значений`
            }
        }
    }
</script>
