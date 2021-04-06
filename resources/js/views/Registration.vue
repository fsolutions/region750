<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <h1>Регистрация</h1>
                <h5>Контактные данные</h5>
                <form @submit.prevent="createClient()" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="lastName">Фамилия</label>
                            <input v-model="lastName" type="text" class="form-control" id="lastName">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="firstName">Имя</label>
                            <input v-model="firstName" type="text" class="form-control" id="firstName">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="surName">Отчество</label>
                            <input v-model="surName" type="text" class="form-control" id="surName">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input v-model="client.email" type="email" class="form-control" id="email">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Телефон</label>
                            <input v-model="client.phone" type="text" class="form-control" id="phone">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Пароль</label>
                            <input v-model="client.password" type="password" class="form-control" id="password">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Подтвердить пароль</label>
                            <input v-model="client.password_confirmation" type="password" class="form-control" id="password">
                        </div>
                    </div>
                    <h5 class="mt-3">Реквизиты компаний</h5>
                    <small>Поиск компаний выполняется автоматически, в выпадающем списке выбрать нужную компанию.</small>
                    <vue-bootstrap-typeahead
                            v-model="daDataSearch"
                            :data="companies"
                            :serializer="item => item.value"
                            @hit="selectedCompany = $event"
                            placeholder="Начните вводить ИНН или название вашей компании…"
                            class="mb-3"
                    />
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="inputNameCompany">Название компаний</label>
                            <input v-model="client.company.name" type="text" class="form-control" id="inputNameCompany">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputTypeCompany">Тип</label>
                        <b-form-select v-model="client.company.type" :options="typeList"></b-form-select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputInnCompany">ИНН</label>
                        <input v-model="client.company.inn" ref="inn" type="text" class="form-control" id="inputInnCompany">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputKppCompany">КПП</label>
                            <input v-model="client.company.kpp" type="text" class="form-control" id="inputKppCompany">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputOgrnCompany">ОГРН</label>
                            <input v-model="client.company.orgn" type="text" class="form-control" id="inputOgrnCompany">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <b-form-checkbox
                            id="checkbox-1"
                            v-model="disabled"
                            name="checkbox-1"
                            @click="disabled = !disabled"
                            >
                            Соглашение на обработку персональных данных
                            </b-form-checkbox>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <button :disabled="!disabled" type="submit" class="btn btn-action btn-primary w-50 mt-1 float-right">Регистрация</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-right">
                <img class="image-register" :src="'/img/verb.svg'">
            </div>
        </div>
    </div>
</template>

<script>
    import {API_REGISTRATION, API_DADATA_COMPANY} from "../constants"
    import {
        typeOfCompany,
    } from '../mixins'

    export default {
    mixins: [
        typeOfCompany,
    ],
    data() {
        return {
            client: {
                id: '',
                name: '',
                phone: '',
                email: '',
                password: '',
                password_confirmation: '',
                company: {
                    id: '',
                    name: '',
                    inn: '',
                    kpp: '',
                    orgn: '',
                    type: '',
                }
            },
            firstName: '',
            lastName: '',
            surName: '',
            daDataSearch: '',
            disabled: false,
            selectedCompany: {
                value: '',
                data: {
                    inn: '',
                    kpp: '',
                    ogrn: '',
                    type: '',
                }
            },
            companies: []
        }
    },
    watch: {
        daDataSearch: function (newQuery){
            api.call('post', API_DADATA_COMPANY, {'company': this.daDataSearch})
            .then(({data}) => {
                this.companies = data
            })
        },
        selectedCompany: function(){
            this.client.company.name = this.selectedCompany.value;
            this.client.company.type = this.selectedCompany.data.type;
            this.client.company.inn = this.selectedCompany.data.inn;
            this.client.company.kpp = this.selectedCompany.data.kpp;
            this.client.company.orgn = this.selectedCompany.data.ogrn;
        }
    },
    methods: {
        createClient() {
            if(this.lastName && this.firstName && this.surName) {
                // Присваиваем обьекту user.name ФИО из полей
                this.client.name = `${this.lastName} ${this.firstName} ${this.surName}`
            }
            api.call('post', API_REGISTRATION, this.client)
        },
    }
}
</script>
