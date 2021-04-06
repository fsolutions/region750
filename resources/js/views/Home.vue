<template>
    <div>
        Home
    </div>
</template>

<script>
    export default {
        data() {
            return {
                authenticated: auth.check(),
            };
        },
        mounted() {
            if (this.authenticated) {
                this.$router.push('/orders');
            } else {
                this.$router.push('/login');
            }
        }
    }
</script>
