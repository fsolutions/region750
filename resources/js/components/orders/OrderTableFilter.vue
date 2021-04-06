<template>
    <div class="float-left green-filter ml-3">
        <div class="btn-group"
             v-for="(menu, index) in greenFilterMenu"
             :key="index"
        >
            <span
                :class="`green-dropdown mr-0 ${menu.active ? 'active' : ''}`"
                @click="changeActive(index, 0)"
            >{{ menu.title }}</span>
            <span
                :class="`green-arrow-dropdown dropdown-toggle dropdown-toggle-split ${menu.active ? 'active' : ''}`"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <span><i class="fas fa-caret-down"></i></span>
            </span>
            <div class="dropdown-menu">
                <b-btn
                    variant="link"
                    v-for="(submenu, s_index) in menu.submenu"
                    :key="s_index"
                    :class="submenu.active ? 'dropdown-item active' : 'dropdown-item'"
                    @click="changeActive(index, s_index)"
                >{{submenu.title}}</b-btn>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            api: {type: String, required: true},              // Link on API for table working
        },
        data() {
            return {
                greenFilterMenu: [
                    {
                        title: "Обращения",
                        active: true,
                        submenu: [
                            {
                                title: "Все обращения",
                                active: true,
                            },
                            {
                                title: "Мои обращения",
                                active: false,
                                only_creator_user_orders: true,
                            },
                        ]
                    },
                    {
                        title: "Лиды",
                        active: false,
                        submenu: [
                            {
                                title: "Все лиды",
                                active: false,
                                reference_order_type_id: 1,
                                reference_sources_id: 23,
                            },
                            // {
                            //     title: "Мои лиды",
                            //     active: false,
                            //     reference_order_type_id: 1,
                            //     reference_sources_id: 23,
                            //     only_creator_user_orders: true,
                            // },
                        ]
                    },
                    {
                        title: "Заявки",
                        active: false,
                        submenu: [
                            {
                                title: "Все заявки",
                                active: false,
                                reference_order_type_id: 1,
                            },
                            {
                                title: "Мои заявки",
                                active: false,
                                reference_order_type_id: 1,
                                only_creator_user_orders: true,
                            },
                        ]
                    },
                    {
                        title: "Поставки",
                        active: false,
                        submenu: [
                            {
                                title: "Все поставки",
                                active: false,
                                reference_order_type_id: 2,
                            },
                            {
                                title: "Мои поставки",
                                active: false,
                                reference_order_type_id: 2,
                                only_creator_user_orders: true,
                            },
                        ]
                    },

                ]
            }
        },
        mounted() {
            let startSettings = this.$store.state.userLocalSettings.filterSettings.orderTable.split(',')
            this.changeActive(startSettings[0],startSettings[1])
        },
        methods: {
            changeActive(activeIndex, submenuIndex = -1) {
                let additionalParameter = ''
                this.$store.commit("SET_FILTER_SETTINGS", {
                    name: 'orderTable',
                    value: `${activeIndex},${submenuIndex}`
                });
                this.greenFilterMenu.forEach((menu, index) => {
                    menu.active = false
                    menu.submenu.forEach((submenu, s_index) => {
                        if (activeIndex == index) {
                            menu.active = true
                        }
                        submenu.active = false
                        if(submenuIndex == s_index && activeIndex == index) {
                            submenu.active = true
                            if (submenu.reference_order_type_id) {
                                additionalParameter += '&reference_order_type_id=' + submenu.reference_order_type_id
                            }
                            if (submenu.reference_sources_id) {
                                additionalParameter += '&reference_sources_id=' + submenu.reference_sources_id
                            }
                            if (submenu.only_creator_user_orders) {
                                additionalParameter += '&only_creator_user_orders=' + submenu.only_creator_user_orders
                            }
                            this.$emit('setFilteringApiParameter', additionalParameter)
                        }
                    })
                })
            }
        }
    }
</script>
