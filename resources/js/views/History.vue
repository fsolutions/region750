<template>
    <div>
        <app-table
            :api="tableApiUrl"
            :items.sync="items"
            :isNeedSearch="false"
            :isNeedCreate="false"
            :onDataEmptyMessage="'История в вашем личном кабинете отсутствует.'"
        ></app-table>
  </div>
</template>

<script>
    import {API_HISTORY} from "../constants"

    import {
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
    } from '../mixins'

    export default {
    components: {
    },
    mixins: [
        defaultDataItems,
        actionShowItem,
        actionCreateOrUpdateItem,
        actionDeleteItem,
    ],
    data() {
        return {
            tableApiUrl: API_HISTORY,
            user: auth.user,
        }
    },
    mounted() {
    },
    watch: {
    },
    methods: {
    }
  }

</script>
