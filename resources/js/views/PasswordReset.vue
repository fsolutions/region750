<template>
    <div>
        <div class="row main-form">
            <div class="col">
                <div class="text-center"><img class="img-login" :src="'/img/header-logo.png'"></div>
                <template v-if="!resetted">
                  <h1 class="text-center">Восстановление пароля</h1>
                  <validation-errors :errors="validationErrors" v-if="validationErrors"></validation-errors>
                  <div class="form-row">
                      <div class="form-group col p-0 mt-4">
                          <label for="phone">Введите ваш телефон</label>
                          <input type="text" name="phone" class="form-control" v-model="maskPhone" v-mask="'+7 (###) ###-##-##'" placeholder="+7 (000) 000-00-00">
                          <div class="small mt-2">На этот номер телефона придет новый пароль.</div>
                      </div>
                  </div>
                  <div class="form-row">
                      <div class="col p-0 mt-3">
                          <button @click="reset()" class="btn btn-primary btn-action w-100">Восстановить пароль<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                      </div>
                  </div>
                  <div class="form-row">
                      <div class="col p-0 text-center">
                          <router-link to="/login" style="line-height: 36px;">Войти в аккаунт &raquo;</router-link>
                      </div>
                  </div>
                </template>
                <template v-if="resetted">
                    <h1 class="text-center">Пароль восстановлен!</h1>
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
    export default {
        data() {
            return {
                maskPhone: '',
                resetted: false,
                validationErrors: '',
                savingProcess: false,
                // authenticated: auth.check(),
            };
        },
        mounted() {
            this.updateStyle()
            // if (this.authenticated) {
            //     this.$router.push('/orders');
            // }
        },
        watch: {
            phone() {
                this.validationErrors = ''
            }
        },
        methods: {
          reset() {
            this.savingProcess = true

            let data = {
                phone: this.maskPhone.replace(/[^0-9]/g, '')
            };

            api.call('post', '/api/password-reset', data)
                .then(({data}) => {
                    this.savingProcess = false
                    this.resetted = true
                    this.validationErrors = ""
                })
                .catch((response) => { 
                    this.savingProcess = false
                    this.validationErrors = response.data.error
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
