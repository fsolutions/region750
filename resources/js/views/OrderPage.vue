<template>
    <div>
        <b-row>
            <b-col>
                <h5>
                    <span
                        @click="hasHistory()
                        ? $router.go(-1)
                        : $router.push('/')"
                        class="button-come-back mr-2"><i class="fas fa-angle-left"></i>
                    </span> Обращение #{{order_id}}
                </h5>
            </b-col>
        </b-row>
        <div class="mt-4 position-relative">
            <order-show-one
                v-if="!dataOverlay"
                :detailedItem="detailedItem"
            ></order-show-one>
            <b-overlay
                :show="dataOverlay"
                spinner-variant="success"
                no-wrap
            ></b-overlay>
        </div>
    </div>
</template>

<script>
    import {API_ORDERS} from "../constants"
    import OrderShowOne from '../components/orders/OrderShowOne'

    export default {
        components: {
            'order-show-one': OrderShowOne
        },
        data() {
            return {
                dataOverlay: true,
                tableApiUrl: API_ORDERS,
                order_id: '',
                detailedItem: {},
                componentRefreshKey: 0
            }
        },
        created() {
            this.loadOneFinance()
        },
        watch: {
            $route(to, from) {
                this.loadOneFinance()
            },
        },
        methods: {
            loadOneFinance() {
                this.dataOverlay = true
                if (this.$route.params.order_id) {
                    this.order_id = this.$route.params.order_id

                    api.call("get", `${this.tableApiUrl}/${this.order_id}`).then(({data}) => {
                        this.detailedItem = data
                        this.componentRefreshKey += 1
                        this.dataOverlay = false
                    })
                }
            },
            hasHistory () {
                return window.history.length > 2
            },
        }
    }
</script>
