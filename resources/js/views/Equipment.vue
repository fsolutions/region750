<template>
    <div>
        <equipment-table
            :key="componentRefreshKey"
            :isNeedCreate="false"
            :typeOfTableFilter="'equipment'"
        ></equipment-table>
    </div>
</template>

<script>
    import {API_CONTRACTS} from "../constants"
    import EquipmentTable from "../components/equipment/EquipmentTable.vue"

    export default {
        components: {
            'equipment-table': EquipmentTable
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
        },
        methods: {
            updateParentData() {
                componentRefreshKey++
            },
        }
    }
</script>
