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
                <b-card v-if="contract.status != 'Договор расторгнут'">
                    <b-card-text>
                        <div class="row">
                            <div class="col-sm-4">
                                <h4 class="card-title mb-0">Договор №{{contract.contract_number}}</h4>
                                <b-badge variant="success" class="always-small-badge" v-if="contract.status == 'Есть бумажный договор'"><i class="fas fa-check"></i> {{contract.status}}</b-badge>
                                <b-badge variant="warning" class="always-small-badge" v-if="contract.status == 'Нет бумажного договора'">{{contract.status}}</b-badge>
                                <b-badge variant="secondary" class="always-small-badge" v-if="contract.status == 'В обработке'">{{contract.status}}</b-badge>
                                <p><em class="text-muted">Адрес: {{ contract.contract_address}}</em></p>
                                <p><b>Дата последнего ТО-ВКГО:</b> {{ findLastTO(index) | formattedDate}}</p>
                                <p>
                                    <tamplate v-if="checkDaysForNextTO(index) != -1">
                                        <b>До следующего ТО-ВКГО:</b> {{ checkDaysForNextTO(index) }} {{ getNumEnding(checkDaysForNextTO(index)) }}
                                    </tamplate>
                                    <template v-else>
                                        <b>Дата следующего ТО не назначена</b>
                                    </template>
                                </p>
                                <p>
                                    <tamplate v-if="findMasterOnNextTO(index) != -1">
                                        <b>Мастер на следующее ТО:</b> {{ findMasterOnNextTO(index) }}
                                    </tamplate>
                                    <template v-else>
                                        <b>Мастер на следующее ТО не назначен</b>
                                    </template>
                                </p>
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
            },
            findLastTO(index) {
                if (this.itemsLocal.data[index].contract_to.length > 0) {
                    if (this.itemsLocal.data[index].contract_to[1]) {
                        return this.itemsLocal.data[index].contract_to[1].to_start_datetime
                    }
                }

                return 'Не задано'
            },
            checkDaysForNextTO(index) {
                if (this.itemsLocal.data[index].contract_to.length > 0) {
                    var now = this.$moment(new Date())
                    var end = this.$moment(this.itemsLocal.data[index].contract_to[0].to_start_datetime)
                    var duration = this.$moment.duration(end.diff(now))
                    return Math.round(duration.asDays())
                }

                return -1 
            },
            findMasterOnNextTO(index) {
                if (this.itemsLocal.data[index].contract_to.length > 0) {
                    let days = this.checkDaysForNextTO(index)
                    if (days > 0 && this.itemsLocal.data[index].contract_to[0].master) {
                        return this.itemsLocal.data[index].contract_to[0].master.name
                    }
                }

                return -1
            },
            /**
             * Функция возвращает окончание для множественного числа слова на основании числа и массива окончаний
             * @param  iNumber Integer Число на основе которого нужно сформировать окончание
             * @param  aEndings Array Массив слов или окончаний для чисел (1, 4, 5),
             *         например ['яблоко', 'яблока', 'яблок']
             * @return String
             */
            getNumEnding(iNumber, aEndings = ['день', 'дня', 'дней'])
            {
                var sEnding, i
                iNumber = iNumber % 100
                if (iNumber>=11 && iNumber<=19) {
                    sEnding=aEndings[2]
                }
                else {
                    i = iNumber % 10
                    switch (i)
                    {
                        case (1): sEnding = aEndings[0]; break
                        case (2):
                        case (3):
                        case (4): sEnding = aEndings[1]; break
                        default: sEnding = aEndings[2]
                    }
                }
                return sEnding
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