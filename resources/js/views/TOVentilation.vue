<template>
    <div>
        <to-table
            :key="componentRefreshKey"
            :isNeedCreate="true"
            :typeOfTableFilter="'ventilation'"
        ></to-table>
    </div>
</template>

<script>
    import TOVentilationTable from "../components/addresses/TOVentilationTable"

    export default {
        components: {
            'to-table': TOVentilationTable
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
            }
        }
    }
</script>
