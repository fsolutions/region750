<template>
    <div id="app-sidebar">
        <div id="mySidebar" class="sidebar">
            <div class="hidden-left-block">
                <div v-show="!handlerSidebar" class="title-active-pages" v-html="handlerActivePageName()"></div>
            </div>
            <div class="scroll-sidebar">
                <!-- <router-link to="/dashboard" class="menu-item-link mt-5">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item pr-1">
                            <div class="menu-icon-block"><span class="menu-icon-child-home"><i class="fas fa-home"></i></span></div>
                        </li>
                        <li class="list-group-item  list-group-name pl-0"><span class="menu-item-name">Информация</span></li>
                    </ul>
                </router-link> -->
                <router-link to="/contracts" class="menu-item-link">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item pr-1">
                            <div class="menu-icon-block"><span class="menu-icon-child-company"><i class="fas fa-home"></i></span></div>
                        </li>
                        <li class="list-group-item list-group-name pl-0"><span class="menu-item-name">Договоры</span></li>
                    </ul>
                </router-link>
                <router-link to="/calendar" class="menu-item-link" v-if="canSeeUsers()">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item pr-1">
                            <div class="menu-icon-block"><span class="menu-icon-child-company"><i class="fas fa-calendar"></i></span></div>
                        </li>
                        <li class="list-group-item list-group-name pl-0"><span class="menu-item-name">Календарь</span></li>
                    </ul>
                </router-link>
                <router-link to="/orders" class="menu-item-link">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item pr-1">
                            <div class="menu-icon-block">
                                <span class="menu-icon-child-order"><i class="fas fa-clipboard-check"></i></span>
                            </div>
                        </li>
                        <li class="list-group-item list-group-name pl-0">
                            <span class="menu-item-name">Обращения</span>
                            <!-- <b-badge class="badge-icon badge-light ml-1" pill variant="success" v-if="notificationsLocal.orders.length > 0">
                                {{notificationsLocal.orders.length}}
                            </b-badge> -->
                        </li>
                    </ul>
                </router-link>
                <router-link to="/prescriptions" class="menu-item-link">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item pr-1">
                            <div class="menu-icon-block"><span class="menu-icon-child-company"><i class="fas fa-file-alt"></i></span></div>
                        </li>
                        <li class="list-group-item list-group-name pl-0"><span class="menu-item-name">Предписания</span></li>
                    </ul>
                </router-link>
                <router-link to="/history" class="menu-item-link" v-if="!canSeeUsers()">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item pr-1">
                            <div class="menu-icon-block"><span class="menu-icon-child-company"><i class="fas fa-history"></i></span></div>
                        </li>
                        <li class="list-group-item list-group-name pl-0"><span class="menu-item-name">История</span></li>
                    </ul>
                </router-link>
                <router-link to="/users" class="menu-item-link" v-if="canSeeUsers()">
                    <ul class="list-group list-group-horizontal menu-item">
                        <li class="list-group-item pr-1">
                            <div class="menu-icon-block"><span class="menu-icon-child-company"><i class="fas fa-user"></i></span></div>
                        </li>
                        <li class="list-group-item list-group-name pl-0"><span class="menu-item-name">Пользователи</span></li>
                    </ul>
                </router-link>
                <!-- <li class="parent_item"><i class="fas fa-chevron-right"></i> <a href="">Обратная связь</a></li> -->
            </div>
            <span class="openbtn" @click="showOrHideSideBar"><i :class="handlerSidebar == true ? 'fas fa-angle-left' : 'fas fa-bars'"></i></span>
        </div>
    </div>
</template>

<script>
    import { API_USERS_LISTS } from '../../constants'

    const initialNotifications = () => ({
        'orders': [],
    })

    export default {
        props: {
            user: { type: Object, required: true },
        },
        data() {
            return {
                notificationsLocal: initialNotifications(),
                handlerSidebar: false,
                internalUsers: [],
                isOnlyForAccountant: false,
                visible: true
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
                        document.getElementById("mySidebar").style.width = "55px";
                        if(this.$mq != 'sm'){
                            document.getElementById("main").style.marginLeft = "55px";
                            // document.getElementById("footer").style.marginLeft = "55px";
                        }
                    }, 300)
                    this.handlerSidebar = false

                }else{
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
                idContent.setAttribute("style", "margin-left: 55px;")
            }
        }
    }
</script>


