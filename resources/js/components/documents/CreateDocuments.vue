<template>
    <div>
        <div class="row mt-3">
            <div class="col-md-3"><h5 class="text-nowrap">Добавление документа</h5></div>
            <div v-if="files.length >= 1" class="col pl-0 add_download_file_block text-right"><span @click="addMultiFile()" class="add_download_file"><i class="fas fa-plus mr-1"></i> Добавить файл</span></div>
        </div>
        <b-overlay
            :show="dataOverlay"
            spinner-variant="success"
            no-wrap
        ></b-overlay>
        <validation-errors :errors="validationErrors" v-if="validationErrors"></validation-errors>
        <div class="form-row form-row-add-file" v-for="(file, index) in files" :key="index">
            <div class="form-group col">
                <label>Название документа</label>
                <div class="mb-3"><input type="text" class="form-control" v-model="file.name"></div>
                <label for="reference_document_type_id">Тип документа</label>
                <select-of-properties
                    :reference_id="34"
                    :needNullElement="false"
                    :needMultipleSelect="false"
                    :defaultSelectName="`Выберите тип документа`"
                    :internalSearch="true"
                    :selected="file.reference_document_type_id"
                    :excludeArray="excludeAddDocumentTypeArray"
                    @set="(value) => {file.reference_document_type_id = value}"
                ></select-of-properties>
            </div>
            <div class="form-group col">
                <label>Описание</label>
                <b-form-textarea
                    :id="`file_textarea_${index}`"
                    v-model="file.description"
                    placeholder=""
                    rows="5"
                    max-rows="5"
                ></b-form-textarea>
            </div>
            <div class="form-group col">
                <label>Дата составления документа</label>
                <div class="mb-3"><input type="date" class="form-control" v-model="file.date_of_document"></div>
                <label>Загрузить документ</label>
                <b-form-file
                    @change="addFile(index, $event)"
                    ref="file-input"
                    placeholder="Выберите файл"
                    drop-placeholder="Перенесите файл в это поле..."
                ></b-form-file>
                <small class="text-nowrap">Выберите файл, или перенесите его в поле загрузки*</small>
            </div>
            <div class="form-group col-1 text-center" v-if="files.length > 1">
                <b-button @click="deleteMultiFile(index)" class="trash_document btn-danger"><i class="fas fa-trash"></i></b-button>
            </div>
        </div>
        <b-alert v-if="files.length == 0" variant="secondary" show class="mt-3 text-center">
            <b class="mr-3">Документы успешно прикреплены!</b>
            <span @click="addMultiFile()" class="add_download_file"><i class="fas fa-plus mr-1"></i> добавить файлы</span>
        </b-alert>
        <div class="row" v-if="needUploadButton">
            <div class="col text-right">
                <button v-if="files.length >= 1" @click.prevent="loadingDocuments" class="btn btn-primary">Загрузить</button>
            </div>
        </div>
    </div>
</template>

<script>
    import { API_DOCUMENTS } from '../../constants'

    const initialNewFile = () => ({
        name: null,
        description: '',
        reference_document_type_id: 337,    // Другое, по умолчания
        date_of_document: null,
        attachment: null,
        loaded: false,
    })

    export default {
        mixins: [],
        props: {
            orderId: { type: Number | String, required: false, default: '' },           //order id for adding document
            needUploadButton: { type: Boolean, default: true, required: false },        // hide upload button
            uploadDocumentsNow: { type: Boolean, default: false, required: false },   //activation of the component work process
            excludeAddDocumentTypeArray: {  type: Array,
                                            required: false,
                                            default: function () {
                                                return []
                                            }
                                        },   //if you need to eclude document type for adding
            whereDocumentLoadingItemIndex: { type: Number | String, required: false, default: -1 },   //if we need to save editIndex
        },
        data() {
            return {
                files: [],
                dataOverlay: false,
                validationErrors: ''
            }
        },
        watch: {
            uploadDocumentsNow: {
                handler(value) {
                    if (value && this.orderId) {
                        this.loadingDocuments()
                    }
                },
                immediate: true
            }
        },
        mounted () {
            this.whereDocumentLoadingItemIndexLocal = this.whereDocumentLoadingItemIndex
            this.addMultiFile()
        },
        methods: {
            addMultiFile() {
                this.files.push(initialNewFile())
            },
            deleteMultiFile(index) {
                this.files.splice(index, 1)
            },
            //processing uploaded file
            addFile(index, event) {
                this.files[index].attachment = event.target.files[0]
            },
            //loading into the document database
            loadingDocuments() {
                this.dataOverlay = true
                const config = { 'content-type': 'multipart/form-data' }

                this.files.forEach((file, index) => {
                    const formData = new FormData()
                    formData.append('name', file.name)
                    formData.append('file', file.attachment)
                    formData.append('description', file.description)
                    formData.append('reference_document_type_id', file.reference_document_type_id)
                    formData.append('date_of_document', file.date_of_document)
                    formData.append('order_id', this.orderId)

                    api.call("post", API_DOCUMENTS, formData, config)
                        .then(({data}) => {
                            this.$root.$emit('updateViewDocument', data)
                            this.$emit('updateEditedItemDocuments', {file: data, editIndex: this.whereDocumentLoadingItemIndexLocal})
                            file.loaded = true
                            this.checkedLoadingFile()
                            file.name = ''
                            file.attachment = ''
                            file.description = ''
                            this.$emit("update:uploadDocumentsNow", false)
                        }).catch((response) => {
                            if (response.status == 422){
                                this.validationErrors = response.data.error
                                setTimeout(() => this.validationErrors = '', 3000);
                            }
                        }).finally(() => {
                            this.dataOverlay = false
                        })
                });
            },
            checkedLoadingFile() {
                let isLoading = true
                this.files.forEach((file, index) => {
                    if(file.loaded == false){
                        isLoading = false
                    }
                })
                if(isLoading){
                    this.$emit('documentLoaded', true)
                    this.files.length = 0
                    this.makeToast('success')
                }
            },
        }
    }
</script>
<style scoped>
    h5{
        font-size: 16px;
        margin-bottom: 20px;
    }
</style>
