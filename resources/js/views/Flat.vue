<template>
    <div>
        <flats-table
            :key="componentRefreshKey"
            :isNeedCreate="true"
        ></flats-table>
    </div>
</template>

<script>
    import FlatTable from "../components/addresses/FlatTable"

    export default {
        components: {
            'flats-table': FlatTable
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
