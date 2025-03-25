<script setup>
defineEmits(['previousWeek', 'nextWeek', 'setWeekStartTimestamp']);

defineProps({
  lang: {
    type: Object,
    required: true,
  },
  configs: {
    type: Object,
    required: true,
  },
  weekStartTimestamp: {
    type: Number,
    required: true,
  },
  weekEndTimestamp: {
    type: Number,
    required: true,
  }
});
</script>

<template>
    <nav aria-label="Week navigation" class="calendar-wrapper">
        <ul class="pagination pagination-lg justify-content-center">
            <li class="page-item">
                <a class="page-link" @click="$emit('previousWeek')" aria-label="Previous">
                    <span>&laquo;</span>
                </a>
            </li>
            <li class="page-item"><a class="page-link" @click="calendar.show = !calendar.show">{{ weekRange() }}</a></li>
            <li class="page-item">
                <a class="page-link" @click="$emit('nextWeek')" aria-label="Next">
                    <span>&raquo;</span>
                </a>
            </li>
        </ul>


        <div v-if="calendar.show" class="calendar">
            <nav class="calendar-navigation">
                <button class="page-link" @click="calendar.shownYear--">&laquo;</button>
                <button class="page-link" @click="calendarSetPreviousMonth()">&lt;</button>
                <button class="page-link today" @click="calendarSetToday()">
                    <svg viewBox="0 0 16 16" width="20px" class="hover-hide"><circle fill="#9d9d9d" cx="8" cy="8" r="8" /></svg>
                    <svg viewBox="0 0 16 16" width="20px" class="hover-show"><circle fill="white" cx="8" cy="8" r="8" /></svg>
                </button>
                <button class="page-link" @click="calendarSetNextMonth()">&gt;</button>
                <button class="page-link" @click="calendar.shownYear++">&raquo;</button>
            </nav>
            <div class="calendar-body">
                <div class="month-header">{{ lang.monthNames[calendar.shownMonth] }} {{ calendar.shownYear }}</div>
                <div class="month-body">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ lang['Views.weekdays.short.mon'] }}</th>
                                <th>{{ lang['Views.weekdays.short.tue'] }}</th>
                                <th>{{ lang['Views.weekdays.short.wed'] }}</th>
                                <th>{{ lang['Views.weekdays.short.thu'] }}</th>
                                <th>{{ lang['Views.weekdays.short.fri'] }}</th>
                                <th>{{ lang['Views.weekdays.short.sat'] }}</th>
                                <th>{{ lang['Views.weekdays.short.sun'] }}</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="row in 6"
                                :class="{ selected: isRowSelected(calendarDayMatrix[row], row) }">
                                <td v-for="col in 7">
                                    <span class="day"
                                        :class="{ 'previous-month': isDayOfPreviousMonth(calendarDayMatrix[row][col], row), 'next-month': isDayOfNextMonth(calendarDayMatrix[row][col], row) }"
                                        @click="selectWeek(calendarDayMatrix[row][col], row)">
                                        {{ calendarDayMatrix[row][col] }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
export default {
    data() {
        return {
            calendar: {
                show: false,
                shownMonth: (new Date(this.$props.weekStartTimestamp * 1000)).getMonth(),
                shownYear: (new Date(this.$props.weekStartTimestamp * 1000)).getFullYear(),
                selectedWeek: this.selectedWeekFromDate((new Date(this.$props.weekStartTimestamp * 1000)).getDate()),
                selectedMonth: (new Date(this.$props.weekStartTimestamp * 1000)).getMonth(),
                selectedYear: (new Date(this.$props.weekStartTimestamp * 1000)).getFullYear(),
                mobileSelectedDay: 0,
            },
        };
    },
    computed: {
        calendarDayMatrix() {
            return this.generateCalendarDayMatrix(this.calendar.shownYear, this.calendar.shownMonth);
        },
    },
    methods: {
        weekRange() {
            return this.dayMonthFromTimestamp(this.$props.weekStartTimestamp) + ' - ' + this.dayMonthFromTimestamp(this.$props.weekEndTimestamp);
        },
        dayMonthFromTimestamp(timestamp) {
            const date = new Date(timestamp * 1000);
            return String(date.getDate()).padStart(2, '0') + '.' + String(date.getMonth()+1).padStart(2, '0') + '.';
        },
        calendarSetPreviousMonth() {
            if (0 == this.calendar.shownMonth) {
                this.calendar.shownYear--;
                this.calendar.shownMonth = 11;
            }
            else {
                this.calendar.shownMonth--;
            }
        },
        calendarSetNextMonth() {
            if (11 == this.calendar.shownMonth) {
                this.calendar.shownYear++;
                this.calendar.shownMonth = 0;
            }
            else {
                this.calendar.shownMonth++;
            }
        },
        calendarSetToday() {
            const weekStartTimestamp = 'now' == this.$props.configs.weekStartTimestamp ? Date.now() / 1000 : this.$props.configs.weekStartTimestamp;

            const today = new Date(weekStartTimestamp * 1000);
            today.setHours(0);today.setMinutes(0);today.setSeconds(0);
            this.calendar.selectedMonth = this.calendar.shownMonth = today.getMonth();
            this.calendar.selectedYear = this.calendar.shownYear = today.getFullYear();
            this.calendar.selectedWeek = this.getDayRow(today.getDate(), this.calendar.shownMonth, this.calendar.shownYear);

            this.$emit('setWeekStartTimestamp', weekStartTimestamp);
        },
        getDayRow(day, month, year) {
            const monthMatrix = this.generateCalendarDayMatrix(year, month, false);
            var foundIndex = false;
            monthMatrix.forEach((row, index) => {
                if (row.includes(day)) {
                    foundIndex = index;
                    return false;
                }
            });
            return foundIndex;
        },
        generateCalendarDayMatrix(shownYear, shownMonth, showNonMonthDays=true) {
            const date = new Date(shownYear, shownMonth);
            const firstWeekday = date.getDay() == 0 ? 7 : date.getDay();
            const daysOfPreviousMonth = (new Date(shownYear, shownMonth, 0)).getDate();
            const daysOfTheMonth = (new Date(shownYear, shownMonth + 1, 0)).getDate();

            const matrix = [];

            var currentDay = -1 * firstWeekday + 1;
            // rows
            for (let i=1; i<=6; i++) {
                const row = [];
                // cols
                for (let j=1; j<=7; j++) {
                    currentDay++;
                    if (currentDay < 1) { if(showNonMonthDays) { row[j] = daysOfPreviousMonth + currentDay; } }
                    else if (currentDay > daysOfTheMonth) { if(showNonMonthDays) { row[j] = currentDay - daysOfTheMonth; } }
                    else row[j] = currentDay;
                }

                matrix[i] = row;
            }

            return matrix;
        },
        isRowSelected(rowValueList, row) {
            if (this.calendar.shownMonth != this.calendar.selectedMonth) {
                // Check if the last week of the previous month is selected
                if (1 == row) {
                    // The current month started on the first, so no previous month is visible
                    if (1 == rowValueList[0]) return false;
                    // Make sure the month matches
                    const previousMonth = this.calendar.shownMonth == 0 ? 11 : this.calendar.shownMonth-1;
                    if (previousMonth != this.calendar.selectedMonth) return false;
                    // Make sure the year matches
                    const previousMonthYear = previousMonth == 11 ? this.calendar.shownYear-1 : this.calendar.shownYear;
                    if (previousMonthYear != this.calendar.selectedYear) return false;
                    
                    const previousMonthLastDay = rowValueList.reduce((a, b) => { return (a > b) ? a : b });

                    return this.calendar.selectedWeek == this.getDayRow(previousMonthLastDay, previousMonth, previousMonthYear);
                }
                // Check if the first weeks of the next month are selected
                else if (3 < row && 14 >= rowValueList.reduce((a, b) => { return (a < b) ? a : b })) {
                    const nextMonthWeekFirstDay = rowValueList.reduce((a, b) => { return (a < b) ? a : b });
                    const nextMonth = this.calendar.shownMonth == 11 ? 0 : this.calendar.shownMonth+1;
                    if (nextMonth != this.calendar.selectedMonth) return false;
                    const nextMonthYear = nextMonth == 0 ? this.calendar.shownYear+1 : this.calendar.shownYear;
                    if (nextMonthYear != this.calendar.selectedYear) return false;

                    return this.calendar.selectedWeek == this.getDayRow(nextMonthWeekFirstDay, nextMonth, nextMonthYear);
                }
                
                return false;
            }
            else if (this.calendar.selectedYear != this.calendar.shownYear) return false;
            else return row == this.calendar.selectedWeek;
        },
        isDayOfPreviousMonth(day, row) {
            return row == 1 && day > 7;
        },
        isDayOfNextMonth(day, row) {
            return (row == 5 || row == 6) && day <= 14;
        },
        selectWeek(day, row) {
            if (this.isDayOfPreviousMonth(day, row)) {
                this.calendarSetPreviousMonth();
            }
            else if (this.isDayOfNextMonth(day, row)) {
                this.calendarSetNextMonth();
            }
            this.calendar.selectedWeek = this.getDayRow(day, this.calendar.shownMonth, this.calendar.shownYear);
            this.calendar.selectedMonth = this.calendar.shownMonth;
            this.calendar.selectedYear = this.calendar.shownYear;

            // Set week timestamps
            const dayOfTheWeek = new Date(this.calendar.selectedYear, this.calendar.selectedMonth, day);
            dayOfTheWeek.setDate(dayOfTheWeek.getDate() - dayOfTheWeek.getDay() + (dayOfTheWeek.getDay() == 0 ? -6 : 1));
            
            this.$emit('setWeekStartTimestamp', dayOfTheWeek.valueOf() / 1000);
        },
        selectedWeekFromDate(day) {
            const firstWeekday = (new Date()).getDay();
            for (let i=0; i<6; i++) {
                if (day <= 7-firstWeekday + i*7) return i+1;
            }
        },
    }
}
</script>