<template>
	<div>
        <div class="field">
            <div class="form-group row justify-content-between">
                <div class="col-sm-6">
                    <!-- <label for="needs" class="col-form-label">Показывать в календаре:</label> -->
                    <b-form-checkbox
                        id="needTO"
                        v-model="needTO"
                        name="needTO"
                    >
                        ТО-ВКГО
                    </b-form-checkbox>                    
                    <b-form-checkbox
                        id="needPRE"
                        v-model="needPRE"
                        name="needPRE"
                    >
                        Предписания
                    </b-form-checkbox>                    
                </div>

                <div class="col-sm-6 text-right" @click="openedDay=false">
                    <label for="displayPeriod" class="form-label">Период отображения:</label>
                    <b-form-select 
                    style="max-width: 150px;"
                    class="mr-3"
                    size="sm"
                    v-model="displayPeriodUom" 
                    :options="[
                        {value: 'month', text: 'Месяц'},
                        {value: 'week', text: 'Неделя'},
                    ]"></b-form-select>
                </div>
            </div>
        </div>

    	<div id="full-calendar">
            <!-- <div class="calendar-controls">
                <div v-if="message" class="notification is-success">{{ message }}</div>

                <div class="box">
                    <h4 class="title is-5"></h4>

                    <div class="field">
                        <label class="label">Период</label>
                        <div class="control">
                            <div class="select">
                                <select v-model="displayPeriodUom">
                                    <option>Месяц</option>
                                    <option>Неделя</option>
                                    <option>Год</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="checkbox">
                            <input v-model="showTimes" type="checkbox" />
                            Show times
                        </label>
                    </div>

                </div>

                <div class="box">
                    <div class="field">
                        <label class="label">Title</label>
                        <div class="control">
                            <input v-model="newItemTitle" class="input" type="text" />
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Start date</label>
                        <div class="control">
                            <input v-model="newItemStartDate" class="input" type="date" />
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">End date</label>
                        <div class="control">
                            <input v-model="newItemEndDate" class="input" type="date" />
                        </div>
                    </div>

                    <button class="button is-info" @click="clickTestAddItem">
                        Add Item
                    </button>
                </div>
            </div> -->
            <div class="calendar-parent mb-4">
                <calendar-view
                    :items="items.filter(item => item.showable)"
                    :show-date="showDate"
                    :time-format-options="{ hour: 'numeric', minute: '2-digit' }"
                    :enable-drag-drop="enableDragDrop"
                    :disable-past="disablePast"
                    :disable-future="disableFuture"
                    :show-times="showTimes"
                    :display-period-uom="displayPeriodUom"
                    :display-period-count="displayPeriodCount"
                    :starting-day-of-week="1"
                    :class="themeClasses"
                    :period-changed-callback="periodChanged"
                    :current-period-label="''"
                    :displayWeekNumbers="true"
                    :enable-date-selection="true"
                    :selection-start="selectionStart"
                    :selection-end="selectionEnd"
                    @date-selection-start="setSelection"
                    @date-selection="setSelection"
                    @date-selection-finish="finishSelection"
                    @drop-on-date="onDrop"
                    @click-date="onClickDay"
                    @click-item="onClickItem"
                >
                    <calendar-view-header
                        slot="header"
                        slot-scope="{ headerProps }"
                        :header-props="headerProps"
                        @input="setShowDate"
                    />
                </calendar-view>
            </div>
            <div class="calendar-controls" v-if="openedDay">
                <h3 class="text-center">{{ selectedDay }}</h3>
                <hr>
                <div v-for="(item, index) in selectedItems.filter(item => item.showable)" :key="index">
                    <template v-if="item.model == 'Prescription'">
                        <div class="smallTextInDay"><em>{{item.startDate | formattedDate}} - {{item.title}}</em></div>
                        <div class="mt-1">Клиент: {{item.to_contract_for_user.name}}: <a :href="`tel:+${item.to_contract_for_user.phone}`">{{item.to_contract_for_user.phone | VMask('+#(###)###-##-##')}}</a></div>
                        <div class="mt-1 mb-5">
                            <b-button size="sm" @click="showItem(item.id, 'Prescription')" class="float-right mr-1">Открыть</b-button>
                        </div>
                    </template>
                    <template v-else>
                        <div class="smallTextInDay"><em>{{item.startDate | formattedDateTime}} - {{item.title}}</em></div>
                        <div class="mt-1">Клиент: {{item.to_contract_for_user.name}}: <a :href="`tel:+${item.to_contract_for_user.phone}`">{{item.to_contract_for_user.phone | VMask('+#(###)###-##-##')}}</a></div>
                        <div class="mt-1 mb-5">
                            <b-button size="sm" @click="showItem(item.id, 'ContractTO')" class="float-right mr-1">Открыть</b-button>
                        </div>
                    </template>
                    <hr>
                </div>
                <div 
                    class="text-center"
                    v-if="selectedItems.filter(item => item.showable).length == 0"
                >
                    Список событий пуст...
                </div>
            </div>
        </div>        
        <b-overlay
            :show="isCalendarBusy"
            spinner-variant="success"
            no-wrap
        ></b-overlay>
        <b-sidebar
            v-model="isSidebarOpenDetail"
            id="sidebar-right"
            title="Детальная информация"
            right
            backdrop
            shadow
            width="65em"
            backdrop-variant="dark"
            ref="showItem"
        >
            <div class="d-block">
                <template v-if="selectedApiVariant == 'ContractTO'">
                    <contract-to-details
                        :detailedItem="detailedItem"
                    ></contract-to-details>
                </template>
                <template v-if="selectedApiVariant == 'Prescription'">
                    <prescription-details
                        :detailedItem="detailedItem"
                    ></prescription-details>
                </template>
            </div>
        </b-sidebar>        
	</div>
</template>
<script>
    import { API_CALENDAR, API_CONTRACTS_TO, API_PRESCRIPTIONS } from "../constants"
    import ContractTODetails from "../components/contracts/ContractTODetails"
    import PrescriptionDetails from "../components/prescriptions/PrescriptionDetails"

    require("../../../node_modules/vue-simple-calendar/static/css/default.css")
    require("../../../node_modules/vue-simple-calendar/static/css/holidays-us.css")
    import {
        CalendarView,
        CalendarViewHeader,
        CalendarMathMixin,
    } from "vue-simple-calendar" // published version

    const initialEditedItem = () => ({
        id: '',
        type: {},
        contract_on_user: {},
        source: {},
        company: {},
        company_employee: {},
        client: {},
        status: {},
        order: {},
        user: {},
        order_user: {},
        master: {},
        order_contract: {},
        order_prescription: {},
        prescription_contract: {},
        prescription_order: {},
        creator: {},
        contract_on_user: {},
        contract_to: {},
        contract_to_last: {},
        orders: {},
        prescriptions: {},
        to_contract: {},
        to_contract_for_user: {}     
    })    

    export default {
        name: "App",
        components: {
            CalendarView,
            CalendarViewHeader,
            "contract-to-details": ContractTODetails,
            "prescription-details": PrescriptionDetails
        },
        mixins: [CalendarMathMixin],
        data() {
            return {
                api: API_CALENDAR,
                additionalGetParameter: '',
                filter: '',
                isCalendarBusy: true,
                needTO: true,
                needPRE: true,
                openedDay: false,
                selectedDay: '',
                selectedItems: [],
                showDate: this.thisMonth(1),
                message: "",
                enableDragDrop: false,
                disablePast: false,
                disableFuture: false,
                displayPeriodUom: "month",
                displayPeriodCount: 1,
                showTimes: true,
                selectionStart: null,
                selectionEnd: null,
                newItemTitle: "",
                newItemStartDate: "",
                newItemEndDate: "",
                useDefaultTheme: true,
                useHolidayTheme: false,
                items: [],
                detailedItem: {},
                isSidebarOpenDetail: false,
                detailedItemIndex: -1,
                selectedApiVariant: ''
            }
        },
        computed: {
            userLocale() {
                return this.getDefaultBrowserLocale
            },
            dayNames() {
                return this.getFormattedWeekdayNames(this.userLocale, "long", 0)
            },
            themeClasses() {
                return {
                    "theme-default": this.useDefaultTheme,
                    "holiday-us-traditional": this.useHolidayTheme,
                    "holiday-us-official": this.useHolidayTheme,
                }
            },
        },
        watch: {
            'needTO': {
                handler(need) {
                    this.items.forEach((item, index) => {
                        if (item.model == 'ContractTO') {
                            item.showable = need
                        }
                    })
                },
                immediate: true,
                deep: true,
            },
            'needPRE': {
                handler(need) {
                    this.items.forEach((item, index) => {
                        if (item.model == 'Prescription') {
                            item.showable = need
                        }
                    })
                },
                immediate: true,
                deep: true,
            },
            isSidebarOpenDetail(value) {
                if (!value) {
                    this.detailedItem = initialEditedItem()
                }
            }
        },
        mounted() {
            this.newItemStartDate = this.isoYearMonthDay(this.today())
            this.newItemEndDate = this.isoYearMonthDay(this.today())
            this.detailedItem = initialEditedItem()
        },
        methods: {
            getDataHandler: async function() {
                this.isCalendarBusy = true
                let filter = '';
                if (this.filter) {
                    filter = "&q=" + this.filter
                }
                await api.call('get', this.api + `?${this.additionalGetParameter}${filter}`)
                    .then((response) => {
                        this.isCalendarBusy = false
                        this.items = response.data
                    })
            },
            periodChanged(range) {
                // range, eventSource) {
                // Demo does nothing with this information, just including the method to demonstrate how
                // you can listen for changes to the displayed range and react to them (by loading items, etc.)
                // console.log(eventSource)
                // console.log(range)

                this.additionalGetParameter = `&start_date=${this.isoYearMonthDay(range.displayFirstDate)}&end_date=${this.isoYearMonthDay(range.displayLastDate)}`;

                this.getDataHandler()
            },
            thisMonth(d, h, m) {
                const t = new Date()
                return new Date(t.getFullYear(), t.getMonth(), d, h || 0, m || 0)
            },
            onClickDay(d) {
                this.selectionStart = null
                this.selectionEnd = null

                this.openedDay = true
                this.selectedDay = d.toLocaleDateString()

                let localSelectedDay = this.$moment(d).format('YYYY-MM-DD')
                this.selectedItems = this.items.filter((item => (this.$moment(item.startDate).format('YYYY-MM-DD') == localSelectedDay && item.showable)));
            },
            onClickItem(e) {
                this.showItem(e.originalItem.id, e.originalItem.model)
            },
            setShowDate(d) {
                this.message = `Changing calendar view to ${d.toLocaleDateString()}`
                this.showDate = d
            },
            setSelection(dateRange) {
                this.selectionEnd = dateRange[1]
                this.selectionStart = dateRange[0]
            },
            finishSelection(dateRange) {
                this.setSelection(dateRange)
                this.message = `You selected: ${this.selectionStart.toLocaleDateString()} -${this.selectionEnd.toLocaleDateString()}`
            },
            onDrop(item, date) {
                this.message = `You dropped ${item.id} on ${date.toLocaleDateString()}`
                // Determine the delta between the old start date and the date chosen,
                // and apply that delta to both the start and end date to move the item.
                const eLength = this.dayDiff(item.startDate, date)
                item.originalItem.startDate = this.addDays(item.startDate, eLength)
                item.originalItem.endDate = this.addDays(item.endDate, eLength)
            },
            clickTestAddItem() {
                this.items.push({
                    startDate: this.newItemStartDate,
                    endDate: this.newItemEndDate,
                    title: this.newItemTitle,
                    id: "e" + Math.random().toString(36).substr(2, 10),
                })
                this.message = "You added a calendar item!"
            },
            showItem(id, apiVariant = 'ContractTO') {
                let tableApiUrl = ''

                if (apiVariant == 'ContractTO') {
                    tableApiUrl = API_CONTRACTS_TO
                } else if (apiVariant == 'Prescription') {
                    tableApiUrl = API_PRESCRIPTIONS
                }

                this.selectedApiVariant = ''

                if (tableApiUrl) {
                    this.selectedApiVariant = apiVariant
                    api.call("get", `${tableApiUrl}/${id}`).then((data) => {
                        this.detailedItem = data.data
                        this.isSidebarOpenDetail = true
                    }).finally(() => {
                    })
                }
            },
        },
    }
</script>

<style>
    #full-calendar {
        display: flex;
        flex-wrap: wrap;
        /* flex-direction: row;
        width: 95vw; */
        min-width: 30rem;
        max-width: 100rem;
        min-height: 40rem;
        margin-left: auto;
        margin-right: auto;
    }
    .calendar-controls {
        flex: 1 1 auto;
        margin-left: 1rem;
        min-width: 14rem;
        max-width: 14rem;
        overflow-y: auto;
        max-height: 75vh;
    }
    .calendar-parent {
        display: flex;
        flex: 1 1 auto;
        /* flex-direction: column;
        flex-grow: 1; */
        overflow-x: hidden;
        overflow-y: hidden;
        max-height: 75vh;
        background-color: white;
        margin-right: 1rem;
    }
    /* For long calendars, ensure each week gets sufficient height. The body of the calendar will scroll if needed */
    .cv-wrapper.period-month.periodCount-2 .cv-week,
    .cv-wrapper.period-month.periodCount-3 .cv-week,
    .cv-wrapper.period-year .cv-week {
        min-height: 6rem;
    }
    .cv-week, .cv-weekdays {
        min-height: 20vh;
    }
    .period-week .cv-week, .period-week .cv-weekdays {
        min-height: 75vh;
    }
    
    .theme-default .cv-item {
        white-space: pre-wrap;
    }
    .theme-default .cv-item.primary {
        background-color: #AAAAAA;
        border-color: #AAAAAA;
    }
    .theme-default .cv-item.success {
        background-color: #52ae34;
        border-color: #52ae34;
        color: #FFF;
    }
    .theme-default .cv-item.success .startTime, .theme-default .cv-item .endTime {
        color: #F1f1f1;
    }

    .theme-default .cv-item.warning {
        background-color: #ffed4a;
        border-color: #ffed4a;
    }
    .theme-default .cv-item.danger {
        background-color: #e3342f;
        border-color: #e3342f;
    }
    .smallTextInDay {
        font-size:12px; 
        line-height: 13px;                            
    }

    /* .theme-default .cv-item.birthday::before {
        content: "\1F382";
        margin-right: 0.5em;
    } */
</style>