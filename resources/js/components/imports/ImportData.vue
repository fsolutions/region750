<template>
    <div class="row mt-3">
        <div class="col-sm-12 col-md-6">
            <input
                class="form-control"
                type="file"
                name="file"
                ref="file"
                @change="handleFileUpload()"
            />
            <div class="text-right mt-2 mb-2">
                <button
                    class="btn btn-primary"
                    @click.prevent="submitFile"
                    v-if="this.file"
                >Начать импорт<i class="fas fa-spinner fa-spin ml-1" v-if="isDataLoading"></i></button>
            </div>
            <div style="text-style: italic;"><b>Правила импорта:</b>
                <ol class="mt-2 ml-1 pl-3">
                    <li>Файл импорта должен соответствовать формату из <a href="/examples/import_example.xlsx" target="_blank">примера</a>, то есть, наименование колонок, количество колонок.</li>
                    <li>Файл не должен содержать больше 3000 строк. Первой строка должны идти заголовки.</li>
                    <li>Если не будет номера телефона - Пользователь и Договор не добавятся, Адрес - добавится.</li>
                    <li>Если не будет адреса - Пользователь и Договор добавятся.</li>
                    <li>Если не будет номера Договора - Пользователь и Адрес добавятся.</li>
                    <li>SMS оповещения не будут отправлены новым пользователя.</li>
                </ol>
            </div>
        </div>
        <div class="col-sm-12 col-md-6">
            <div v-if="ImportResult.length > 0">
                <h5 class="mt-3 mb-4">Импорт успешно завершен!</h5>
                <div v-for="(item, index) in ImportResult" :key="index">
                    <p><b>{{ item.name }}:</b> {{ item.count }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {API_IMPORT} from "../../constants";

    export default {
        data(){
            return {
                file: '',
                ImportResult: [],
                apiImport: API_IMPORT,
                isDataLoading: false
            }
        },
        methods: {
            submitFile(){
                this.isDataLoading = true
                this.ImportResult = []
                const config = { 'content-type': 'multipart/form-data', "x-requested-with": "XMLHttpRequest" }
                const formData = new FormData();
                formData.append('file', this.file);

                api.call("post", this.apiImport, formData, config)
                    .then((data) => {
                        this.isDataLoading = false
                        this.file = ''
                        this.ImportResult = data.data
                        console.log('success', data)
                    })
            },
            handleFileUpload(){
                this.file = this.$refs.file.files[0];
            }
        }
    }


</script>

