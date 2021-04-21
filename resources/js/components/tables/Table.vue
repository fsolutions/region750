<template>
    <div>
        <div :class="$mq == 'sm' ? 'row justify-content-between mb-2' : 'row justify-content-between text-nowrap'">
            <template v-if="$mq != 'sm' && canSeeFilters()">
                <div class="col-md-6 col-6" v-if="typeOfTableFilter">
                    <contract-table-filter
                        v-if="typeOfTableFilter == 'contracts'"
                        :api="api"
                        @setFilteringApiParameter="setFilteringApiParameter"
                    ></contract-table-filter>
                    <contract-to-table-filter
                        v-if="typeOfTableFilter == 'contractsTO'"
                        :api="api"
                        @setFilteringApiParameter="setFilteringApiParameter"
                    ></contract-to-table-filter>
                    <order-table-filter
                        v-if="typeOfTableFilter == 'orders'"
                        :api="api"
                        @setFilteringApiParameter="setFilteringApiParameter"
                    ></order-table-filter>
                    <prescription-table-filter
                        v-if="typeOfTableFilter == 'prescriptions'"
                        :api="api"
                        @setFilteringApiParameter="setFilteringApiParameter"
                    ></prescription-table-filter>

                    <template v-if="$mq != 'sm'">
                        <template v-if="isNeedSearch">
                            <i class="fas fa-search float-left"></i>
                            <input class="form-control input_table_search" type="search" placeholder="Поиск" v-model="filter">
                        </template>
                    </template>
                </div>
            </template>
            <template v-if="$mq == 'sm' && canSeeFilters()">
                <div class="col">
                    <span class="button-filter-table" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample"><i class="fas fa-sort-down filter-table-icon"></i></span>
                </div>
            </template>
            <template v-if="!canSeeFilters()">
                <div class="col"></div>
            </template>
            <div :class="$mq == 'sm' ? 'col text-right' : (typeOfTableFilter!='' ? 'col-md-5 text-right':'col text-right')">
                <template>
                    <button
                        @click="refreshDataInTable()"
                        class="btn button-reload-table mr-3 mb-3"
                    >
                        <i class="fas fa-sync-alt reload-sync-icon" animation="spin" v-if="!isTableRefreshing"></i>
                        <i class="fas fa-spinner fa-spin reload-sync-icon" v-if="isTableRefreshing"></i>
                    </button>
                </template>
                <template v-if="$mq == 'sm'">
                    <template v-if="(checkActionAllow('create') && isNeedCreate) || isNeedCreate">
                        <span @click="createOrEditItemModal()" class="button-create"><i class="fas fa-plus"></i></span>
                    </template>
                </template>
                <template v-else>
                    <template v-if="(checkActionAllow('create') && isNeedCreate) || isNeedCreate">
                        <button
                            @click="createOrEditItemModal()"
                            type="button"
                            class="btn btn-outline-secondary btn-outline-pill float-right mb-3 mr-3"
                        >
                            {{customAddButtonName}}
                        </button>
                    </template>
                </template>
            </div>
        </div>
        <div class="row" v-if="$mq == 'sm'">
            <div class="collapse"  id="collapseExample">
                <template v-if="isNeedSearch">
                    <i class="fas fa-search float-left"></i>
                    <input class="form-control input_table_search mb-3" type="search" placeholder="Поиск" v-model="filter">
                </template>
                <div class="filter-mobile-block">
                    <contract-table-filter
                        v-if="typeOfTableFilter == 'contracts'"
                        :api="api"
                        @setFilteringApiParameter="setFilteringApiParameter"
                    ></contract-table-filter>
                </div>
            </div>
        </div>
        <b-table
            id="main-table"
            hover
            show-empty
            :tbody-tr-class="rowClass"
            :responsive="responsive"
            :stacked="stacked"
            :items="itemsLocal.data"
            :busy.sync="isTableBusy"
            :per-page="itemsLocal.per_page"
            :fields="headerContextMenuName!='' ? visibleHeaders : itemsLocal.headers"
            :api-url="api"
            @sort-changed="sortingChanged"
        >
            <template v-slot:head()="scope">
                <div class="text-nowrap" @contextmenu="openTableHeaderMenu($event)">
                    {{ scope.label }}
                </div>
            </template>
            <template #cell(operation_generation)="data">
                <span v-html="data.value"></span>
            </template>
            <template #cell(sum)="data">
                {{ data.value | toCurrency }}
            </template>
            <template #cell(sum_payment)="data">
                {{ data.value | toCurrency }}
            </template>
            <template #cell(debt)="data">
                {{ data.value | toCurrency }}
            </template>
            <template #cell(receive_datetime)="data">
                {{ data.value | formattedDateTime }}
            </template>
            <template #cell(order_start_datetime)="data">
                {{ data.value | formattedDate }}
            </template>
            <template #cell(prescription_start_datetime)="data">
                {{ data.value | formattedDate }}
            </template>
            <template #cell(to_start_datetime)="data">
                {{ data.value | formattedDateTime }}
            </template>
            <template #cell(contract_start_datetime)="data">
                {{ data.value | formattedDate }}
            </template>
            <template #cell(contract_to_last)="data">
                <template v-if="data.value[0]">
                    <span :class="checkDaysForNextTO(data.value) < 0 ? 'text-danger':'text-success'">{{ data.value[0].to_start_datetime | formattedDateTime }}</span>
                </template>
                <template v-else>
                    {{ "—" }}
                </template>
            </template>
            <template #cell(phone)="data">
                {{ data.value | VMask('+#(###)###-##-##') }}
            </template>
            <template #cell(lead_phone)="data">
                {{ data.value | VMask('+#(###)###-##-##') }}
            </template>
            <template v-slot:cell(actions)="row">
                <div class="ml-auto mr-auto text-center table-action-cell">
                    <component
                        :is="`action-${name}`"
                        :row.sync="row"
                        :actionAllows="items.actionAllows"
                        @universalEmit="universalEmit"
                    ></component>
                </div>
            </template>
            <template #cell()="data">
                <template v-if="data.field.loading">
                    <template v-if="!data.value"><i class="fas fa-spinner fa-spin ml-1"></i></template>
                    <template v-else>{{ data.value }}</template>
                </template>
                <template v-else>
                    {{ data.value || "—" }}
                </template>
            </template>
            <template v-slot:table-busy>
                <div class="text-center text-success my-5">
                    <b-spinner class="align-middle"></b-spinner>
                </div>
            </template>
            <template #empty="scope">
                <p class="my-4"><em>{{ onDataEmptyMessage }}</em></p>
            </template>
            <template #emptyfiltered="scope">
                <p class="my-4"><em>По заданному фильтру ничего не найдено</em></p>
            </template>
        </b-table>
        <div class="row justify-content-center">
            <div class="col-md-10 table_pagination mb-4">
                <b-pagination
                    v-show="itemsLocal.total > 5"
                    v-model="itemsLocal.current_page"
                    :total-rows="itemsLocal.total"
                    :per-page="itemsLocal.per_page"
                    :current-page="itemsLocal.current_page"
                    aria-controls="main-table"
                    size="md"
                    class="my-0"
                    pills
                ></b-pagination>
            </div>
            <div class="col-md-2 float-right mb-4">
                <b-form-group
                    v-show="itemsLocal.total > 5"
                    label-cols-lg="5"
                    class="mb-0"
                >
                    <b-form-select
                        v-model="itemsLocal.per_page"
                        id="perPageSelect"
                        size="sm"
                        :options="pageOptions"
                    ></b-form-select>
                </b-form-group>
            </div>
        </div>
        <!-- <context-menu-headers
            v-if="headerContextMenuName != '' && itemsLocal.headers.length > 0"
            :headerContextMenuName="headerContextMenuName"
            :menuName="'table-context-menu'"
            :menuItems="itemsLocal.headers"
            :event="openedTableHeaderMenuEvent"
            :restoreHeader="restoreHeader"
            @check="setNewHeaders"
        ></context-menu-headers> -->
    </div>
</template>

<script>
    import debounce from '../../services/helpers'

    import {
        checkActionAllow
    } from '../../mixins'

    import ContractTableFilter from '../contracts/ContractTableFilter'
    import ContractTOTableFilter from '../contracts/ContractTOTableFilter'
    import OrderTableFilter from '../orders/OrderTableFilter'
    import PrescriptionTableFilter from '../prescriptions/PrescriptionTableFilter'
    import ActionContract from "./actions/actionContract";
    import ActionOrder from "./actions/actionOrder";
    import ActionCustom from "./actions/actionCustom";

    export default {
        props: {
            name: { type: String, required: false, default: 'custom' },             // Naming of table for specify moments
            api: { type: String, required: true },                                  // Link on API for table working
            items: { type: Object, required: true },                                // Main items Object
            isNeedSearch: { type: Boolean, required: true },                        // If table needs search line
            isNeedCreate: { type: Boolean, required: false, default: true },        // If table needs create new element button
            typeOfTableFilter: { type: String, required: false, default: '' },      // Type of filter for output: orders, services, finance
            additionalGetParameter: { type: String, required: false, default: '' }, // If we want to add something, for example, &order_id=10
            customAddButtonName: { type: String, required: false, default: 'Создать' },      // If we need to rename add button
            onDataEmptyMessage: {
                type: String,
                required: false,
                default: 'Таблица временно пуста. Попробуйте добавить новую запись.'
            },                                                                       // Custom Message on empty data in table
            headerContextMenuName: { type: String, required: false, default: '' },   // If table header needs context menu column filter - type filter name
        },
        components: {
            "contract-table-filter": ContractTableFilter,
            "contract-to-table-filter": ContractTOTableFilter,
            "order-table-filter": OrderTableFilter,
            "prescription-table-filter": PrescriptionTableFilter,
            "action-contract": ActionContract,
            "action-order": ActionOrder,
            "action-custom": ActionCustom,
        },
        mixins: [
            checkActionAllow
        ],
        data() {
            return {
                pageOptions: [5, 10, 25, 50, 100],
                isTableBusy: true,
                isTableRefreshing: true,
                filter: "",                                      // Search line value
                additionalParameter: "",
                sortParamsLine: "",
                openedTableHeaderMenuEvent: null,
                visibleHeaders: [],
                restoreHeader: true,
                user: auth.user,
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
            stacked() {
                const stackedBreakpoints = ['sm'];
                return stackedBreakpoints.includes(this.$mq);
            },
            responsive() {
                const responsiveBreakpoints = ['md', 'lg', 'xl'];
                return responsiveBreakpoints.includes(this.$mq);
            },
            tableHeaderSettings () {
                return this.$store.getters.TABLE_HEADER_SETTINGS
            }
        },
        watch: {
            'items.current_page': function (){
                this.getDataHandler()
            },
            'items.per_page': function (){
                this.getDataHandler()
            },
            'itemsLocal.headers': {
                handler(headers) {
                    this.visibleHeaders = headers.filter(field => field.visible)
                },
                immediate: true,
                deep: true,
            },
            additionalParameter() {
                this.getDataHandler()
            },
            filter: debounce(function (value) {
                this.getDataHandler()
            }, 500),
        },
        mounted() {
            if (!this.additionalParameter) {
                this.getDataHandler()
            }
        },
        methods: {
            getDataHandler: async function() {
                this.isTableBusy = true
                this.restoreHeader = true
                // this.sortParamsLine = ''
                let filter = '';
                if (this.filter) {
                    filter = "&q=" + this.filter
                }
                await api.call('get', this.api + `?page=${this.itemsLocal.current_page}&per_page=${this.itemsLocal.per_page}${this.additionalParameter}${this.additionalGetParameter}${this.sortParamsLine}${filter}`)
                    .then((response) => {
                        this.isTableBusy = false
                        this.isTableRefreshing = false
                        this.itemsLocal = response.data
                        this.$emit('onLoad')
                    })
            },
            sortingChanged(ctx) {
                let elasticSortBy = ''
                this.itemsLocal.headers.forEach((header, index) => {
                    if (header.key == ctx.sortBy) {
                        elasticSortBy = header.sortBy
                    }
                })
                let sortDirection = 'asc'
                if (ctx.sortDesc) {
                    sortDirection = 'desc'
                }
                // this.sortParamsLine = `&sortBy=${ctx.sortBy}&sortDirection=${sortDirection}`
                this.sortParamsLine = `&elasticSortBy=${elasticSortBy}&sortBy=${ctx.sortBy}&sortDirection=${sortDirection}`
                this.getDataHandler()
            },
            createOrEditItemModal(index) {
                this.$emit('edit', index)
            },
            universalEmit(whatEmit, ...params) {
                this.$emit(whatEmit, ...params)
            },
            setFilteringApiParameter(additionalParameter) {
                this.additionalParameter = additionalParameter
            },
            companyNameTdOutput(item, value) {
                if (item.short_order && item.short_order == 1) {
                    return item.temp_name
                }
                return value
            },
            rowClass(item, type) {
                if (!item || type !== 'row') return
                if (item.debt == 0 && item.sum != 0) {
                    return 'table-success'
                }
                if (item.reference_status_id == 313) {
                    return 'table-success'
                }
                if (item.reference_status_id == 314
                    || item.reference_status_id == 362) {
                    return 'table-danger'
                }
                if (item.reference_status_id == 312) {
                    return 'table-warning'
                }
                if (item.reference_status_id == 361) {
                    return 'table-success'
                }
            },
            refreshDataInTable() {
                this.isTableRefreshing = true
                this.getDataHandler()
            },
            openTableHeaderMenu(e) {
                this.openedTableHeaderMenuEvent = e
            },
            setNewHeaders(headers) {
                this.itemsLocal.headers = headers
                this.restoreHeader = false
            },
            checkDaysForNextTO(item) {
                if (item.length > 0) {
                    var now = this.$moment(new Date())
                    var end = this.$moment(item[0].to_start_datetime)
                    var duration = this.$moment.duration(end.diff(now))
                    return Math.round(duration.asDays())
                }

                return -1 
            },
            canSeeFilters() {
                if (!this.user.role.includes("client")) {
                    return true
                }
                return false
            },            
        }
    }
</script>
<style>
    .btn-100px {
        min-width: 100px;
        text-transform: none;
    }
    .font-size-120 {
        font-size: 125%;
    }
</style>
