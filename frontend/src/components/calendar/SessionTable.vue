<script setup>
import UserList from '../admin/UserList.vue';
import Legend from './Legend.vue'
import WeekNavigation from './WeekNavigation.vue'
import SessionActions from './SessionActions.vue'
import SessionDetailsModal from '../modals/SessionDetailsModal.vue'
import NewSessionModal from '../modals/NewSessionModal.vue'

defineProps({
  lang: {
    type: Object,
    required: true,
  },
  configs: {
    type: Object,
    required: true,
  },
  user: {
    type: Object,
    required: true,
  },
  adminView: {
    type: Boolean,
    default: false,
  }
});
</script>

<template>
    <table class="table table-hover session-overview" cellspacing="0">
        <thead style="position: sticky; top: 0">
            <tr style="background-color: white;">
                <td></td>
                <th scope="col"
                    v-for="(day, dayIndex) in configs.daysInAWeek"
                    :col-id="dayIndex">{{ weekDayNameFromTimestamp(weekStartTimestamp + 60*60*24*dayIndex) }} - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*dayIndex) }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="rowTimestamp in rowsTimestampsList">
                <th scope="row">{{ timeFromRowTimestamp(rowTimestamp) }}</th>

                <td v-for="(_, addDay) in configs.daysInAWeek"
                    :col-id="addDay"
                    :class="getEventMarkingClass(weekStartTimestamp + rowTimestamp + addDay*24*60*60)">
                    <SessionActions
                        :lang="lang"
                        :configs="configs"
                        :user="user"
                        :admin-view="adminView"
                        :booking="getBookedTime(weekStartTimestamp, rowTimestamp, addDay)"
                        :timestamp="weekStartTimestamp + rowTimestamp + addDay*24*60*60"
                        :user-list="userList"
                        @show-booking-details="(booking) => { sessionDetails = booking; sessionDetailsModal.show(); }"
                        @show-create-booking="(timestamp) => { newSessionTimestamp = timestamp; newSessionModal.show() }"
                        @updated="fetchWeekBookings"/>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- mobile table -->
    <div class="session-overview-mobile">
        <div class="day" v-for="(_, addDay) in configs.daysInAWeek" :class="{ active: addDay == mobileSelectedDay }">
            <div class="row heading">
                <div class="time"></div>
                <div class="booking">
                    <button @click="previousDay()">&lt;</button>
                    <span>{{ weekDayNameFromTimestamp(weekStartTimestamp + 60*60*24*addDay) }} - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*addDay) }}</span>
                    <button @click="nextDay()">&gt;</button>
                </div>
            </div>
            <div v-for="rowTimestamp in rowsTimestampsList" class="row">
                <div class="time">{{ timeFromRowTimestamp(rowTimestamp) }}</div>
                <div class="booking" :class="getEventMarkingClass(weekStartTimestamp + rowTimestamp + addDay*24*60*60)">
                    <SessionActions
                    :lang="lang"
                    :configs="configs"
                    :user="user"
                    :admin-view="adminView"
                    :booking="getBookedTime(weekStartTimestamp, rowTimestamp, addDay)"
                    :timestamp="weekStartTimestamp + rowTimestamp + addDay*24*60*60"
                    :user-list="userList"
                    @show-booking-details="(booking) => { sessionDetails = booking; sessionDetailsModal.show(); }"
                    @show-create-booking="(timestamp) => { newSessionTimestamp = timestamp; newSessionModal.show() }"
                    @updated="fetchWeekBookings"/>
                </div>
            </div>
        </div>
    </div>

    <SessionDetailsModal
        :lang="lang"
        :configs="configs"
        :user-list="userList"
        :session-details="sessionDetails"
        @deleted="fetchWeekBookings"/>

    <NewSessionModal
        :lang="lang"
        :configs="configs"
        :user="user"
        :timestamp="newSessionTimestamp"
        @created="fetchWeekBookings"/>
</template>

<script>
export default {
    mounted() {
        this.fetchWeekBookings();
        this.__startTableHighlighting();

        this.sessionDetailsModal = new bootstrap.Modal(document.getElementById("sessionDetailsModal"));
        this.newSessionModal = new bootstrap.Modal(document.getElementById("newSessionModal"));
    },
    data() {
        return {
            __user_list: {},
            __user_list_fetched: false,
            bookedTimestamps: {},
            __weekStartTimestamp: null,
            bookedTimestamps: {},
            sessionDetails: null,
            sessionDetailsModal: null,
            newSessionModal: null,
            newSessionTimestamp: 0,
            mobileSelectedDay: 0,
            eventMarkingList: document.php.eventMarkingList,
        };
    },
    computed: {
        userList() {
            const users = {};

            if (!this.$props.adminView) {
                users[this.$props.user.id] = this.$props.user;
            }
            else {
                if (this.$props.user.isAdmin && !this.__user_list_fetched) {
                    this.__user_list_fetched = true;

                    axios.get(this.$props.configs.baseURL + "users")
                    .then((response) => {
                        this.userList = {};
                        response.data.forEach(user => {
                            this.userList[user.id] = user;
                        });
                    });
                }
                return this.__user_list;
            }

            return users;
        },
        rowsTimestampsList() {
            var response = [];
            for (let rowTimestamp = this.$props.configs.sessionOffset; rowTimestamp < 24*60*60; rowTimestamp+=this.$props.configs.sessionInterval) {
                response.push(rowTimestamp);
            }

            return response;
        },
        weekStartTimestamp: {
            get() {
                if (null == this.__weekStartTimestamp) {
                    this.__weekStartTimestamp = this.$props.configs.weekStartTimestamp
                    if ('now' == this.$props.configs.weekStartTimestamp) {
                        this.__weekStartTimestamp = this.getWeekStartTimestamp(new Date());
                    }
                }
                
                return this.__weekStartTimestamp;
            },
            set(value) {
                this.__weekStartTimestamp = value;
            },
        },
        weekEndTimestamp() {
            return this.weekStartTimestamp + this.$props.configs.daysInAWeek*24*60*60 + 23*60*60;
        },
    },
    methods: {
        __startTableHighlighting() {
            document.querySelectorAll('.session-overview').forEach((table) => {
                table.querySelectorAll('tbody > tr > td').forEach((td) => {
                    td.addEventListener('mouseover', (e) => {
                        const colId = e.currentTarget.getAttribute('col-id');
                        const header = table.querySelector('thead > tr > th[col-id="' + colId + '"]');
                        header.classList.add('highlight');
                    });
                    td.addEventListener('mouseout', (e) => {
                        table.querySelectorAll('thead > tr > th').forEach(th => {
                            th.classList.remove('highlight');
                        });
                    });
                });
            });
        },
        fetchWeekBookings() {
            axios.get(this.$props.configs.baseURL + "sessions/bookings/" + this.weekStartTimestamp + "/" + this.weekEndTimestamp)
            .then((response) => {
                this.sessionDetailsModal.hide();
                this.newSessionModal.hide();
                this.bookedTimestamps = {};

                for (var sessionBooking of response.data) {
                    if (typeof sessionBooking.id == "undefined") sessionBooking.id = false;
                    if (typeof sessionBooking.user_id == "undefined") sessionBooking.user_id = false;
                    if (typeof sessionBooking.title == "undefined") sessionBooking.title = '';
                    if (typeof sessionBooking.description == "undefined") sessionBooking.description = '';

                    this.bookedTimestamps[sessionBooking.start_time] = sessionBooking;
                }
            });
        },
        getWeekStartTimestamp(date=null) {
            var d = date ? date : new Date();
            var day = d.getDay();
            var diff = d.getDate() - day + (day == 0 ? -6 : 1);
            const newTimestamp = (new Date(d.setDate(diff))).setHours(0, 0, 0, 0)
            return Math.floor(newTimestamp / 1000);
        },
        selectedWeekFromDate(day) {
            const firstWeekday = (new Date()).getDay();
            for (let i=0; i<6; i++) {
                if (day <= 7-firstWeekday + i*7) return i+1;
            }
        },
        weekDayNameFromTimestamp(timestamp) {
            const date = new Date(timestamp * 1000);
            var weekday = date.getDay() == 0 ? 7 : date.getDay();
            weekday--;
            return this.lang.dayNames[weekday];
        },
        dayMonthFromTimestamp(timestamp) {
            const date = new Date(timestamp * 1000);
            return String(date.getDate()).padStart(2, '0') + '.' + String(date.getMonth()+1).padStart(2, '0') + '.';
        },
        timeFromRowTimestamp(rowTimestamp) {
            let date = new Date(0);
            date.setHours(0);
            date.setSeconds(rowTimestamp);
            let hours = date.getHours().toString();
            if (hours.length == 1) hours = "0" + hours;
            let minutes = date.getMinutes().toString();
            if (minutes.length == 1) minutes = "0" + minutes;

            return hours + ":" + minutes;
        },
        getEventMarkingClass(timestamp) {
            var classList = [];
            this.eventMarkingList.forEach(marking => {
                if (marking.startTimestamp <= timestamp && timestamp < marking.endTimestamp) {
                    classList.push(marking.cssClasses);
                }
            });

            return classList.join(' ');
        },
        getBookedTime(weekStartTimestamp, rowTimestamp, day) {
            const startTime = weekStartTimestamp + rowTimestamp + day*24*60*60;
            if (!(startTime in this.bookedTimestamps)) {
                return null;
            }
            else {
                return this.bookedTimestamps[startTime];
            }
        },
        previousDay() {
            if (0 == this.mobileSelectedDay) {
                this.mobileSelectedDay = this.$props.configs.daysInAWeek - 1;
                this.weekStartTimestamp -= 60*60*24*this.$props.configs.daysInAWeek;
            }
            else {
                this.mobileSelectedDay--;
            }
        },
        nextDay() {
            if (this.$props.configs.daysInAWeek - 1 == this.mobileSelectedDay) {
                this.mobileSelectedDay = 0;
                this.weekStartTimestamp += 60*60*24*this.$props.configs.daysInAWeek;
            }
            else {
                this.mobileSelectedDay++;
            }
        },
    }
}
</script>
