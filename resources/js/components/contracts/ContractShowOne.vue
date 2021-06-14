<template>
    <div class="d-block">
        <div class="row">
            <div class="col-md-2">
                <ul id="oneOrderTabs" role="tablist" class="parent_item_block d-none d-sm-block nav fa-ul contract-show-one-menu">
                    <li class="parent_item pb-0">
                        <a class="nav-link active" data-toggle="tab" role="tab" aria-controls="contract-details" aria-selected="true" href="#contract-details"><span class="fa-li"><i class="fas fa-chevron-right"></i></span> Карточка договора</a>
                    </li>
                    <hr>
                    <li class="parent_item pb-0">
                        <a class="nav-link" data-toggle="tab" role="tab" aria-controls="contract-to" aria-selected="true" href="#contract-to"><span class="fa-li"><i class="fas fa-chevron-right"></i></span> ТО-ВКГО</a>
                    </li>
                    <hr>
                    <li class="parent_item pb-0">
                        <a class="nav-link" data-toggle="tab" role="tab" aria-controls="contract-orders" aria-selected="true" href="#contract-orders"><span class="fa-li"><i class="fas fa-chevron-right"></i></span> Обращения</a>
                    </li>
                    <hr>
                    <li class="parent_item pb-0">
                        <a class="nav-link" data-toggle="tab" role="tab" aria-controls="contract-prescriptions" aria-selected="true" href="#contract-prescriptions"><span class="fa-li"><i class="fas fa-chevron-right"></i></span> Предписания</a>
                    </li>
                    <hr>
                    <li class="parent_item pb-0">
                        <a class="nav-link" data-toggle="tab" role="tab" aria-controls="contract-equipment" aria-selected="true" href="#contract-equipment"><span class="fa-li"><i class="fas fa-chevron-right"></i></span> Оборудование</a>
                    </li>
                    <hr>
                    <li class="parent_item pb-0">
                        <a class="nav-link" data-toggle="tab" role="tab" aria-controls="contract-history" aria-selected="true" href="#contract-history"><span class="fa-li"><i class="fas fa-chevron-right"></i></span> История</a>
                    </li>
                    <hr>
                </ul>
            </div>
            <div class="col-md-10">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="contract-details">
                        <contract-details
                            :detailedItemIndex="detailedItemIndex"
                            :detailedItem="detailedItem"
                        ></contract-details>
                    </div>
                    <div class="tab-pane fade" id="contract-to">
                        <h5 class="mb-4">ТО-ВКГО по договору №{{detailedItem.contract_number}} (ID: {{detailedItem.id}})</h5>
                        <contract-to-table
                            :contract_id="detailedItem.id"
                            :additionalGetParameter="`&contract_id=${detailedItem.id}`"
                            :contractForTO="detailedItem"
                            :contractForTOIndex="detailedItemIndex"
                            :isNeedCreate="detailedItem.status != 'Договор расторгнут' ? true:false"
                            @updateParentData="updateParentData"
                            :typeOfTableFilter="'contractsTO'"
                        ></contract-to-table>
                    </div>
                    <div class="tab-pane fade" id="contract-orders">
                        <h5 class="mb-4">Обращения по договору №{{detailedItem.contract_number}} (ID: {{detailedItem.id}})</h5>
                        <orders-table
                            :contract_id="detailedItem.id"
                            :additionalGetParameter="`&contract_id=${detailedItem.id}`"
                            :contractForTO="detailedItem"
                            :contractForTOIndex="detailedItemIndex"
                            :isNeedCreate="detailedItem.status != 'Договор расторгнут' ? true:false"
                            :typeOfTableFilter="'orders'"
                        ></orders-table>
                    </div>
                    <div class="tab-pane fade" id="contract-prescriptions">
                        <h5 class="mb-4">Предписания по договору №{{detailedItem.contract_number}} (ID: {{detailedItem.id}})</h5>
                        <prescriptions-table
                            :contract_id="detailedItem.id"
                            :additionalGetParameter="`&contract_id=${detailedItem.id}`"
                            :contractForTO="detailedItem"
                            :contractForTOIndex="detailedItemIndex"
                            :isNeedCreate="detailedItem.status != 'Договор расторгнут' ? true:false"
                            :typeOfTableFilter="'prescriptions'"
                        ></prescriptions-table>
                    </div>
                    <div class="tab-pane fade" id="contract-equipment">
                        <h5 class="mb-4">Оборудование по договору №{{detailedItem.contract_number}} (ID: {{detailedItem.id}})</h5>
                        <equipment-table
                            :contract_id="detailedItem.id"
                            :additionalGetParameter="`&equip_contract_id=${detailedItem.id}`"
                            :isNeedCreate="false"
                        ></equipment-table>
                    </div>
                    <div class="tab-pane fade" id="contract-history">
                        <h5 class="mb-4">История по договору №{{detailedItem.contract_number}} (ID: {{detailedItem.id}})</h5>
                        <app-table
                            :api="apiHistory"
                            :additionalGetParameter="`&contract_id=${detailedItem.id}`"
                            :items.sync="HistoryItems"
                            :isNeedSearch="false"
                            :isNeedCreate="false"
                            :onDataEmptyMessage="'История по контракту отсутствует.'"
                        ></app-table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { API_HISTORY } from "../../constants"

    import ContractDetails from './ContractDetails'
    import ContractTOTable from './ContractTOTable'
    import OrderTable from '../orders/OrderTable'
    import PrescriptionTable from '../prescriptions/PrescriptionTable'
    import EquipmentTable from '../equipment/EquipmentTable'
    
    // import ChangeHistory from '../logs/ChangeHistory'

    export default {
        components: {
            "contract-details": ContractDetails,
            "contract-to-table": ContractTOTable,
            "orders-table": OrderTable,
            "prescriptions-table": PrescriptionTable,
            "equipment-table": EquipmentTable
            // 'change-history': ChangeHistory,
        },
        props: {
            detailedItemIndex: { type: Number, required: false, default: -1 },
            detailedItem: { type: Object, required: true },
        },
        data() {
            return {
                changeLoadOrder: false,
                onRefreshFinanceRequestsKey: 0, // key of finance requests refreshing
                apiHistory: API_HISTORY,
                HistoryItems: {
                    actionAllows: [],
                    headers: [],
                    data: [],
                    links: []
                }
            }
        },
        watch: {
        },
        created() {
        },
        mounted() {
            // $(document).ready(function() {
            //     $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            //         e.target // newly activated tab
            //         e.relatedTarget // previous active tab

            //         $('.contract-show-one-menu a[data-toggle="tab"]').removeClass('active');
            //         if (e.relatedTarget) {
            //             $(e.relatedTarget).removeClass('active');
            //         }

            //         $(e.target).addClass('active');
            //     })
            // })
        },
        methods: {
            onCloseSidePanelLoadingDocument(value) {
                if(value == true){
                    this.checkedLoadDocument = false
                }
            },
            onRefreshFinanceRequests() {
                this.onRefreshFinanceRequestsKey += 1
            },
            closeOrder(index) {
                this.$emit('closeOrder', index)
            },
            updateParentData() {
                this.$emit('updateParentData', this.detailedItemIndex)                
            }
        }
    }
</script>
