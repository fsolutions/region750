<template>
    <div>
        <div class="row main-form mb-5">
            <div class="col">
                <div class="text-center"><img class="img-login" :src="'/img/header-logo.png'"></div>
                <template v-if="!registered">
                    <h1 class="text-center">Регистрация</h1>
                    <validation-errors :errors="validationErrors" v-if="validationErrors"></validation-errors>
                    <form @submit.prevent="createClient()" class="mb-0">
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="lastName">Фамилия</label>
                                <input v-model="lastName" required type="text" class="form-control" id="lastName">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="firstName">Имя</label>
                                <input v-model="firstName" required type="text" class="form-control" id="firstName">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="surName">Отчество</label>
                                <input v-model="surName" required type="text" class="form-control" id="surName">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="phone">Телефон</label>
                                <input v-mask="'+# (###) ###-##-##'" required v-model="maskPhone" type="text" class="form-control" id="phone">
                            </div>
                            <div class="form-group col-md-12">
                                <label for="email">Email (Если есть)</label>
                                <input v-model="client.email" type="email" class="form-control" id="email">
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
                                <button :disabled="!disabled" type="submit" class="btn btn-action btn-primary w-100 mt-1">Зарегистрироваться<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col p-0 text-center">
                                <router-link to="/login" style="line-height: 36px;">Войти в аккаунт &raquo;</router-link>
                            </div>
                        </div>
                    </form>
                </template>
                <template v-if="registered">
                    <h1 class="text-center">Регистрация прошла успешно!</h1>
                    <b-card-text class="text-center">
                        <h4 class="card-title my-5">
                            Доступ отправлен к вам на телефон. <br>
                            <router-link to="/login">Войдите в систему &raquo;</router-link>
                        </h4>
                    </b-card-text>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
    import {API_REGISTRATION} from "../constants"
    import {
    } from '../mixins'

    export default {
    mixins: [
    ],
    data() {
        return {
            client: {
                name: '',
                phone: '',
                email: '',
                password: '',
                password_confirmation: '',
            },
            firstName: '',
            lastName: '',
            surName: '',
            disabled: false,
            maskPhone: '',
            registered: false,
            validationErrors: '',
            savingProcess: false,
        }
    },
    watch: {
        'maskPhone': function (phone){
            if (phone) {
                let freshPhone = phone.replace(/[^0-9]/g, '')
                this.editedItem.phone = freshPhone
            }
        },
    },
    methods: {
        createClient() {
            this.savingProcess = true

            this.client.phone = this.maskPhone
            if(this.lastName && this.firstName && this.surName) {
                // Присваиваем обьекту user.name ФИО из полей
                this.client.name = `${this.lastName} ${this.firstName} ${this.surName}`
            }
            this.client.password = this.generatePassword()
            this.client.password_confirmation = this.client.password
            api.call('post', API_REGISTRATION, this.client)
                .then(({data}) => {
                    this.registered = true
                    this.validationErrors = ""
                    this.savingProcess = false
                })
                .catch((response) => {
                    this.validationErrors = response.data.error
                    this.savingProcess = false
                });
        },
        generatePassword() {
            let randomstring = Math.random().toString(36).slice(-8)
            return randomstring
        }
    }
}
</script>
<style scoped>
    .main-form {
        margin-top: 30px;
        margin: 0 auto;
        max-width: 610px;
        position: relative;
        top: 14px;
        background: #ffffff;
        color: black;
        text-shadow: none;
        box-shadow: 0px 0px 8px 0px #dadada;
        border-radius: 12px;
        padding: 23px 15px;
    }
    .img-login {
        width: 100%;
        max-width: 312px;
        height: auto;
    }
</style>