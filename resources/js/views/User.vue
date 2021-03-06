<template>
    <div>
        <app-table
            :name="'user'"
            :typeOfTableFilter="'user'"
            :api="tableApiUrl"
            :items.sync="items"
            :isNeedSearch="true"
            @show="showItem"
            @edit="createOrEditItemModal"
            @delete="deleteItem"
            @sendSms="sendSms"
        ></app-table>

        <b-sidebar
            v-if="isSidebarOpen"
            v-model="isSidebarOpen"
            id="sidebar-right"
            right
            backdrop
            shadow
            width="35em"
            backdrop-variant="dark"
            ref="editItem"
            :title="modalTitle()"
            @hidden="closeSidePanelCallback()"
        >
            <div class="d-block">
                <validation-errors :errors="validationErrors" v-if="validationErrors" @stopLoaders="stopLoaders"></validation-errors>

                <form @submit.prevent="createOrUpdateItem()" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="lastName">Фамилия</label>
                            <input v-model="lastName" required type="text" class="form-control" id="lastName">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="firstName">Имя</label>
                            <input v-model="firstName" required type="text" class="form-control" id="firstName">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="surName">Отчество</label>
                            <input v-model="surName" required type="text" class="form-control" id="surName">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Телефон</label>
                            <input v-mask="'+# (###) ###-##-##'" required v-model="maskPhone" type="text" class="form-control" id="phone">
                        </div>
                        <!-- <div class="form-group col-md-6">
                            <label for="position">Должность</label>
                            <input v-model="editedItem.position" type="text" class="form-control" id="position">
                        </div> -->
                        <div class="form-group col-md-6">
                            <label for="email">Email</label>
                            <input v-model="editedItem.email" type="email" class="form-control" id="email">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="phone">Пароль</label>
                            <input v-model="editedItem.password" :required="editIndex == -1 ? true:false" min="8" type="password" class="form-control" id="password">
                        </div>
                        <div class="form-group col-md-6 text-center">
                            <label for="phone">Придумать пароль</label>
                            <button type="button" @click="generatePassword()" class="btn btn-secondary btn-sm">Создать пароль</button>
                            <div class="mt-2">{{editedItem.password}}</div>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="role">Роль</label>
                            <b-form-select v-model="editedItem.role" required :options="rolesList" id="role"></b-form-select>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Сохранить<i class="fas fa-spinner fa-spin ml-1" v-if="savingProcess"></i></button>
                    </div>
                </form>
            </div>
        </b-sidebar>
        <b-sidebar
            v-model="isSidebarOpenDetail"
            id="sidebar-right"
            title="Детальная информация"
            right
            backdrop
            shadow
            width="35em"
            backdrop-variant="dark"
            ref="showItem"
        >
            <div class="d-block">
                <pre>{{detailedItem}}</pre>
            </div>
        </b-sidebar>
        <b-sidebar
            v-model="isSidebarOpenForSMS"
            id="sidebar-right"
            title="Отправка SMS"
            right
            backdrop
            shadow
            width="35em"
            backdrop-variant="dark"
            ref="showItem"
        >
            <div class="d-block">
                <p>
                    <b>Имя пользователя:</b> {{userForSMS.name}}<br>
                    <b>Телефон:</b> {{userForSMS.phone}}
                </p>
                <b-form-textarea
                    id="textarea"
                    v-model="sms_text"
                    placeholder="Введите сообщение для SMS. Старайтесь делать его небольшим, чтобы экономить бюджет компании..."
                    rows="5"
                    max-rows="6"
                ></b-form-textarea>
                <div class="text-right mt-4">
                    <button @click="sendSmsAction()" :disabled="sms_text.length ==0 ? true:false" class="btn btn-action btn-primary w-100 mt-1">Отправить SMS<i class="fas fa-spinner fa-spin ml-1" v-if="sendingSMS"></i></button>
                </div>
            </div>
        </b-sidebar>
  </div>
</template>

<script>
    import {API_USERS, API_POST_SEND_SMS} from "../constants"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        constantsUserRoles,
        mixinFIO
    } from '../mixins'

    const initialEditedItem = () => ({
        id: '',
        name: '',
        phone: '',
        position: '',
        email: '',
        password: '',
        role: 'client',       // if we want to set new role
        roles: []
    })

    export default {
    mixins: [
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
        constantsUserRoles,
        mixinFIO
    ],
    data() {
        return {
            tableApiUrl: API_USERS,
            fieldFIO: 'name',
            maskPhone: '',
            isSidebarOpenForSMS: false,
            userForSMSIndex: '',
            userForSMS: {},
            sms_text: '',
            sendingSMS: false
        }
    },
    mounted() {
        this.editedItem = initialEditedItem()
        this.userForSMS = initialEditedItem()
    },
    watch: {
        'maskPhone': function (phone){
            if (phone) {
                let freshPhone = phone.replace(/[^0-9]/g, '')
                this.editedItem.phone = freshPhone
            }
        },
        isSidebarOpenForSMS(value) {
            if (!value) {
                this.userForSMSIndex = ''
                this.userForSMS = initialEditedItem()
                this.sms_text = ''
                this.sendingSMS = false
            }
        }
    },
    methods: {
        makeToast(variant = null) {
            this.$bvToast.toast(variant === 'success' ? 'Пользователь сохранен' : 'Пользователь удален', {
                title: `Оповещение`,
                variant: variant,
                solid: true
            })
        },
        modalTitle() {
            let title = 'Добавление пользователя'
            if (this.editIndex > -1) {
                title = 'Пользователь #' + this.editedItem.id
            }

            return title
        },
        // calls from mixin on item edit
        onItemEditModalCallback() {
            if (this.editedItem.roles.length > 0) {
                this.editedItem.role = this.editedItem.roles[0].slug
            }
            this.maskPhone = this.editedItem.phone
            this.splitFIO()
        },
        // calls from mixin on item save
        onCreateOrUpdateItemCallback() {
            this.setFIO()
        },
        // calls from mixin on item save
        onCreatedOrUpdatedCallback() {
            this.isSidebarOpen = false
        },
        // calls from mixin on modal close
        closeSidePanelCallback() {
            this.editedItem = initialEditedItem()
            this.maskPhone = ''
            this.lastName = ''
            this.firstName = ''
            this.surName = ''
        },
        generatePassword() {
            let randomstring = Math.random().toString(36).slice(-8);
            this.editedItem.password = randomstring
        },
        sendSms(index) {
            this.isSidebarOpenForSMS = true
            this.userForSMSIndex = index
            this.userForSMS = this.items.data[index]
        },
        sendSmsAction() {
            this.sendingSMS = true

            const data = {
                phone: this.userForSMS.phone,
                message: this.sms_text
            }
            api.call("post", API_POST_SEND_SMS, data).then(({data}) => {
                this.isSidebarOpenForSMS = false
                this.$bvToast.toast('SMS сообщение успешно отправлено пользователю!', {
                    title: `Оповещение`,
                    variant: 'success',
                    solid: true
                })
            }).catch(() => {
                this.$bvToast.toast('SMS сообщение не отправлено! Обратитесь к администратору.', {
                    title: `Оповещение`,
                    variant: 'danger',
                    solid: true
                })
            })
            .finally(() => {
                this.sendingSMS = false
            })

        }
    }
  }

</script>
