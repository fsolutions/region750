<template>
    <div>
        <orders-table
            :key="componentRefreshKey"
            :contractForTO="{}"
            :isNeedCreate="false"
            :typeOfTableFilter="'orders'"
        ></orders-table>
    </div>
</template>

<script>
    import { API_POST_NOTIFICATIONS_ORDERS_READ } from "../constants"
    
    import OrderTable from "../components/orders/OrderTable"

    export default {
        components: {
            'orders-table': OrderTable
        },
        mixins: [
        ],
        data() {
            return {
                componentRefreshKey: 0,
                user: auth.user,
            }
        },
        watch: {
        },
        mounted() {
            this.clearOrdersNotificationList()
        },
        methods: {
            clearOrdersNotificationList: async function(service_id) {
                const response = await api.call('post', API_POST_NOTIFICATIONS_ORDERS_READ, {
                    place: 'left_menu'
                })
                this.$store.dispatch('GET_NOTIFICATIONS')
            },
            updateParentData() {
                componentRefreshKey++
            }
        }
    }
</script>
