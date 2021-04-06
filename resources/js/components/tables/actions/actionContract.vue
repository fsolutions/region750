<template>
    <div>
        <b-button-group size="sm" class="button-group-fill">
            <b-button 
                class="button-group-fill-info"
                v-if="checkActionAllow('show')"
                @click="showItem(row.index)"
            >
                <i class="far fa-eye"></i>
            </b-button>
            <b-button 
                class="button-group-fill-edit"
                v-if="checkActionAllow('edit')"
                @click="createOrEditItemModal(row.index)"
            >
                <i class="fas fa-pencil-alt"></i>
            </b-button>
            <b-button 
                class="button-group-fill-delete"
                v-if="checkActionAllow('delete')"
                @click="deleteItem(row.index)"
            >
                <i class="fas fa-trash text-danger"></i>
            </b-button>
        </b-button-group>
        <div 
            v-if="checkActionAllow('add-contract-to')
                && row.item.status != 'Договор расторгнут'"        
            class="mt-1"
        >
            <span
                class="table_create_to"
                @click="openContractTOCreateModal(row.index)"
            >
                <span class="table_create_to-title">Назначить ТО</span>
            </span>
        </div>
    </div>
</template>
<script>
    import {
        checkActionAllow
    } from '../../../mixins'

    export default {
        props: {
            row: {type: Object, required: true},                                                // Row of table
            actionAllows: {type: Array, required: false, default: function () { return [] }},   // Array of rules for action allows
        },
        mixins: [
            checkActionAllow
        ],
        data() {
            return {
            }
        },
        methods: {
            showItem(index) {
                this.$emit('universalEmit', 'show', index)
            },
            createOrEditItemModal(index) {
                this.$emit('universalEmit', 'edit', index)
            },
            deleteItem(index) {
                this.$emit('universalEmit', 'delete', index)
            },
            openContractTOCreateModal(index) {
                this.$emit('universalEmit', 'openContractTOCreateModal', index)
            },
        }
    }
</script>

