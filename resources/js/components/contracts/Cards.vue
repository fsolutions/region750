<template>
    <div>
        <div class='text-left text-nowrap'>
            <button
                @click="refreshDataInCards()"
                class="btn button-reload-table mr-3 mb-3"
            >
                <i class="fas fa-sync-alt reload-sync-icon" animation="spin" v-if="!isCardsRefreshing"></i>
                <i class="fas fa-spinner fa-spin reload-sync-icon" v-if="isCardsRefreshing"></i>
            </button>
            <template v-if="(checkActionAllow('create') && isNeedCreate) || isNeedCreate">
                <button
                    @click="createOrEditItemModal()"
                    type="button"
                    class="btn btn-outline-secondary btn-outline-pill mb-3 mr-3"
                >
                    Добавить договор
                </button>
            </template>
        </div>
        <div class="contract-cards">
            <div v-if="itemsLocal.data.length == 0 && !dataOverlay">
                <b-card-text class="text-center">
                    <h4 class="card-title my-5">Добавьте свой договор в систему, <br>или дождитесь, когда его внесет менеджер</h4>
                </b-card-text>
            </div>
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
                                <b-badge variant="danger" class="always-small-badge" v-if="contract.status == 'Передать в ГЖИ' || contract.status == 'Передано в ГЖИ'">{{contract.status}}</b-badge>
                                <p><em class="text-muted">Адрес: {{ contract.contract_address}}</em></p>
                                <p>
                                    <template v-if="checkDaysForNextTO(index) > -1">
                                        <!-- <b>До следующего ТО-ВКГО:</b> {{ checkDaysForNextTO(index) }} {{ getNumEnding(checkDaysForNextTO(index)) }} -->
                                        <b>Следующее ТО назначено на <span class="bigDate" v-html="nextTO(index)"></span></b>
                                    </template>
                                    <template v-else>
                                        <b>Дата следующего ТО <span class="bigDate">не назначена</span></b>
                                    </template>
                                </p>
                                <p class="mb-0"><b>Дата последнего ТО <span class="bigDate" style="font-size:16px;" v-html="findLastTO(index)"></span></b></p>
                                <template v-if="findMasterOfPreviousTO(index) != -1">
                                    <p>
                                        <b>Работы выполнял </b> {{ findMasterOfPreviousTO(index) }}
                                    </p>
                                </template>
                            </div>
                            <div class="col-sm-6 text-center">
                                <b-button @click="orderMaster(index)" variant="primary" class="mt-4">Вызвать мастера</b-button>
                                <p class="text-small mb-1 mt-2">или позвоните нам</p>
                                <p><a href="tel:+79153788117" class="big-link">+7 (915) 378-81-17</a></p>
                            </div>
                        </div>
                    </b-card-text>
                </b-card>
            </div>
        </div>
        <fast-order-form
            :openForm="fastOrderFormOpened"
            :contract_id="selectedContractId"
            @sended="sendedFastOrderForm"
        ></fast-order-form>
        <b-overlay
            :show="dataOverlay"
            spinner-variant="success"
            no-wrap
        ></b-overlay>
    </div>
</template>
<script>
    import { API_ORDERS } from "../../constants"
    import fastOrderForm from "../orders/fastOrderForm"

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
        components: {
            'fast-order-form': fastOrderForm
        },
        data() {
            return {
                dataOverlay: true,
                isCardsRefreshing: true,
                order_reference_service_id: '',
                order_description: '',
                fastOrderFormOpened: false,
                selectedContractId: ''
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
            setServiceOfOrder(value) {
                this.order_reference_service_id = value
            },
            orderMaster(index) {
                this.selectedContractId = this.itemsLocal.data[index].id
                this.fastOrderFormOpened = true
            },
            nextTO(index) {
                return this.$moment(this.itemsLocal.data[index].contract_to[0].to_start_datetime).format('MMMM YYYY') + ' г.'
            },
            findLastTO(index) {
                let result = 'не задано'
                if (this.itemsLocal.data[index].contract_to.length > 0) {
                    this.itemsLocal.data[index].contract_to.some((to_element, to_index) => {
                        if (to_element.to_status == 'Проведено') {
                            result = this.$moment(to_element.to_start_datetime).format('DD.MM.YYYY') + ' г.'   
                            return true 
                        }
                    })
                }

                return result
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
            findMasterOfPreviousTO(index) {
                let result = -1

                if (this.itemsLocal.data[index].contract_to.length > 0) {
                    this.itemsLocal.data[index].contract_to.some((to_element, to_index) => {
                        if (to_element.to_status == 'Проведено') {
                            if (to_element.master) {
                                result = to_element.master.name
                            } else {
                                result = -1
                            }
                            return true 
                        }
                    })
                }

                return result
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
            },
            sendedFastOrderForm() {
                this.selectedContractId = ''
                this.fastOrderFormOpened = false
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
    .bigDate {
        color: #2176bd;
        font-size: 18px;
    }
</style>