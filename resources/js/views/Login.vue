<template>
    <div>
        <div class="row main-form">
            <div class="col">
                <div class="text-center"><img class="img-login" :src="'/img/header-logo.png'"></div>
                <h1 class="text-center">Вход в кабинет</h1>
                <b-alert show variant="danger" v-if="errorMsg">{{ errorMsg }}</b-alert>
                <div class="form-row">
                    <div class="form-group col p-0">
                        <label for="phone">Телефон</label>
                        <input type="text" name="phone" class="form-control" v-model="maskPhone" v-mask="'+# (###) ###-##-##'" placeholder="+7 (000) 000-00-00">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col p-0">
                        <label for="password">Пароль</label>
                        <input type="password" name="password" class="form-control" v-model="password">
                    </div>
                </div>
                <div class="form-row">
                    <div class="col p-0 mt-3">
                        <button @click="login()" class="btn btn-primary btn-action w-100">Войти</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                maskPhone: '',
                phone: '',
                password: '',
                errorMsg: '',
                authenticated: auth.check(),
            };
        },
        mounted() {
            this.updateStyle()
            if (this.authenticated) {
                this.$router.push('/orders');
            }
        },
        watch: {
            maskPhone(phone){
                if (phone) {
                    let freshPhone = phone.replace(/[^0-9]/g, '')
                    this.phone = freshPhone
                }
            },
            phone() {
                this.errorMsg = ''
            },
            password() {
                this.errorMsg = ''
            },
        },
        methods: {
            login() {
                let data = {
                    phone: this.phone,
                    password: this.password
                };

                axios.post('/api/login', data)
                    .then(({
                        data
                    }) => {
                        auth.login(data.token, data.user);

                        this.$router.push('/contracts');
                    })
                    .catch(({
                        response
                    }) => {
                        this.errorMsg = "Ошибка авторизации, попробуйте снова."
                    });
            },
            updateStyle() {
                let idContent = document.getElementById("main")
                idContent.setAttribute("style", "margin-left: 0px;")
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
