<template>
    <div>
        <div id="header" class="container-fluid">
            <navigation
                :authenticated="authenticated"
                :user="user"
            ></navigation>
        </div>
        <template v-if="authenticated && user.role">
            <sidebar-navigation 
                :user="user"
            />
        </template>
        <div id="main" :style="handlerMarginStyle">
            <div class="container-fluid p-0">
                <router-view></router-view>
            </div>
        </div>
        <!-- <div id="footer" class="fixed-bottom" v-if="$route.path != '/login'">
            <app-footer v-if="$mq != 'sm'" />
        </div> -->
    </div>
</template>

<script>
    export default {
        name: 'App',
        data() {
            return {
                authenticated: auth.check() || false,
                user: auth.user || {
                                        id: '',
                                        name: '',
                                        email: '',
                                        phone: '',
                                        role: []
                                    },
                class: 'col-md-12',
            };
        },
        mounted() {
            this.$store.dispatch("UPDATE_USER", this.user);
            if(this.authenticated = true) {
                this.getReferencePropertiesList()
                this.getGroupedReferencePropertiesList()
            }
            Event.$on('userLoggedIn', () => {
                this.authenticated = true;
                this.user = auth.user;
                this.$store.dispatch("UPDATE_USER", this.user);
                if(this.authenticated = true)
                    this.class = 'col-md-10'
            });
        },
        methods: {
            getReferencePropertiesList: async function() {
                const response = await this.$store.dispatch('GET_REFERENCE_PROPERTIES')
            },
            getGroupedReferencePropertiesList: async function() {
                const response = await this.$store.dispatch('GET_GROUPED_REFERENCE_PROPERTIES')
            },
            handlerMarginStyle(){
                if(this.$mq === 'sm'){
                    let mobileMargin = 'margin-left: 60px'
                    return mobileMargin
                }
            }
        }
    }
</script>

<style scoped>
#main {
  transition: margin-left .5s;
  padding-top: 85px;
  padding-left: 20px;
  padding-right: 20px;
}
#footer {
  transition: margin-left .5s;
}
</style>
