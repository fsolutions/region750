<template>
    <div>
        <prescriptions-table
            :key="componentRefreshKey"
            :contractForTO="{}"
            :isNeedCreate="false"
            :typeOfTableFilter="'prescriptions'"
        ></prescriptions-table>
    </div>
</template>

<script>
    import PrescriptionTable from "../components/prescriptions/PrescriptionTable"

    export default {
        components: {
            'prescriptions-table': PrescriptionTable
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
