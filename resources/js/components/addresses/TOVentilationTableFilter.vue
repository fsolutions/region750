<template>
    <div class="float-left green-filter ml-3">
        <div class="btn-group"
        >
            <span
                :class="`green-dropdown mr-0 active`"
            >{{selectedFilterName}}</span>
            <span
                :class="`green-arrow-dropdown dropdown-toggle dropdown-toggle-split active`"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <span><i class="fas fa-caret-down"></i></span>
            </span>
            <div class="dropdown-menu">
                <b-btn
                    variant="link"
                    v-for="(item, index) in greenFilterMenu" 
                    :key="index"
                    :class="item.active ? 'dropdown-item active' : 'dropdown-item'"
                    @click="changeActive(index)"
                >{{item.title}}</b-btn>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            api: {type: String, required: true},              // Link on API for table working
        },
        data() {
            return {
                greenFilterMenu: [
                    {title: "Все ТО", active: true},
                    {title: "Запланировано", active: false, status: "Запланировано"},
                    {title: "Проведено", active: false, status: "Проведено"},
                    {title: "Отменено", active: false, status: "Отменено"},
                    {title: "Перенесено", active: false, status: "Перенесено"},
                ],
                selectedFilterName: 'Фильтр'
            }
        },
        mounted() {
            this.changeActive(this.$store.state.userLocalSettings.filterSettings.TOVentilation)
        },
        methods: {
            changeActive(activeIndex) {
                let additionalParameter = ""
                this.$store.dispatch("SET_FILTER_SETTINGS", {
                    name: 'TOVentilation',
                    value: activeIndex
                });
                this.greenFilterMenu.forEach((item, index) => {
                    item.active = false
                    if (activeIndex == index) {
                        item.active = true
                        this.selectedFilterName = item.title
                        if (item.status) {
                            additionalParameter = '&ventilation_status=' + item.status
                        }
                        this.$emit('setFilteringApiParameter', additionalParameter)
                    }
                })
            }
        }
    }
</script>
