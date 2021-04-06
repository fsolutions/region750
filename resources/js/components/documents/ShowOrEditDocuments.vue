<template>
    <div>
        <div class="row mt-3 document_block">
        <b-overlay
            :show="dataOverlay"
            spinner-variant="success"
            no-wrap
        ></b-overlay>
            <div
                class="col-md-3"
                id="download_docs_view"
                v-for="(item, index) in items" :key="index"
            >
                <div class="card text-center mb-4 document_view">
                    <div class="card-body">
                        <div class="card-action-top" v-if="!noActions">
                            <span @click="editDocument(index)">
                                <i class="fas fa-pencil-alt doc-edit float-right"></i>
                            </span>
                        </div>
                        <div class="card-title">
                            <a :href="hfs+item.path" target="_blank"><img :id="'doc-logo-'+index" :src="'/img/icon-document.svg'"></a>
                            <b-tooltip v-if="item.description" :target="'doc-logo-'+index" triggers="hover" placement="bottom">
                                {{item.description}}
                            </b-tooltip>
                        </div>
                        <div class="card-info">
                            <div class="card-subtitle mb-2 document_view_name">{{item.name}}</div>
                            <div class="card-subtitle mb-1 document_view_type">{{item.type ? item.type.name : ''}}</div>
                            <div class="card-subtitle mb-0 document_view_date">{{item.created_at}}</div>
                        </div>
                        <div class="card-action-end" v-if="!noActions">
                            <span @click="deleteDocument(index)">
                                <i class="far fa-trash-alt doc-trash float-right"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <b-modal
            ref="editDocument"
            title="Редактирование документа"
            hide-footer
            centered
        >
            <div>
                <form @submit.prevent="updateDocument()">
                    <div class="form-group">
                        <label>Название документа</label>
                        <input type="text" class="form-control" v-model="editDocumentItem.name">
                    </div>
                    <div class="form-group">
                        <label>Описание документа</label>
                        <input type="text" class="form-control" v-model="editDocumentItem.description">
                    </div>
                    <div class="form-group">
                        <label>Дата составления документа (при необходимости)</label>
                        <input
                            type="date"
                            class="form-control"
                            v-model="editDocumentItem.date_of_document"
                        >
                    </div>
                    <div class="form-group">
                        <label for="reference_document_type_id">Тип документа</label>
                        <select-of-properties
                            :reference_id="34"
                            :needNullElement="false"
                            :needMultipleSelect="false"
                            :defaultSelectName="`Выберите тип документа`"
                            :internalSearch="true"
                            :excludeArray="excludeAddDocumentTypeArray"
                            :selected="editDocumentItem.reference_document_type_id"
                            @set="(value) => {editDocumentItem.reference_document_type_id = value}"
                        ></select-of-properties>
                    </div>
                    <button class="btn btn-primary float-right">Сохранить</button>
                </form>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import {HOST_FOR_STORAGE, API_DOCUMENTS} from '../../constants'
    import {
        defaultDataItems,
    } from '../../mixins'

    const initialEditDocumentItem = () => ({
        id: '',
        path: '',
        name: '',
        description: '',
        creator_user_id: '',
        order_id: '',
        reference_document_type_id: '',
        date_of_document: '',
        deleted_at: '',
        created_at: '',
        updated_at: '',
    })

    export default {
    mixins: [
        defaultDataItems
    ],
        props: {
            orderId: { type: Number | String, required: false, default: '' },           //order id for showing documents
            creatorUserId: { type: Number | String, required: false, default: '' },     //creator user id for showing documents
            referenceDocumentTypeIds: { type: Array,
                required: false,
                default: function () {
                    return []
                }
            },                                                                           //array of document type id for searching
            requestDocuments: { type: Boolean, default: false, required: false },       //activation of the component work process
            updateDocumentList: { type: Number, default: 1, required: false },       //activation of the component work process
            excludeAddDocumentTypeArray: {  type: Array,
                required: false,
                default: function () {
                    return []
                }
            },                                                                          //if you need to exclude document type for adding
            noActions: { type: Boolean, default: false, required: false },              //if we whant to block actions on document card
        },
        data() {
            return {
                items: [],
                editDocumentItem: '',
                editDocumentIndex: -1,
                dataOverlay: true,
                hfs: HOST_FOR_STORAGE
            }
        },
        watch: {
            requestDocuments: function(val){
                if (val == true) {
                    this.showDocuments()
                }
            },
            updateDocumentList: {
                handler(newVal, oldVal) {
                    if (newVal != oldVal && this.requestDocuments) {
                        this.showDocuments()
                    }
                }
            }
        },
        mounted() {
            this.$root.$on('updateViewDocument', (payload) => {
                this.items.push(payload)
            })
            this.editDocumentItem = initialEditDocumentItem()

            if (this.requestDocuments) {
                this.showDocuments()
            }
        },
        methods: {
            //list uploaded documents
            showDocuments() {
                let additionalGetParameter = ''

                additionalGetParameter += (this.orderId ? `&order_id=${this.orderId}`: ``)
                additionalGetParameter += (this.creatorUserId ? `&creator_user_id=${this.creatorUserId}`: ``)
                additionalGetParameter += (this.referenceDocumentTypeIds ? `&reference_document_type_ids=${this.referenceDocumentTypeIds}`: ``)

                api.call("get", `${API_DOCUMENTS}?${additionalGetParameter}`)
                .then(({data}) => {
                    this.items = data.data
                })
                .finally(() => {
                    this.dataOverlay = false
                })
            },
            //edit document
            editDocument(index) {
                this.$refs['editDocument'].show() //open modal

                this.editDocumentIndex = index
                this.editDocumentItem = this.items[index]
            },
            //update document
            updateDocument() {
                let id = this.editDocumentItem.id
                this.$refs['editDocument'].hide() //close modal

                this.dataOverlay = true

                const updateDocumentFields = {
                    'name': this.editDocumentItem.name,
                    'description': this.editDocumentItem.description,
                    'reference_document_type_id': this.editDocumentItem.reference_document_type_id,
                    'date_of_document': this.editDocumentItem.date_of_document
                }

                api.call("put", `${API_DOCUMENTS}/${id}`, updateDocumentFields)
                .then(({data}) => {
                    this.items[this.editDocumentIndex] = Object.assign(this.items[this.editDocumentIndex], data)
                })
                .finally(() => {
                    this.dataOverlay = false
                    this.editDocumentItem = initialEditDocumentItem()
                    this.editDocumentIndex = -1
                    this.makeToast('success')
                })
            },
            //delete document
            deleteDocument(index) {
                let id = this.items[index].id

                this.$swal({
                    title: 'Подтвердите удаление',
                    icon: 'question',
                    showCancelButton: true,
                    cancelButtonText: 'Нет',
                    confirmButtonText: 'Да',
                }).then((result) => {
                    this.dataOverlay = true
                    if(result.isConfirmed){
                        api.call("delete", `${API_DOCUMENTS}/${id}`)
                        .then(({data}) => {
                            this.items.splice(index, 1)
                        })
                        .finally(() => {
                            this.dataOverlay = false
                            this.makeToast('danger')
                        })
                    }
                }).finally(() => {
                    this.dataOverlay = false
                })
            }
        },
    }
</script>

