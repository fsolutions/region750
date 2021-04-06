<template>
    <div v-if="validationErrors">
        <ul class="alert alert-danger">
            <li v-for="(value, key, index) in validationErrors">{{ value }}</li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: {
            errors: { type: Object, required: false },
        },
        data() {
            return {
            }
        },
        computed: {
            validationErrors(){
                let errors = Object.values(this.errors)
                errors = errors.flat()
                this.$emit('stopLoaders')
                return errors
            }
        }
    }
</script>
