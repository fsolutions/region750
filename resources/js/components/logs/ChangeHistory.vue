<template>
    <div>
        <b-table
            id="main-table"
            :items="items"
            :fields="fields"
            hover
            fixed
            show-empty
        >
            <template #cell(operation_name)="row">
                {{vocabular[row.item.operation_name]}}
            </template>

            <template #cell(show_details)="row">
                <div id="button">
                    <button v-if="checkShow(row.item.fields_value)" size="sm" @click="row.toggleDetails" class="btn btn-outline-secondary">
                    {{ row.detailsShowing ? 'Закрыть' : 'Открыть'}}
                    </button>
                </div>
            </template>

            <template #row-details="row">
                <b-card>
                    <b-row class="mb-2">
                        <b-col sm="12">
                            <table class="table table-borderless table-fixed">
                                <tbody v-html="getFieldsValue(row.item.fields_value)">
                                </tbody>
                            </table>
                        </b-col>
                    </b-row>
                </b-card>
            </template>

            <template #empty="scope">
                <p class="my-4"><em>У элемента пока нет истории для отображения.</em></p>
            </template>
        </b-table>
    </div>
</template>

<script>
import {API_LOG_HISTORY, VOCABULAR} from "../../constants"

export default {
    props: {
        changeHistoryId: { type: Number, required: false },
        changeModel: { type: Array, required: false },
        changeLoad: { type: Boolean, default: false, required: false },
        changeLoadOrder: { type: Boolean, default: false, required: false },
    },
    data() {
        return {
            vocabular: VOCABULAR,
            changeLogs: API_LOG_HISTORY,
            items: [],
            fields: [
                { key: 'user.name', label: 'Пользователь' },
                { key: 'operation_name', label: 'Операция' },
                { key: 'name', label: 'Обращение/Услуга/Свойство' },
                { key: 'created_at', label: 'Дата создания' },
                { key: 'show_details', label: 'Подробная информация', class: 'text-center' }
            ],
        }
    },
    watch: {
        changeLoad: function(){
            this.getChangeHistory()
        },
        changeLoadOrder: function(){
            this.getOrderChangeHistory()
        },
    },
    methods: {
        getChangeHistory(changeModel, changeHistoryId) {
            let getParam = `?model=${this.changeModel}&id=${this.changeHistoryId}`

            api.call("get", `${this.changeLogs}/${getParam}`).then(({data}) => {
                this.items = data
            })
        },
        getOrderChangeHistory(changeHistoryId) {
            let getParam = `order?id=${this.changeHistoryId}`

            api.call("get", `${this.changeLogs}/${getParam}`).then(({data}) => {
                this.items = data
            })
        },
        getFieldsValue(fieldsVal) {
            let outFields = [];

            for(let i in fieldsVal){

                outFields += `<tr><td class="text-right"><b>${this.vocabular[i]}</b>:</td><td class="text-left">${fieldsVal[i]}</td></tr>`
            }

            return outFields;
        },
        //check for data fields_check
        checkShow(fieldsCheck){
            if(fieldsCheck === undefined){
                return false
            }
            return true
        }
    }
}
</script>
