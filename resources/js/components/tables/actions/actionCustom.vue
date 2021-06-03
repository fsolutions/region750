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
            <b-button 
                class="button-group-fill-delete"
                v-if="checkActionAllow('reject') && row.item.order_status && row.item.order_status == 'В обработке'"
                @click="rejectItem(row.index)"
            >
                Отменить
            </b-button>
        </b-button-group>
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
            rejectItem(index) {
                this.$emit('universalEmit', 'reject', index)
            },
        }
    }
</script>

