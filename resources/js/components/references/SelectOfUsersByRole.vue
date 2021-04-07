<template>
    <multiselect
        v-model="selectedPrepare"
        id="ajax"
        label="name"
        track-by="id"
        placeholder="Начните вводить телефон, e-mail или ФИО..."
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
        :limit-text="limitText"
        :max-height="600"
        :show-no-results="true"
        @search-change="asyncFind"
    >
        <template
            slot="option"
            slot-scope="{ option }">
            <span class="custom__tag">
                <span>{{ '#' + option.id + ' ' + option.name + ' (+' + option.phone + ')' }}</span>
            </span>
        </template>
        <span slot="noResult">Такого пользователя не найдено. Попробуйте изменить параметры поиска или заведите нового пользователя.</span>
        <span slot="noOptions">Список пуст. Введите что-нибудь.</span>
    </multiselect>
</template>
<script>
    import {
        API_USERS
    } from '../../constants'
    import debounce from '../../services/helpers'

    export default {
        props: {
            roles: { type: String, required: true },             // What roles user list to load, can use multiple by separating slugs with ||
            defaultSelectName: {
                type: String,
                required: false,
                default: 'Выберите пользователя'
            },
            selected: { type: String|Number, required: false },          // What to output in default select
            selectedUser: { type: Object, required: false },          // What to output in default select
            hotUpdate: { type: Boolean, required: false }          // If we want to update selectedPrepare
        },
        data() {
            return {
                options: [],
                selectedLocal: '',
                selectedPrepare: {},
                search: '',
                loading: false
            }
        },
        watch: {
            selectedPrepare(element) {
                this.selectedLocal = element == null ? '' : element.id
                this.$emit('set', this.selectedLocal)
            },
            hotUpdate(value) {
                if (this.selectedUser && this.selectedUser.id) {
                    this.options.push(this.selectedUser)
                    this.selectedPrepare = this.selectedUser
                }
            }
        },
        mounted() {
            if (this.selectedUser && this.selectedUser.id) {
                this.options.push(this.selectedUser)
                this.selectedPrepare = this.selectedUser
            }
        },
        methods: {
            getDataHandler: async function() {
                const response = await api.call('get', API_USERS + `?s=${this.search}&roles=${this.roles}`)
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
                return `и ${count} других пользователей`
            }
        }
    }
</script>
