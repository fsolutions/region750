<template>
    <div class="mb-2">
        <b-card-group deck>
            <b-card
                border-variant="success"
                header="Обращения"
                header-bg-variant="success"
                header-text-variant="white"
                align="center"
            >
                <b-card-text>
                    <div class="row">
                        <div class="col-6">
                            <h5>5</h5>
                            <small>Обращений</small>
                        </div>
                        <div class="col-6">
                            <h5></h5>
                            <small><router-link to="/orders">Перейти &raquo;</router-link></small>
                        </div>
                    </div>
                </b-card-text>
            </b-card>

            <b-card
                border-variant="primary"
                header="Финансы"
                header-bg-variant="primary"
                header-text-variant="white"
                align="center"
            >
                <b-card-text>
                    <div class="row">
                        <div class="col-6">
                            <h5>15</h5>
                            <small>Обращений</small>
                        </div>
                        <div class="col-6">
                            <h5>160 000</h5>
                            <small>Рублей</small>
                        </div>
                    </div>
                </b-card-text>
            </b-card>

            <b-card
                border-variant="success"
                header="Запросы"
                header-bg-variant="success"
                header-text-variant="white"
                align="center"
            >
                <b-card-text>
                    <div class="row">
                        <div class="col-6">
                            <h5>50</h5>
                            <small>Обращений</small>
                        </div>
                        <div class="col-6">
                            <h5>1 600 000</h5>
                            <small>Рублей</small>
                        </div>
                    </div>
                </b-card-text>
            </b-card>
        </b-card-group>
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
