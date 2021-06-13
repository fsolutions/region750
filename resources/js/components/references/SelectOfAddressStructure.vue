<template>
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
                <span>{{ '#' + option.id + ' ' + option.name }}</span>
            </span>
        </template>
        <span slot="noResult">Такого значения не найдено. Попробуйте изменить параметры поиска или заведите новую единицу.</span>
        <span slot="noOptions">Список пуст. Введите что-нибудь.</span>
    </multiselect>
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
        },
        data() {
            return {
                options: [],
                selectedLocal: '',
                selectedPrepare: {},
                search: '',
                loading: false,
                structures: []
            }
        },
        watch: {
            selectedPrepare(element) {
                this.selectedLocal = element == null ? '' : element.id
                this.$emit('set', this.selectedLocal)
            },
            hotUpdate(value) {
                if (this.selectedStructure && this.selectedStructure.id) {
                    this.options.push(this.selectedStructure)
                    this.selectedPrepare = this.selectedStructure
                }
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
            }
        },
        methods: {
            getDataHandler: async function() {
                const response = await api.call('get', this.structures[this.structure] + `?q=${this.search}`)
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
            limitText (count) {
                return `и ${count} других значений`
            }
        }
    }
</script>
