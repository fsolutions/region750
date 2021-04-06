<template>
    <div>
        <template v-if="needMultipleSelect">
            <multiselect
                v-model="selectedTags"
                tag-placeholder=""
                :placeholder="defaultSelectName ? defaultSelectName : 'Начните вводить значение...'"
                select-label=""
                label="text"
                track-by="value"
                :options="options"
                :multiple="true"
                :taggable="true"
                :hide-selected="true"
                :searchable="internalSearch"
                :loading="loading"
                open-direction="bottom"
            >
                <span slot="noResult">Ничего не найдено. Попробуйте снова...</span>
            </multiselect>
        </template>
        <template v-if="!needMultipleSelect && !needGroups">
            <multiselect
                v-model="selectedPrepare"
                :placeholder="defaultSelectName ? defaultSelectName : 'Начните вводить значение...'"
                select-label=""
                label="text"
                track-by="value"
                :options="options"
                :hide-selected="true"
                :searchable="internalSearch"
                :loading="loading"
                open-direction="bottom"
            >
                <span slot="noResult">Ничего не найдено. Попробуйте снова...</span>
            </multiselect>
        </template>
        <template v-if="!needMultipleSelect && needGroups">
            <multiselect
                v-model="selectedPrepare"
                :placeholder="defaultSelectName ? defaultSelectName : 'Начните вводить значение...'"
                select-label=""
                label="text"
                track-by="value"
                group-values="options"
                group-label="label"
                :group-select="false"
                :options="options"
                :hide-selected="true"
                :searchable="internalSearch"
                :loading="loading"
                open-direction="bottom"
            >
                <span slot="noResult">Ничего не найдено. Попробуйте снова...</span>
            </multiselect>
        </template>
    </div>
</template>

<script>
    import {
        API_REFERENCE,
        API_REFERENCE_PROPERTIES
    } from '../../constants'

    export default {
        props: {
            reference_id: { type: Number, required: false },       // What reference properties list to load
            reference_parent_id: { type: Number, required: false },// What reference list to load
            index: { type: String|Number, required: false, default: -1 },       // What index to save
            needNullElement: {
                type: Boolean,
                required: true,
                default: false
            },                                                    // Need default value in select list
            needMultipleSelect: {
                type: Boolean,
                required: false,
                default: false
            },                                                    // Need multiple selection
            defaultSelectName: {
                type: String,
                required: false,
                default: 'Выберите один из вариантов'
            },                                                   // What to output in default select
            selected: { type: String|Number, required: false },
            selectedArray: { type: Array|String, required: false },
            internalSearch: { type: String|Number, required: false, default: true },
            needGroups: { type: Boolean, required: false, default: false },
            excludeArray: { type: Array,
                            required: false,
                            default: function () {
                                return []
                            }
                          },      // If you want to exclude properties
        },
        data() {
            return {
                options: [],
                selectedTags: [],
                selectedArrayLocal: [],
                selectedLocal: '',
                selectedPrepare: {},
                loading: true
            }
        },
        mounted() {
            this.getDataHandler()
        },
        computed: {
            // selectedLocal: {
            //     get: function() {
            //         return this.selected
            //     },
            //     set: function(value) {
            //         this.$emit('set', value, this.reference_id, this.index)
            //     }
            // },
        },
        watch: {
            selectedTags(tags) {
                this.selectedArrayLocal = []
                tags.forEach((tag) => {
                    this.selectedArrayLocal.push(tag.value)
                })

                this.$emit('set', this.selectedArrayLocal)
            },
            selectedPrepare(element) {
                this.selectedLocal = element.value
                this.$emit('set', this.selectedLocal, this.reference_id, this.index)
            }
        },
        methods: {
            getDataHandler: async function() {
                let response
                if (this.reference_id) {
                    response = await api.call('get', API_REFERENCE_PROPERTIES + `?reference_id=${this.reference_id}`)
                } else if (this.reference_parent_id) {
                    response = await api.call('get', API_REFERENCE + `?reference_parent_id=${this.reference_parent_id}`)
                }

                // Sort if no need grouping
                if (!this.needGroups) {
                    const sortByText = (a, b) => (a['text'] > b['text'] && 1) ||
                        (a['text'] === b['text'] ? 0 : -1)
                    response.data = response.data.sort(sortByText)
                }

                let resultArray = []
                if (this.excludeArray.length > 0) {
                    response.data.forEach((option, index) => {
                        if (!this.excludeArray.includes(option.value)) {
                            resultArray.push(option)
                        }
                    })
                } else {
                    resultArray = response.data
                }

                this.options = resultArray
                this.loading = false
                if (this.needMultipleSelect) {
                    this.prepareTags()
                } else {
                    this.prepareSelectedLocal()
                }
                if (this.needNullElement) {
                    this.options.unshift({value: null, text: this.defaultSelectName})
                } else {
                    // this.options.unshift({value: null, text: this.defaultSelectName, disabled: true})
                }
            },
            prepareTags() {
                this.options.forEach((tag) => {
                    if (this.selectedArray.includes(tag.value)) {
                        this.selectedTags.push(tag)
                    }
                })
            },
            // @TODO: need recursive if will be more levels
            prepareSelectedLocal() {
                this.options.forEach((element) => {
                    if (this.selected == element.value) {
                        this.selectedPrepare = element
                    }
                    // check for second level select
                    if (element.options && element.options.length > 0) {
                        element.options.forEach((elementChild) => {
                            if (this.selected == elementChild.value) {
                                this.selectedPrepare = elementChild
                            }
                        })
                    }
                })
            }
        }
    }
</script>
