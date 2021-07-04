<template>
    <div>
        <h2>Импорт адресов, пользователей, договоров</h2>
        <import-data></import-data>
    </div>
</template>

<script>

    import ImportData from "../components/imports/ImportData.vue";

    export default {

        components: {
            "import-data": ImportData,

        },
    }

</script>
