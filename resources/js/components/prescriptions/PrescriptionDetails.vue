<template>
    <div>
        <b-row>
            <b-col>
                <h5>
                    <span class="mr-2">Карточка предписания №{{detailedItem.prescription_number || "Не задан"}} (ID: {{detailedItem.id}})</span>
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
                          Статус предписания: 
                            <b-badge variant="success" class="small-badge" v-if="detailedItem.prescription_status == 'Проведено'"><i class="fas fa-check"></i> {{detailedItem.prescription_status}}</b-badge>
                            <b-badge variant="warning" class="small-badge" v-if="detailedItem.prescription_status == 'Запланировано'">{{detailedItem.prescription_status}}</b-badge>
                            <b-badge variant="danger" class="small-badge" v-if="detailedItem.prescription_status == 'Отменено'">{{detailedItem.prescription_status}}</b-badge>
                            <b-badge variant="secondary" class="small-badge" v-if="detailedItem.prescription_status == 'Перенесено'">{{detailedItem.prescription_status}}</b-badge>
                        </b-col>
                    </b-row>
                </caption>
                <b-tbody>
                    <b-tr>
                        <b-td
                            stacked-heading="Номер предписания"
                        >
                            {{detailedItem.prescription_number || "Не задан"}}
                        </b-td>
                        <b-td
                            stacked-heading="Дата к исполнению"
                        >
                            <b>{{ detailedItem.prescription_start_datetime | formattedDate }}</b>
                        </b-td>
                        <b-td
                            stacked-heading="Содержимое предписания"
                            v-if="detailedItem.prescription_comment"
                            style="white-space: pre-line;"
                        >{{ detailedItem.prescription_comment || "Пока не заполнено" }}
                        </b-td>
                        <b-td
                            stacked-heading="Составитель предписания"
                        >
                            <template v-if="detailedItem.master">
                              {{ detailedItem.master.name }}
                            </template>
                            <template v-else>
                              Не известно
                            </template>
                        </b-td>
                        <b-td
                            stacked-heading="Клиент"
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
                        <template v-if="detailedItem.prescription_contract">
                          <b-td
                              stacked-heading="Номер договора"
                          >
                              <b>{{ detailedItem.prescription_contract.contract_number }}</b>
                          </b-td>
                          <b-td
                              stacked-heading="Адрес из договора"
                          >
                              {{ detailedItem.prescription_contract.contract_address }}
                          </b-td>
                          <b-td
                              stacked-heading="Дата заключения договора"
                          >
                              {{ detailedItem.prescription_contract.contract_start_datetime | formattedDate }}
                          </b-td>
                        </template>
                        <b-td
                            stacked-heading="Дата добавления предписания в систему"
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
