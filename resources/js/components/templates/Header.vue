<template>
    <div class="row justify-content-between header-block mb-4">
        <div class="col header-logo">
            <template v-if="$mq != 'sm'">
                <img :src="'/img/header-logo-mid.png'" class="ml-2">
                <a href="tel:+79153788117" class="head-phone-link ml-3">+7 (915) 378-81-17</a>
            </template>
            <template v-else>
                <img :src="'/img/header-logo-min.png'" class="ml-2">
            </template>
        </div>
        <div class="col">
            <ul class="list-group list-group-horizontal float-right mr-3">
                <li class="list-group-item header-group-item">
                    <div class="notification-block-parent" @click="sidebarNotification = !sidebarNotification">
                        <span class="pulse" v-if="notificationsLocal['bell'].length > 0"></span>
                        <b-iconstack font-scale="3">
                            <b-icon stacked icon="circle-fill" variant="notification-background"></b-icon>
                            <b-icon stacked icon="bell-fill" scale="0.5" variant="notification-icon"></b-icon>
                        </b-iconstack>
                        <div class="notification-block-child" v-if="notificationsLocal['bell'].length > 0">
                            <span class="badge badge-light">{{notificationsLocal['bell'].length}}</span>
                        </div>
                    </div>
                </li>
                <li class="list-group-item header-group-item">
                    <router-link to="/dashboard">
                        <div class="user-block-parent" id="popover-right-nav-menu">
                            <b-avatar :size="35" variant="secondary" :text="nameAvatar(user.name)"></b-avatar>
                            <span class="ml-1 mr-auto pr-1 font-weight-bold user-block-name">{{user.name}}</span>
                        </div>
                    </router-link>
                </li>
                <li class="list-group-item header-group-item">
                    <div class="user-block-parent-dropdown dropdown-toggle" id="popover-right-nav-menu" data-offset="-145" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-caret-down"></i>
                    </div>
                    <div class="dropdown-menu dropdown-header-right-menu mt-2">
                        <!-- <a class="dropdown-item" href="#"><span class="dropdown-icon-question"><i class="fas fa-question-circle"></i></span> Справка и поддержка</a>
                        <div class="dropdown-divider"></div> -->
                        <span class="dropdown-item">Ваш ID в системе: {{user.id}}</span>
                        <div class="dropdown-divider"></div>
                        <router-link class="dropdown-item" to="/logout"><span class="dropdown-icon-exit"><i class="fas fa-sign-out-alt exit-icon"></i></span> Выйти</router-link>
                    </div>
                </li>
            </ul>
        </div>
        <b-sidebar v-model="sidebarNotification" id="notification-sidebar" aria-controls="id" title="Уведомления" right shadow backdrop>
            <div class="pr-3 pt-1">
                <ul class="fa-ul" style="margin-left: 10px;">
                    <li v-for="(notify, index) in notificationsLocal['bell']" class="pb-3">
                        <span class="fa-li"><i class="fas fa-chevron-right"></i></span>
                        <div>
                            <span v-html="notify.data.message"></span>
                            <template v-if="notify.data.parameters.link">
                               <span @click="sidebarNotification=false"><router-link :to="notify.data.parameters.link">{{notify.data.parameters.linkText || 'Открыть'}}</router-link></span>
                            </template>
                        </div>
                        <div>
                            <small>Время: {{ formatDateTime(notify.created_at) }}</small>
                            <b-button size="sm" @click="closeNotification(index, notify.id)" class="float-right"><i class="fas fa-spinner fa-spin mr-2" v-if="notify.read_at"></i>Закрыть</b-button>
                        </div>
                    </li>
                </ul>
                <div class="text-center pb-3 pt-2" v-if="notificationsLocal['bell'].length == 0">
                    <span class="font-weight-bold text-secondary text-uppercase">Нет уведомлений</span>
                </div>
            </div>
        </b-sidebar>
    </div>
</template>

<script>
    import {API_POST_NOTIFICATIONS_READ, API_USERS_LISTS} from "../../constants"

    const initialNotifications = () => ({
        'bell': [],
    })

    export default {
        props: {
            authenticated: { type: Boolean, required: true },
            user: { type: Object, required: true },
        },
        data() {
            return {
                notificationsLocal: initialNotifications(),
                isOnlyForAccountant: false,
                internalUsers: [],
                sidebarNotification: false
            };
        },
        computed: {
            notifications() {
                return this.$store.getters.NOTIFICATIONS
            },
        },
        watch: {
            notifications(newNotifications) {
                this.distributeNotifications(newNotifications)
            },
            // '$route.path': {
            //     handler: function(pathName) {
            //         this.$store.dispatch("SELECT_MENU_ITEM", pathName);
            //     },
            //     deep: true,
            //     immediate: true
            // }
        },
        mounted() {
            if (this.authenticated && this.user) {
                this.startGettingNotifications()
                this.onlyForAccountant()
            }
        },
        methods: {
            nameAvatar(name) {
                let replaceName = name.replace(/[^А-Я]/g, "")
                return replaceName
            },
            distributeNotifications(newNotifications = []) {
                this.notificationsLocal = initialNotifications();

                newNotifications.forEach((notify) => {
                    if (notify.data.parameters.place[0] == 'bell') {
                        this.notificationsLocal['bell'].push(notify)
                    }
                })
            },
            closeNotification(index, id) {
                this.notificationsLocal['bell'][index].read_at = 'reading'

                api.call("post", `${API_POST_NOTIFICATIONS_READ}/${id}`).then(({data}) => {
                    this.notificationsLocal['bell'].splice(index, 1);
                })
            },
            formatDateTime(dateString) {
                if (!dateString) {
                    return dateString
                }
                let formattedDate = (new Date(dateString)).toLocaleDateString()
                let formattedTime = (new Date(dateString)).toLocaleTimeString()
                let formattedTimeArray = formattedTime.split(":");

                return formattedDate + " " + formattedTimeArray[0] + ":" + formattedTimeArray[1]
            },
            closeMenu($event) {
                $($event.currentTarget).closest('.navbar-collapse').collapse('hide');
            },
            startGettingNotifications() {
                this.$store.dispatch('GET_NOTIFICATIONS')
            },
            onlyForAdmins() {
                if (this.user.role.includes("administrator")) {
                    return true
                }
                return false
            },
            onlyForAccountant() {
                if (this.user.role.includes("administrator") ||
                    this.user.role.includes("head-accountant") ||
                    this.user.role.includes("accountant")) {
                    this.isOnlyForAccountant = true
                    this.getAllFinanceUsers()
                    return true
                }
                return false
            },
            getAllFinanceUsers() {
                api.call("get", `${API_USERS_LISTS}?list_type=for-accountants`).then(({data}) => {
                    this.internalUsers = data
                    this.$store.dispatch('GET_NOTIFICATIONS')
                })
            }
        }
    }
</script>

<style scoped>
.head-phone-link {
    font-size: 21px;
    color: #362518;
    text-transform: uppercase;
    font-weight: bold;
}
.pulse {
    margin: 100px;
    display: block;
    width: 43.19px;
    height: 43.19px;
    border-radius: 50%;
    background: transparent;
    cursor: pointer;
    box-shadow: 0 0 0 rgb(255 152 0);
    -webkit-animation: pulse-data-v-aab7e1e4 2s infinite;
    animation: pulse-data-v-aab7e1e4 2s infinite;
    position: absolute;
    top: -100px;
    z-index: 1;
    left: -100px;
}
.pulse:hover {
  animation: none;
}

@-webkit-keyframes pulse {
  0% {
    -webkit-box-shadow: 0 0 0 0 rgb(255 152 0);
  }
  70% {
      -webkit-box-shadow: 0 0 0 10px rgba(204,169,44, 0);
  }
  100% {
      -webkit-box-shadow: 0 0 0 0 rgba(204,169,44, 0);
  }
}
@keyframes pulse {
  0% {
    -moz-box-shadow: 0 0 0 0 rgb(255 152 0);
    box-shadow: 0 0 0 0 rgb(255 152 0);
  }
  70% {
      -moz-box-shadow: 0 0 0 10px rgba(204,169,44, 0);
      box-shadow: 0 0 0 10px rgba(204,169,44, 0);
  }
  100% {
      -moz-box-shadow: 0 0 0 0 rgba(204,169,44, 0);
      box-shadow: 0 0 0 0 rgba(204,169,44, 0);
  }
}
</style>
