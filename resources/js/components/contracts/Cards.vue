<template>
    <div>
        <div class='text-left text-nowrap'>
            <template>
                <button
                    @click="refreshDataInCards()"
                    class="btn button-reload-table mr-3 mb-3"
                >
                    <i class="fas fa-sync-alt reload-sync-icon" animation="spin" v-if="!isCardsRefreshing"></i>
                    <i class="fas fa-spinner fa-spin reload-sync-icon" v-if="isCardsRefreshing"></i>
                </button>
            </template>
            <template>
                <template v-if="(checkActionAllow('create') && isNeedCreate) || isNeedCreate">
                    <button
                        @click="createOrEditItemModal()"
                        type="button"
                        class="btn btn-outline-secondary btn-outline-pill mb-3 mr-3"
                    >
                        Добавить договор
                    </button>
                </template>
            </template>
        </div>
        <div class="contract-cards">
            <div 
                v-if="!dataOverlay"
                class="one_card my-2"
                v-for="(contract, index) in itemsLocal.data"
                :key="index"
            >
                <b-card>
                    <b-card-text>
                        <div class="row">
                            <div class="col-sm-4">
                                <h4 class="card-title">Договор №{{contract.contract_number}}</h4>
                                <p class="small-text">Адрес: {{ contract.contract_address}}</p>
                                <p>Дата последнего ТО-ВКГО: 06.03.2020</p>
                                <p>До следующего ТО-ВКГО: 30 дней</p>
                            </div>
                            <div class="col-sm-6 text-center">
                                <select-of-properties
                                    :reference_id="1"
                                    :needNullElement="false"
                                    :needMultipleSelect="true"
                                    :defaultSelectName="`Выберите нужные услуги`"
                                    :selectedArray="set_categories_ids"
                                    @set="setCategoriesOfOrder"
                                ></select-of-properties>

                                <b-button @click="orderMaster(index)" variant="primary" class="mt-3">Вызвать мастера</b-button>
                                <p class="text-small mb-1 mt-2">или позвоните нам</p>
                                <p><a href="tel:+79153788117" class="big-link">+7 (915) 378-81-17</a></p>
                            </div>
                        </div>
                    </b-card-text>
                </b-card>
            </div>
        </div>
        <b-overlay
            :show="dataOverlay"
            spinner-variant="success"
            no-wrap
        ></b-overlay>
    </div>
</template>
<script>
    import {
        checkActionAllow
    } from '../../mixins'

    export default {
        props: {
            items: { type: Object, required: true },                                // Main items Object
            api: { type: String, required: true },                                  // Link on API for cards working
            isNeedCreate: { type: Boolean, required: false, default: true },        // If table needs create new element button
        },
        mixins: [
            checkActionAllow
        ],
        data() {
            return {
                dataOverlay: true,
                isCardsRefreshing: true,
                set_categories_ids: []
            }
        },
        computed: {
            itemsLocal: {
                get: function() {
                    return this.items
                },
                set: function(value) {
                    this.$emit('update:items', value)
                }
            },
        },
        mounted() {
            this.getDataHandler()
        },
        methods: {
            getDataHandler: async function() {
                this.dataOverlay = true
                await api.call('get', this.api)
                    .then((response) => {
                        this.dataOverlay = false
                        this.isCardsRefreshing = false
                        this.itemsLocal = response.data
                    })
            },
            refreshDataInCards() {
                this.isCardsRefreshing = true
                this.getDataHandler()
            },
            showItem(index) {
                this.$emit('show', index)
            },
            createOrEditItemModal(index) {
                this.$emit('edit', index)
            },
            setCategoriesOfOrder(value) {
                this.set_categories_ids = value
            },
            orderMaster(index) {
            }
        }
    }
</script>
<style scoped>
    .contract-cards {
        min-height: 300px;
    }
    a.big-link {
        font-size: 16px;
        font-weight: bold;
        color: #362518;
    }
</style>