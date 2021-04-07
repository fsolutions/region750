<template>
    <div>
        <b-row>
            <b-col>
                <h5>
                    <span class="mr-2">Карточка договора №{{detailedItem.contract_number}} (ID: {{detailedItem.id}})</span>
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
                        <b-col cols="12" md="auto" class="text-right" style="font-size: 16px;">
                            <b-badge variant="success" class="small-badge" v-if="detailedItem.status == 'Есть бумажный договор'"><i class="fas fa-check"></i> {{detailedItem.status}}</b-badge>
                            <b-badge variant="warning" class="small-badge" v-if="detailedItem.status == 'Нет бумажного договора'">{{detailedItem.status}}</b-badge>
                            <b-badge variant="secondary" class="small-badge" v-if="detailedItem.status == 'В обработке'">{{detailedItem.status}}</b-badge>
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
                            stacked-heading="ID в системе"
                        >
                            {{ detailedItem.id }}
                        </b-td>
                        <b-td
                            stacked-heading="Номер договора"
                        >
                            {{ detailedItem.contract_number }}
                        </b-td>
                        <b-td
                            stacked-heading="Адрес из договора"
                        >
                            {{ detailedItem.contract_address }}
                        </b-td>
                        <b-td
                            stacked-heading="Дата заключения договора"
                        >
                            {{ detailedItem.contract_start_datetime | formattedDate }}
                        </b-td>
                        <b-td
                            stacked-heading="Пользователь договора"
                        >
                            <template v-if="detailedItem.contract_on_user">
                                <b>ФИО:</b> {{detailedItem.contract_on_user.name}}<br>
                                <b>Телефон:</b> <a :href="`tel:+${detailedItem.contract_on_user.phone}`">{{detailedItem.contract_on_user.phone | VMask('+#(###)###-##-##')}}</a><br>
                                <b>Email:</b> {{detailedItem.contract_on_user.email || "Не задан"}}
                            </template>
                            <template v-else>
                                Не задан
                            </template>
                        </b-td>
                        <b-td
                            stacked-heading="Комментарий к договору от менеджера"
                            v-if="detailedItem.contract_comment"
                            style="white-space: pre-line;"
                        >{{ detailedItem.contract_comment || "Пока ничего не комментировали" }}
                        </b-td>
                        <b-td
                            stacked-heading="Дата добавления договора в систему"
                        >
                            {{ detailedItem.created_at | formattedDateTime }}
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
        }
    }
</script>
<style scoped>
    .closedBg {
        background-color: #00389717;
    }
</style>
