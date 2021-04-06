<template>
    <div>
        <template v-if="!$route.params.service_id">
            <b-button-group size="sm" class="button-group-fill">
                <b-dropdown
                size="sm"
                variant="group-fill btn-secondary"
                v-if="!$route.params.service_id && checkActionAllow('show') && row.item.reference_order_type_id == 2"
                :id="'dropdown_' + row.item.id"
                :offset="!stacked ? '' : -105"
                dropleft
                :boundary="!stacked ? '.table-responsive' : 'scrollParent'"
                :no-flip="!stacked ? true : false"
                @show="dropDownVisibilityControl('dropdown_' + row.item.id)"
                @hidden="dropDownVisibilityControl('dropdown_' + row.item.id, false)"
                >
                    <template #button-content>
                        <span><i class="button-group-fill-options fas fa-cogs"></i></span>
                    </template>
                    <b-dropdown-item-button
                        v-if="!$route.params.service_id && checkActionAllow('show')"
                        @click="showItem(row.index)"
                    >
                        Карточка поставки
                    </b-dropdown-item-button>
                    <b-dropdown-divider></b-dropdown-divider>
                    <template v-if="checkActionAllow('make-bill')">
                        <b-dropdown-group header="Финансы">
                            <b-dropdown-item-button>
                                <span
                                    @click="createOrderBillFromAction(row.index)"
                                >
                                    <b-icon icon="blank" aria-hidden="true"></b-icon>
                                    Счета
                                </span>
                            </b-dropdown-item-button>
                            <b-dropdown-item-button>
                                <span
                                    @click="createOrderFinanceRequestFromAction(row.index)"
                                >
                                    <b-icon icon="blank" aria-hidden="true"></b-icon>
                                    Запросы
                                </span>
                            </b-dropdown-item-button>
                        </b-dropdown-group>
                        <b-dropdown-divider></b-dropdown-divider>
                    </template>
                    <b-dropdown-item-button
                        @click="openOrderDocumentsFromAction(row.index)"
                    >
                        Документы
                    </b-dropdown-item-button>
                    <b-dropdown-divider></b-dropdown-divider>
                    <b-dropdown-item-button
                        v-if="checkActionAllow('distribute-services')
                                && row.item.reference_order_type_id == 2
                                && row.item.reference_status_id != 361"
                        @click="openServiceDistributionModal(row.index)"
                    >
                        Перераспределить
                    </b-dropdown-item-button>
                    <b-dropdown-divider></b-dropdown-divider>
                    <b-dropdown-item-button
                        v-if="checkActionAllow('edit') && row.item.reference_status_id != 361"
                        @click="createOrEditItemModal(row.index)"
                    >
                        Редактировать
                    </b-dropdown-item-button>
                    <b-dropdown-item-button
                        @click="copyItem(row.index)"
                    >
                        Скопировать
                    </b-dropdown-item-button>
                    <template v-if="checkActionAllow('delete')">
                        <b-dropdown-divider></b-dropdown-divider>
                        <b-dropdown-item-button
                            variant="danger"
                            @click="deleteItem(row.index)"
                        >
                            <b-icon icon="trash-fill" aria-hidden="true"></b-icon>
                            Удалить
                        </b-dropdown-item-button>
                    </template>
                </b-dropdown>
                <b-button 
                    class="button-group-fill-info"
                    v-if="!$route.params.service_id && checkActionAllow('show')" 
                    @click="showItem(row.index)"
                >
                    <i class="far fa-eye"></i>
                </b-button>
                <b-button 
                    class="button-group-fill-edit"
                    v-if="checkActionAllow('edit') && ((row.item.reference_order_type_id == 1 && row.item.reference_status_id != 362) || $route.name != 'orders')"
                    @click="createOrEditItemModal(row.index)"
                >
                    <i class="fas fa-pencil-alt"></i>
                </b-button>
                <b-button 
                    class="button-group-fill-delete"
                    v-if="checkActionAllow('delete') && (row.item.reference_order_type_id == 1 || row.item.sum_payment == 0)"
                    @click="deleteItem(row.index)"
                >
                    <i class="fas fa-trash text-danger"></i>
                </b-button>
            </b-button-group>
        </template>
        <div class="mt-1">
            <span
                v-if="checkActionAllow('make-order-supply')
                        && row.item.reference_order_type_id == 1
                        && row.item.reference_status_id != 362
                        && !row.item.short_order"
                class="table_actions_supply"
                @click="makeOrderSupply(row.index)"
                :disable="row.item.loading"
            >
                <span class="table_actions_supply-title">В&nbsp;поставку<i class="fas fa-spinner fa-spin ml-1" v-if="row.item.loading"></i></span>
            </span>
            <span
                v-if="checkActionAllow('distribute-services')
                        && row.item.reference_order_type_id == 2
                        && checkIfDistributed(row.item)
                        && row.item.reference_status_id != 361"
                class="table_distribute-services"
                @click="openServiceDistributionModal(row.index)"
            >
                <span class="table_distribute-services-title">Распределить</span>
            </span>
        </div>
        <template v-if="$route.params.service_id">
            <router-link :to="`/orders/${row.item.id}/services/${$route.params.service_id}`">
                <span class="table_actions_create_order">
                    <span class="table_actions_create_order-title">В&nbsp;обращение&nbsp;</span>
                </span>
            </router-link>
        </template>
    </div>
</template>
<script>
    import {
        checkActionAllow
    } from '../../../mixins'

    export default {
        props: {
            row: {type: Object, required: true},                                                // Row of table
            actionAllows: {type: Array, required: false, default: function () { return [] }},   // Array of rules for action allows
        },
        mixins: [
            checkActionAllow
        ],
        data() {
            return {
            }
        },
        computed: {
            stacked() {
                const stackedBreakpoints = ['sm'];
                return stackedBreakpoints.includes(this.$mq);
            },
            responsive() {
                const responsiveBreakpoints = ['md', 'lg', 'xl'];
                return responsiveBreakpoints.includes(this.$mq);
            },
        },
        methods: {
            showItem(index) {
                this.$emit('universalEmit', 'show', index)
            },
            createOrEditItemModal(index) {
                this.$emit('universalEmit', 'edit', index)
            },
            deleteItem(index) {
                this.$emit('universalEmit', 'delete', index)
            },
            copyItem(index) {
                this.$emit('universalEmit', 'copy', index)
            },
            openOrderDocumentsFromAction(index) {
                this.$emit('universalEmit', 'openOrderDocumentsFromAction', index)
            },
            makeOrderSupply(index) {
                this.$emit('universalEmit', 'makeOrderSupply', index)
            },
            openServiceDistributionModal(index) {
                this.$emit('universalEmit', 'openServiceDistributionModal', index)
            },
            checkIfDistributed(item) {
                if (item.service_users && item.selected_services) {
                    if (item.service_users.length == item.selected_services.length) {
                        return false
                    }
                }
                return true
            },
            dropDownVisibilityControl(id, show = true){
                if (!this.stacked) {
                    const el = document.getElementById(id)
                    const parent = el.closest('.table-responsive')
                    const allSticky = parent.querySelectorAll('.b-table-sticky-column')

                    if (show) {
                        parent.style.minHeight = '400px'
                        parent.style.overflowY = 'hidden'
                        allSticky.forEach(sticky => {
                            sticky.style.zIndex = '0'
                        })
                    } else {
                        parent.style.minHeight = 'auto'
                        parent.style.overflowY = 'auto'
                        allSticky.forEach(sticky => {
                            sticky.style.zIndex = '2'
                        })
                    }
                    el.closest('.b-table-sticky-column').style.zIndex = '2'
                }
            },
        }
    }
</script>

