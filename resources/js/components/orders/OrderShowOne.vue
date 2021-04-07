<template>
    <div class="d-block">
        <div class="row">
            <div class="col-md-2">
                <ul id="oneOrderTabs" role="tablist" class="parent_item_block d-none d-sm-block nav fa-ul order-show-one-menu">
                    <li class="parent_item pb-0">
                        <a class="nav-link active" data-toggle="tab" role="tab" aria-controls="order-details" aria-selected="true" href="#order-details"><span class="fa-li"><i class="fas fa-chevron-right"></i></span> Карточка</a>
                    </li>
                    <hr>
                    <!-- <li class="parent_item nav-item" v-if="!detailedItem.short_order">
                        <a
                            class="nav-link"
                            data-toggle="tab"
                            role="tab"
                            aria-controls="order-documents"
                            aria-selected="false"
                            href="#order-documents"
                            @click="requestDocuments = !requestDocuments"
                        >
                            <span class="fa-li"><i class="fas fa-chevron-right"></i></span>
                            Документы
                        </a>
                    </li> -->
                    <li class="parent_item nav-item">
                        <a
                            class="nav-link"
                            data-toggle="tab"
                            href="#order-change-history"
                            role="tab"
                            aria-controls="order-change-history"
                            aria-selected="false"
                            @click="changeLoadOrder = !changeLoadOrder"
                        >
                            <span class="fa-li"><i class="fas fa-chevron-right"></i></span>
                            История
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-10">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="order-details">
                        <order-details
                            :detailedItemIndex="detailedItemIndex"
                            :detailedItem="detailedItem"
                            @closeOrder="closeOrder"
                            @closeOrderFinally="closeOrderFinally"
                        ></order-details>
                    </div>
                    <div class="tab-pane fade" id="order-documents">
                        <h5>Документы по обращению #{{detailedItem.id}}</h5>
                        <show-edit-documents
                            :orderId="detailedItem.id"
                            :companyId="detailedItem.company_id"
                            :requestDocuments.sync="requestDocuments"
                        />
                        <create-documents
                            :orderId="detailedItem.id"
                            :companyId="detailedItem.company_id"
                            @documentLoaded="onCloseSidePanelLoadingDocument"
                        />
                    </div>
                    <div class="tab-pane fade" id="order-change-history">
                        <h5>История изменений по обращению #{{detailedItem.id}}</h5>
                        <change-history
                            :changeHistoryId.sync="detailedItem.id"
                            :changeLoadOrder.sync="changeLoadOrder"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import OrderDetails from './OrderDetails'
    import ChangeHistory from '../logs/ChangeHistory'

    export default {
        components: {
            "order-details": OrderDetails,
            'change-history': ChangeHistory,
        },
        props: {
            detailedItemIndex: { type: Number, required: false, default: -1 },
            detailedItem: { type: Object, required: true },
        },
        data() {
            return {
                requestDocuments: false,    //sign for document output by order
                changeLoadOrder: false,
                onRefreshFinanceRequestsKey: 0, // key of finance requests refreshing
                documentIdsFromCompany: [],
            }
        },
        watch: {
        },
        created() {
        },
        mounted() {
            $(document).ready(function() {
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    e.target // newly activated tab
                    e.relatedTarget // previous active tab

                    $('.order-show-one-menu a[data-toggle="tab"]').removeClass('active');
                    if (e.relatedTarget) {
                        $(e.relatedTarget).removeClass('active');
                    }

                    $(e.target).addClass('active');
                })
            })

            this.setDocumentIdsFromCompany()
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
            closeOrderFinally(index) {
                this.$emit('closeOrderFinally', index)
            },
        }
    }
</script>
