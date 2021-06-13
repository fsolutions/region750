<template>
    <div id="app-sidebar">
        <div id="mySidebar" :class="handlerSidebar ? 'sidebar sidebar-wide mt-2':'sidebar sidebar-narrow mt-2'">
            <div class="hidden-left-block">
                <div v-show="!handlerSidebar" class="title-active-pages" v-html="handlerActivePageName()"></div>
            </div>
            <div class="scroll-sidebar">
                <!-- <router-link to="/dashboard" class="menu-item-link mt-5">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block"><span class="menu-icon-child-home"><i class="fas fa-home"></i></span></div>
                        </li>
                        <li class="list-group-item  list-group-name pl-0"><span class="menu-item-name">Информация</span></li>
                    </ul>
                </router-link> -->
                <router-link to="/contracts" class="menu-item-link colored-menu-link green-menu-link">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block"><span class="menu-icon-child-company"><i class="fas fa-home"></i></span></div>
                        </li>
                        <li class="list-group-item menu-name list-group-name pl-0"><span class="menu-item-name">Договоры</span></li>
                    </ul>
                </router-link>
                <router-link to="/calendar" class="menu-item-link colored-menu-link yellow-menu-link" v-if="canSeeUsers()">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block"><span class="menu-icon-child-all"><i class="fas fa-calendar"></i></span></div>
                        </li>
                        <li class="list-group-item menu-name list-group-name pl-0"><span class="menu-item-name">Календарь</span></li>
                    </ul>
                </router-link>
                <router-link to="/orders" class="menu-item-link colored-menu-link blue-menu-link">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block">
                                <span class="menu-icon-child-order"><i class="fas fa-clipboard-check"></i></span>
                            </div>
                        </li>
                        <li class="list-group-item menu-name list-group-name pl-0">
                            <span class="menu-item-name">Обращения</span>
                            <!-- <b-badge class="badge-icon badge-light ml-1" pill variant="success" v-if="notificationsLocal.orders.length > 0">
                                {{notificationsLocal.orders.length}}
                            </b-badge> -->
                        </li>
                    </ul>
                </router-link>
                <router-link to="/prescriptions" class="menu-item-link colored-menu-link red-menu-link">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block"><span class="menu-icon-child-order"><i class="fas fa-file-alt"></i></span></div>
                        </li>
                        <li class="list-group-item menu-name list-group-name pl-0"><span class="menu-item-name">Предписания</span></li>
                    </ul>
                </router-link>
                <router-link to="/history" class="menu-item-link colored-menu-link brown-menu-link" v-if="!canSeeUsers()">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block"><span class="menu-icon-child-all"><i class="fas fa-history"></i></span></div>
                        </li>
                        <li class="list-group-item menu-name list-group-name pl-0"><span class="menu-item-name">История</span></li>
                    </ul>
                </router-link>
                <router-link to="/users" class="menu-item-link colored-menu-link gray-menu-link" v-if="canSeeUsers()">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block"><span class="menu-icon-child-all"><i class="fas fa-user"></i></span></div>
                        </li>
                        <li class="list-group-item menu-name list-group-name pl-0"><span class="menu-item-name">Пользователи</span></li>
                    </ul>
                </router-link>
                <router-link to="/addresses" class="menu-item-link colored-menu-link gray-menu-link" v-if="canSeeUsers()">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block"><span class="menu-icon-child-all"><i class="fas fa-map-marked-alt"></i></span></div>
                        </li>
                        <li class="list-group-item menu-name list-group-name pl-0"><span class="menu-item-name">Адреса</span></li>
                    </ul>
                </router-link>
                <span class="menu-item-link colored-menu-link purple-menu-link" v-if="!canSeeUsers()" @click="fastOrderMaster()" style="cursor: pointer;">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item menu-icon mr-4 pr-0">
                            <div class="menu-icon-block"><span class="menu-icon-child-all"><i class="fas fa-comments"></i></span></div>
                        </li>
                        <li class="list-group-item menu-name list-group-name pl-0"><span class="menu-item-name">Обратная связь</span></li>
                    </ul>
                </span>
            </div>
            <div class="sidebar-copy-text">
                © 2021 Комплексное техническое обслуживание внутридомового газового оборудования, внутриквартирного газового оборудования, вентканалов и дымоходов
            </div>
            <span class="openbtn" @click="showOrHideSideBar"><i :class="handlerSidebar == true ? 'fas fa-angle-left' : 'fas fa-bars'"></i></span>
        </div>
        <fast-order-form
            :openForm="fastOrderFormOpened"
            @sended="sendedFastOrderForm"
        ></fast-order-form>
    </div>
</template>

<script>
    import fastOrderForm from '../orders/fastOrderForm'
    import { API_USERS_LISTS } from '../../constants'

    const initialNotifications = () => ({
        'orders': [],
    })

    export default {
        props: {
            user: { type: Object, required: true },
        },
        components: {
            'fast-order-form': fastOrderForm
        },
        data() {
            return {
                notificationsLocal: initialNotifications(),
                handlerSidebar: false,
                internalUsers: [],
                isOnlyForAccountant: false,
                visible: true,
                fastOrderFormOpened: false,
            }
        },
        computed: {
            notifications() {
                return this.$store.getters.NOTIFICATIONS
            }
        },
        mounted () {
            this.updateStyle()
            this.onlyForAccountant()
            this.showOrHideSideBar()
            this.$store.dispatch('GET_NOTIFICATIONS')
            setInterval( () => {
                this.$store.dispatch('GET_NOTIFICATIONS')
            }, 30000)
        },
        watch: {
            notifications(newNotifications) {
                this.distributeNotifications(newNotifications)
            },
        },
        methods: {
            handlerActivePageName(){
                if(this.$route.meta.title){
                    return this.$route.meta.title
                }

                for(let userId in this.internalUsers){
                    if(this.$route.params.user_id == this.internalUsers[userId].id){
                        return 'Запросы: '+this.internalUsers[userId].name
                    }
                }
            },
            showOrHideSideBar() {
                if(this.handlerSidebar == true) {
                    this.visible = false

                    setTimeout(() => {
                        document.getElementById("mySidebar").style.width = "60px";
                        if(this.$mq != 'sm'){
                            document.getElementById("main").style.marginLeft = "60px";
                            // document.getElementById("footer").style.marginLeft = "60px";
                        }
                    }, 300)
                    this.handlerSidebar = false

                } else{
                    document.getElementById("mySidebar").style.width = "300px";
                    if(this.$mq != 'sm'){
                        document.getElementById("main").style.marginLeft = "300px";
                        // document.getElementById("footer").style.marginLeft = "300px";
                    }
                    setTimeout(() => {
                        this.visible = true
                    }, 300)
                    this.handlerSidebar = true
                }
            },
            distributeNotifications(newNotifications = []) {
                this.notificationsLocal = initialNotifications();

                newNotifications.forEach((notify) => {
                    if (notify.data.parameters.order_id) {
                        this.notificationsLocal['orders'].push(notify)
                    }
                })
            },
            canSeeUsers() {
                if (!this.user.role.includes("client")) {
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
            },
            updateStyle() {
                let idContent = document.getElementById("main")
                idContent.setAttribute("style", "margin-left: 60px;")
            },
            fastOrderMaster() {
                this.fastOrderFormOpened = true
            },
            sendedFastOrderForm() {
                this.fastOrderFormOpened = false
            }        
        }
    }
</script>
<style scoped>
.colored-menu-link .menu-item {
    max-height: 55px;
}
.colored-menu-link > .list-group-item {
    border: 0!important;
    border-radius: 0!important;
}

.colored-menu-link .menu-icon-block {
    background: transparent!important;
    color: #FFF;
}

.router-link-active .menu-item-name,
.colored-menu-link:hover .menu-item-name{
    color: #fff;
}

.green-menu-link .menu-icon,
.green-menu-link.router-link-active .menu-item,
.green-menu-link:hover .menu-item{
    background: #289c75!important;
    border-radius: 0!important;
}

.blue-menu-link .menu-icon,
.blue-menu-link.router-link-active .menu-item,
.blue-menu-link:hover .menu-item {
    background: #2d60a2!important;
    border-radius: 0!important;
}

.red-menu-link .menu-icon,
.red-menu-link.router-link-active .menu-item,
.red-menu-link:hover .menu-item {
    background: #cd4640!important;
    border-radius: 0!important;
}

.yellow-menu-link .menu-icon,
.yellow-menu-link.router-link-active .menu-item,
.yellow-menu-link:hover .menu-item {
    background: #cb9e65!important;
    border-radius: 0!important;
}

.brown-menu-link .menu-icon,
.brown-menu-link.router-link-active .menu-item,
.brown-menu-link:hover .menu-item {
    background: #8e642f!important;
    border-radius: 0!important;
}

.purple-menu-link .menu-icon,
.purple-menu-link.router-link-active .menu-item,
.purple-menu-link:hover .menu-item {
    background: #98548f!important;
    border-radius: 0!important;
}

.gray-menu-link .menu-icon,
.gray-menu-link.router-link-active .menu-item,
.gray-menu-link:hover .menu-item {
    background: #807f80!important;
    border-radius: 0!important;
}

</style>