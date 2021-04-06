<template>
    <!--  WHEN WILL BE OTHER TYPES - REMAKE THIS CONTEXT MENU COMPONENT!!!  -->
    <div class="dropdown-menu dropdown-menu-sm" id="table-context-menu">
        <span class="dropdown-item-text px-3 text-muted">Выберите колонки:</span>
        <div class="px-3 pb-2">
            <div
                class="form-check"
                v-for="(menuItem, index) in menuItems"
                :key="index"
            >
                <input
                    class="form-check-input"
                    type="checkbox"
                    :id="`${menuName}_checkbox_${index}`"
                    :disabled="menuItems.length == 1 && !menuItem.visible"
                    v-model="menuItem.visible"
                >
                <label class="form-check-label" :for="`${menuName}_checkbox_${index}`">
                    {{ menuItem.label }}
                </label>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        props: {
            headerContextMenuName: {type: String, required: true}, // Menu name for localStorage identify
            menuName: {type: String, required: true},              // Menu name for identification of menu if them will be 2,3,4 or more
            event: {type: MouseEvent, required: false},            // Menu open event
            menuItems: {type: Array, required: true},              // Menu items array for displaying
            openedTableHeaderMenu: {type: Boolean, required: false}, // If menu opened - open it hear
            restoreHeader: {type: Boolean, required: false, default: false}, // If need to restore menu after api request
        },
        data() {
            return {
            }
        },
        computed: {
            tableHeaderSettings () {
                return this.$store.state.userLocalSettings.tableHeaderSettings
            }
        },
        watch: {
            event: {
                deep: true,
                handler(event) {
                    this.openMenu(event)
                }
            },
            menuItems: {
                handler(headers) {
                    this.setNewHeaders(headers)
                },
                deep: true,
            },
        },
        mounted() {
            this.setNewHeaders(this.menuItems, true)
        },
        methods: {
            openMenu(e) {
                e.preventDefault()
                let top = e.pageY - 150;
                let left = e.pageX - 430;
                let contextMenu = document.getElementById(this.menuName)

                contextMenu.style.top = top + 'px'
                contextMenu.style.left = left + 'px'

                contextMenu.classList.add("show")

                document.addEventListener('click', (event) => {
                    let isClickInside = contextMenu.contains(event.target);
                    if (isClickInside) {
                    }
                    else {
                        contextMenu.classList.remove("show")
                        contextMenu.style.top = '-10000px'
                        contextMenu.style.left = '-10000px'
                        this.$emit('close')
                    }
                })
            },
            setNewHeaders(headers, restore = false) {
                if ((!restore && !this.restoreHeader) || Object.keys(this.tableHeaderSettings[this.headerContextMenuName]).length == 0) {
                    this.$store.dispatch("SET_TABLE_HEADER_SETTINGS", {
                        name: this.headerContextMenuName,
                        value: this.headersFilterPrepare(headers)
                    })
                }

                let savedHeaderFilter = this.tableHeaderSettings[this.headerContextMenuName]
                this.menuItems.forEach((headerItem, index) => {
                    if (savedHeaderFilter[headerItem.key]) {
                        headerItem.visible = savedHeaderFilter[headerItem.key].visible
                    }
                })

                this.$emit('check', this.menuItems)
            },
            headersFilterPrepare(headers) {
                let result = {}

                headers.forEach((headerItem) => {
                    result[headerItem.key] = {
                        'visible': headerItem.visible
                    }
                })

                return result
            }
        }
    }
</script>
<style>
</style>
