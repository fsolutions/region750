<template>
    <div>
        <b-row>
            <b-col>
                <h5>
                    <span class="mr-2">Карточка обращения №{{detailedItem.id}}</span>
                </h5>
            </b-col>
        </b-row>
        <div class="position-relative">
            <b-table-simple small caption-top stacked class="service-properties-table">
                <caption>
                    <b-row class="align-items-center">
                        <b-col>
                            <span class="text-bold-size-14">Основная информация</span>
                        </b-col>
                        <b-col cols="12" md="auto" class="text-right" style="font-size: 16px;">
                          Статус обращения: 
                            <b-badge variant="success" class="small-badge" v-if="detailedItem.order_status == 'Исполнено'"><i class="fas fa-check"></i> {{detailedItem.order_status}}</b-badge>
                            <b-badge variant="warning" class="small-badge" v-if="detailedItem.order_status == 'Запланировано исполнение'">{{detailedItem.order_status}}</b-badge>
                            <b-badge variant="danger" class="small-badge" v-if="detailedItem.order_status == 'Отменено'">{{detailedItem.order_status}}</b-badge>
                            <b-badge variant="secondary" class="small-badge" v-if="detailedItem.order_status == 'В обработке'">{{detailedItem.order_status}}</b-badge>
                        </b-col>
                    </b-row>
                </caption>
                <b-tbody>
                    <b-tr>
                        <b-td
                            stacked-heading="ID в системе"
                        >
                            {{ detailedItem.id }}
                        </b-td>
                        <b-td
                            stacked-heading="Услуга обращения"
                        >
                            {{ detailedItem.order_service.name }}
                        </b-td>
                        <b-td
                            stacked-heading="Описание обращения"
                            style="white-space: pre-line;"
                        >{{ detailedItem.order_description }}
                        </b-td>
                        <template v-if="detailedItem.order_contract">
                          <b-td
                              stacked-heading="Номер договора"
                          >
                              {{ detailedItem.order_contract.contract_number }}
                          </b-td>
                          <b-td
                              stacked-heading="Адрес из договора"
                          >
                              {{ detailedItem.order_contract.contract_address }}
                          </b-td>
                          <b-td
                              stacked-heading="Дата заключения договора"
                          >
                              {{ detailedItem.order_contract.contract_start_datetime | formattedDate }}
                          </b-td>
                          <b-td
                              stacked-heading="Обращение по предписанию"
                          >
                            <template v-if="detailedItem.order_prescription">
                              {{ detailedItem.order_prescription.prescription_number }}
                            </template>
                            <template v-else>
                              Без предписания
                            </template>
                          </b-td>
                        </template>
                        <template v-if="!detailedItem.order_contract">
                          <b-td
                              stacked-heading="Номер договора"
                          >
                              Обращение без договора
                          </b-td>
                        </template>
                        <b-td
                            stacked-heading="Обращение от клиента"
                        >
                            <template v-if="detailedItem.order_user">
                                <b>ФИО:</b> {{detailedItem.order_user.name}}<br>
                                <b>Телефон:</b> <a :href="`tel:+${detailedItem.order_user.phone}`">{{detailedItem.order_user.phone | VMask('+#(###)###-##-##')}}</a><br>
                                <b>Email:</b> {{detailedItem.order_user.email || "Не задан"}}
                            </template>
                            <template v-else>
                                Не задан
                            </template>
                        </b-td>
                        <b-td
                            stacked-heading="Комментарий для клиента"
                            v-if="detailedItem.order_comment_for_user"
                            style="white-space: pre-line;"
                        >{{ detailedItem.order_comment_for_user || "Пока ничего не комментировали" }}
                        </b-td>
                        <b-td
                            stacked-heading="Комментарий для коллег"
                            v-if="detailedItem.order_comment"
                            style="white-space: pre-line;"
                        >{{ detailedItem.order_comment || "Пока ничего не комментировали" }}
                        </b-td>
                        <b-td
                            stacked-heading="Дата отработки"
                        >
                            {{ detailedItem.order_start_datetime | formattedDateTime }}
                        </b-td>
                        <b-td
                            stacked-heading="Обращение обработал"
                        >
                            <template v-if="detailedItem.master">
                              {{ detailedItem.master.name }}
                            </template>
                            <template v-else>
                              Не известно
                            </template>
                        </b-td>
                        <b-td
                            stacked-heading="Дата составления обращения"
                        >
                            {{ detailedItem.created_at }}
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
        }
    }
</script>
<style scoped>
    .closedBg {
        background-color: #00389717;
    }
</style>
