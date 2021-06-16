<template>
    <div>
        <b-row>
            <b-col>
                <h5>
                    <span class="mr-2">Карточка ТО-ВГКО №{{detailedItem.id}}</span>
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
                          Статус ТО: 
                            <b-badge variant="success" class="small-badge" v-if="detailedItem.to_status == 'Проведено'"><i class="fas fa-check"></i> {{detailedItem.to_status}}</b-badge>
                            <b-badge variant="warning" class="small-badge" v-if="detailedItem.to_status == 'Запланировано'">{{detailedItem.to_status}}</b-badge>
                            <b-badge variant="danger" class="small-badge" v-if="detailedItem.to_status == 'Отменено'">{{detailedItem.to_status}}</b-badge>
                            <b-badge variant="secondary" class="small-badge" v-if="detailedItem.to_status == 'Перенесено'">{{detailedItem.to_status}}</b-badge>
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
                            stacked-heading="Запланированная дата проведения ТО"
                        >
                            <b>{{ detailedItem.to_start_datetime | formattedDateTime }}</b>
                        </b-td>
                        <b-td
                            stacked-heading="SMS напоминание направлено"
                        >
                            <template v-if="detailedItem.to_sms_sended">
                                {{ detailedItem.to_sms_sended | formattedDateTime }}
                            </template>
                            <template v-else>
                                Не отправлялось
                            </template>
                        </b-td>
                        <b-td
                            stacked-heading="Мастер, назначенный на ТО"
                        >
                            <template v-if="detailedItem.master">
                              {{ detailedItem.master.name }}
                            </template>
                            <template v-else>
                              Не известно
                            </template>
                        </b-td>
                        <b-td
                            stacked-heading="Клиент на ТО"
                        >
                            <template v-if="detailedItem.to_contract_for_user">
                                <b>ФИО:</b> {{detailedItem.to_contract_for_user.name}}<br>
                                <b>Телефон:</b> <a :href="`tel:+${detailedItem.to_contract_for_user.phone}`">{{detailedItem.to_contract_for_user.phone | VMask('+#(###)###-##-##')}}</a><br>
                                <span v-if="detailedItem.to_contract_for_user.email"><b>Email:</b> {{detailedItem.to_contract_for_user.email}}</span>
                            </template>
                            <template v-else>
                                Не задан
                            </template>
                        </b-td>
                        <b-td
                            v-if="detailedItem.to_no_access_times != 0"
                            style="background: #ffeaea;"
                            stacked-heading="Клиент не обеспечил доступ"
                        >
                            <template v-if="detailedItem.to_no_access_times == 1">
                                {{ detailedItem.to_no_access_times }} раз
                            </template>
                            <template v-if="detailedItem.to_no_access_times == 2">
                                {{ detailedItem.to_no_access_times }} раза
                            </template>
                        </b-td>
                        <template v-if="detailedItem.to_contract">
                          <b-td
                              stacked-heading="Номер договора"
                          >
                              <b>{{ detailedItem.to_contract.contract_number }}</b>
                          </b-td>
                          <b-td
                              stacked-heading="Адрес из договора"
                          >
                              {{ detailedItem.to_contract.contractRealaddress }}
                          </b-td>
                          <b-td
                              stacked-heading="Дата заключения договора"
                          >
                              {{ detailedItem.to_contract.contract_start_datetime | formattedDate }}
                          </b-td>
                        </template>
                        <b-td
                            stacked-heading="Комментарий по ТО"
                            v-if="detailedItem.to_comment"
                            style="white-space: pre-line;"
                        >{{ detailedItem.to_comment || "Пока ничего не комментировали" }}
                        </b-td>
                        <b-td
                            stacked-heading="Дата добавления ТО в систему"
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
        }
    }
</script>
<style scoped>
    .closedBg {
        background-color: #00389717;
    }
</style>
