<template>
    <div>
        <b-row>
            <b-col>
                <h5>
                    <span class="mr-2">Карточка обращение #{{detailedItem.id}}</span>
                    <b-badge variant="success" class="always-small-badge" v-if="debtInRubles() <= 0 && detailedItem.sum != 0"><i class="fas fa-check"></i> Оплачено</b-badge>
                    <b-badge variant="secondary" class="always-small-badge" v-else>Ожидает оплаты</b-badge>
                </h5>
            </b-col>
            <template v-if="detailedItemIndex != -1">
                <b-col class="text-right">
                    <span
                              v-if="detailedItem.reference_order_type_id == 1 && detailedItem.reference_status_id != 362"
                              size="sm"
                              class="button-outline-red"
                              @click="closeOrder(detailedItemIndex)"
                    >
                        Закрыть заявку
                    </span>
                    <span
                              v-if="detailedItem.reference_order_type_id == 2 && detailedItem.reference_status_id != 361"
                              size="sm"
                              class="button-outline-green"
                              @click="closeOrderFinally(detailedItemIndex)"
                    >
                        Завершить поставку
                    </span>
                </b-col>
            </template>
        </b-row>
        <div class="position-relative">
            <b-table-simple small caption-top stacked class="service-properties-table">
                <caption>
                    <b-row class="align-items-center">
                        <b-col>
                            <span class="text-bold-size-14">Основная информация</span>
                        </b-col>
                        <b-col cols="12" md="auto" class="text-right">
                            <span class="text-bold-size-14">Sales Менеджер обращение: <span class="font-weight-light">{{ getSalesManagerName() }}</span> <br> Статус: <span class="font-weight-light">{{ getStatusName() }}</span></span> 
                        </b-col>
                    </b-row>
                </caption>
                <b-thead head-variant="dark">
                    <b-tr>
                        <b-th>ID:</b-th>
                    </b-tr>
                </b-thead>
                <b-tbody>
                    <b-tr>
                        <b-td
                            stacked-heading="ID обращение"
                        >
                            {{ detailedItem.id }}
                        </b-td>
                        <b-td
                            v-if="detailedItem.short_order"
                            stacked-heading="Условное наименование (метка, компания и т.д.)"
                        >
                            {{ detailedItem.temp_name || '' }}
                        </b-td>
                        <b-td
                            stacked-heading="Статус"
                        >
                            {{ getStatusName() }}
                        </b-td>
                        <b-td
                            class="closedBg"
                            stacked-heading="Причина отказа"
                            v-if="detailedItem.reference_close_reason_id"
                        >
                            {{ getCloseReason() }}
                        </b-td>
                        <b-td
                            class="closedBg"
                            stacked-heading="Комментарий к отказу"
                            v-if="detailedItem.close_comment"
                        >
                            {{ detailedItem.close_comment }}
                        </b-td>
                        <b-td
                            stacked-heading="Тип обращение"
                        >
                            {{ detailedItem.type ? detailedItem.type.name : "Не известно" }}
                        </b-td>
                        <b-td
                            stacked-heading="Комментарий к заявке"
                            v-if="detailedItem.comment"
                            style="white-space: pre-line;"
                        >
                            {{ detailedItem.comment }}
                        </b-td>
                        <b-td
                            stacked-heading="Источник клиента"
                        >
                            {{ (detailedItem.company && detailedItem.company.source) ? detailedItem.company.source.name : "Не указан" }}
                        </b-td>
                        <b-td
                            stacked-heading="Наименование компании"
                        >
                            {{ detailedItem.company ? detailedItem.company.name : "Не задана" }} /
                            {{ detailedItem.company ? detailedItem.company.inn : "ИНН пока не задан" }}
                        </b-td>
                        <b-td
                            stacked-heading="Клиент"
                        >
                            {{ detailedItem.company_employee ? detailedItem.company_employee.lead_fio : "ФИО не указан" }} /
                            {{ detailedItem.company_employee ? detailedItem.company_employee.lead_phone : "Телефон не указан" }} /
                            {{ detailedItem.company_employee ? detailedItem.company_employee.lead_email : "E-mail не указан" }}
                        </b-td>
                        <b-td
                            stacked-heading="Sales менеджер обращение"
                        >
                            {{ getSalesManagerName() }}
                        </b-td>
                        <b-td
                            stacked-heading="Sales менеджер компании"
                        >
                            {{ getCompanySalesManagerName() }}
                        </b-td>
                        <b-td
                            stacked-heading="Обращение создал"
                        >
                            {{ detailedItem.creator ? detailedItem.creator.name : "Не известно" }}
                        </b-td>
                        <b-td
                            stacked-heading="Категории обращение"
                        >
                            {{ detailedItem.selected_categories || "Не заданы" }}
                        </b-td>
                        <b-td
                            stacked-heading="Виды услуг"
                        >
                            <template v-if="detailedItem.selected_services.length > 0">
                                <div v-for="(item, index) in detailedItem.selected_services" :key="item.id">
                                    <router-link :to="`/orders/${detailedItem.id}/services/${item.id}`">{{ item.name }}</router-link><template v-if="index != detailedItem.selected_services.length - 1">, </template>
                                </div>
                            </template>
                            <template v-else>
                                Не заданы
                            </template>
                        </b-td>
                        <b-td
                            stacked-heading="Дата получения заявки"
                        >
                            {{ detailedItem.receive_datetime | formattedDateTime }}
                        </b-td>
                        <b-td
                            stacked-heading="Дата отработки заявки (КП направлено/консультация оказана)"
                        >
                            {{ detailedItem.processing_end_datetime | formattedDateTime }}
                        </b-td>
                    </b-tr>
                </b-tbody>
            </b-table-simple>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            detailedItemIndex: { type: Number, required: false, default: -1 },
            detailedItem: { type: Object, required: true },
        },
        data() {
            return {
            }
        },
        mounted() {
        },
        methods: {
            closeOrder(index) {
                this.$emit('closeOrder', index)
            },
            closeOrderFinally(index) {
                this.$emit('closeOrderFinally', index)
            },
            getSalesManagerName() {
                let name = "Не известен"
                if (this.detailedItem.company && this.detailedItem.company.sales_manager) {
                    name = this.detailedItem.company.sales_manager.name
                }
                // if we have sales manager on one order
                if (this.detailedItem.optional_sales_manager && this.detailedItem.optional_sales_manager.name) {
                    name = this.detailedItem.optional_sales_manager.name
                }
                return name
            },
            getCompanySalesManagerName() {
                let name = "Не известен"
                if (this.detailedItem.company && this.detailedItem.company.sales_manager) {
                    name = this.detailedItem.company.sales_manager.name
                }
                return name
            },
            getStatusName() {
                let name = "Не известен"
                if (this.detailedItem.status && this.detailedItem.status.name) {
                    name = this.detailedItem.status.name
                }
                return name
            },
            getCloseReason() {
                let name = ''
                if (this.detailedItem.close_reason && this.detailedItem.close_reason.name) {
                    name = this.detailedItem.close_reason.name.replace(/\s*\(.*\)/,'')
                }

                return name
            }
        }
    }
</script>
<style scoped>
    .closedBg {
        background-color: #00389717;
    }
</style>
