<template>
    <div>
        <vdgo-table
            :key="componentRefreshKey"
            :isNeedCreate="true"
            :typeOfTableFilter="'vdgo'"
        ></vdgo-table>
    </div>
</template>

<script>
    import TOVDGOTable from "../components/addresses/TOVDGOTable"

    export default {
        components: {
            'vdgo-table': TOVDGOTable
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
