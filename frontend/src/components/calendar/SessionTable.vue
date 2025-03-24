<script setup>
import UserList from '../admin/UserList.vue';
import Legend from './Legend.vue'
import WeekNavigation from './WeekNavigation.vue'
import SessionActions from './SessionActions.vue'

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
                        @updated="fetchWeekBookings"/>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    mounted() {
        this.fetchWeekBookings();
        this.__startTableHighlighting();
    },
    data() {
        return {
            __user_list: {},
            __user_list_fetched: false,
            bookedTimestamps: {},
            __weekStartTimestamp: null,
            bookedTimestamps: {},
            calendar: {
                show: false,
                shownMonth: (new Date()).getMonth(),
                shownYear: (new Date()).getFullYear(),
                selectedWeek: this.selectedWeekFromDate((new Date()).getDate()),
                selectedMonth: (new Date()).getMonth(),
                selectedYear: (new Date()).getFullYear(),
                mobileSelectedDay: 0,
            },
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

                    const date = new Date(this.__weekStartTimestamp * 1000);
                    this.__weekStartTimestamp = date.valueOf() / 1000;

                    this.calendar.selectedMonth = this.calendar.shownMonth = date.getMonth();
                    this.calendar.selectedYear = this.calendar.shownYear = date.getFullYear();
                    this.calendar.selectedWeek = this.selectedWeekFromDate(date.getDate());

                    return this.__weekStartTimestamp;
                }
                else return this.__weekStartTimestamp;
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
    }
}
</script>
