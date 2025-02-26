<?php
$title = 'Session Booking';
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
<body>

<div id="app">
    <!-- navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" :href="this.baseUrl"><?php echo htmlspecialchars($title); ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="<?= lang('Views.toggle_navigation'); ?>">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <span class="me-auto"></span>
                <ul v-if="userLoggedIn" class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link disabled">{{ userName }}</a></li>
                    <li class="nav-item"><a class="nav-link" @click="logout()" href="#"><?= lang('Views.logout'); ?></a></li>
                </ul>
                <ul v-else class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="modal" data-bs-target="#registrationModal" href="#"><?= lang('Views.register'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="modal" data-bs-target="#loginModal" href="#"><?= lang('Views.login'); ?></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- notifications -->
    <div class="notification-wrapper">
        <span v-for="message in messageList">
            <span class="alert d-inline-block" :class="{ 'alert-success': message.status >= 200 && message.status < 300, 'alert-danger': message.status >= 300 }" role="alert">
                <span class="text">{{ message.text }}</span>
                <button type="button" class="btn-close" aria-label="<?= lang('Views.close'); ?>" @click="clearMessage(message.id)"></button>
            </span>
        </span>
    </div>
    
    <!-- main content -->
    <div class="container">
        <!-- week navigation -->
        <nav aria-label="Week navigation" class="calendar-wrapper">
            <ul class="pagination pagination-lg justify-content-center">
                <li class="page-item">
                    <a class="page-link" @click="selectPreviousWeek()" aria-label="Previous">
                        <span>&laquo;</span>
                    </a>
                </li>
                <li class="page-item"><a class="page-link" @click="calendar.show = !calendar.show">{{ weekRange() }}</a></li>
                <li class="page-item">
                    <a class="page-link" @click="selectNextWeek()" aria-label="Next">
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
                    <div class="month-header">{{ calendar.monthNames[calendar.shownMonth] }} {{ calendar.shownYear }}</div>
                    <div class="month-body">
                        <table>
                            <thead>
                                <tr>
                                    <th><?= lang('Views.weekdays.short.mon'); ?></th>
                                    <th><?= lang('Views.weekdays.short.tue'); ?></th>
                                    <th><?= lang('Views.weekdays.short.wed'); ?></th>
                                    <th><?= lang('Views.weekdays.short.thu'); ?></th>
                                    <th><?= lang('Views.weekdays.short.fri'); ?></th>
                                    <th><?= lang('Views.weekdays.short.sat'); ?></th>
                                    <th><?= lang('Views.weekdays.short.sun'); ?></th>
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

        <!-- session table -->
        <table class="table table-hover session-overview" cellspacing="0">
            <thead style="position: sticky; top: 0">
                <tr style="background-color: white;">
                    <td></td>
                    <th scope="col" :col-id="0"><?= lang('Views.weekdays.long.monday'); ?> - {{ dayMonthFromTimestamp(weekStartTimestamp) }}</th>
                    <th scope="col" :col-id="1"><?= lang('Views.weekdays.long.tuesday'); ?> - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24) }}</th>
                    <th scope="col" :col-id="2"><?= lang('Views.weekdays.long.wednesday'); ?> - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*2) }}</th>
                    <th scope="col" :col-id="3"><?= lang('Views.weekdays.long.thursday'); ?> - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*3) }}</th>
                    <th scope="col" :col-id="4"><?= lang('Views.weekdays.long.friday'); ?> - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*4) }}</th>
                    <th scope="col" :col-id="5"><?= lang('Views.weekdays.long.saturday'); ?> - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*5) }}</th>
                    <th scope="col" :col-id="6"><?= lang('Views.weekdays.long.sunday'); ?> - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*6) }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="rowTimestamp in rowsTimestampsList">
                    <th scope="row">{{ timeFromRowTimestamp(rowTimestamp) }}</th>
                    <td v-for="(_, addDay) in 7" :col-id="addDay">
                        <span v-if="true == timeBooked(weekStartTimestamp, rowTimestamp, addDay)" class="booked"></span>
                        <span v-else-if="false == userId" class="free"></span>
                        <button v-else-if="userId == timeBooked(weekStartTimestamp, rowTimestamp, addDay)" class="own" @click="deleteBookedSession(weekStartTimestamp + rowTimestamp + addDay*24*60*60)"></button>
                        <button v-else class="free" @click="bookSession(weekStartTimestamp + rowTimestamp + addDay*24*60*60)"></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- registration modal -->
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="registrationModalLabel"><?= lang('Views.register_new_account'); ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('Views.close'); ?>"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="recipient-name" class="col-form-label"><?= lang('Validation.user.firstname.label'); ?>:</label>
                        <input type="text" class="form-control" v-model="registrationData.firstname">
                    </div>
                    <div class="mb-3">
                        <label for="recipient-name" class="col-form-label"><?= lang('Validation.user.lastname.label'); ?>:</label>
                        <input type="text" class="form-control" v-model="registrationData.lastname">
                    </div>
                    <div class="mb-3">
                        <label for="recipient-name" class="col-form-label"><?= lang('Validation.user.email.label'); ?>:</label>
                        <input type="email" class="form-control" v-model="registrationData.email">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="register()"><?= lang('Views.register'); ?></button>
            </div>
            </div>
        </div>
    </div>


    <!-- login modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="loginModalLabel"><?= lang('Views.login'); ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('Views.close'); ?>"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="recipient-name" class="col-form-label"><?= lang('Validation.user.email.label'); ?>:</label>
                        <input type="email" class="form-control" v-model="loginEmail">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="requestLoginLink()"><?= lang('Views.request_login_link'); ?></button>
            </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script type="module">


const { createApp, ref } = Vue

document.app = createApp({
    mounted() {
        this.getLoggedInUser();
        
        if (!this.userLoggedIn && "login" == location.pathname.split("/").pop()) {
            var query_params = this.getQueryParameters();
            if ("email" in query_params && "token" in query_params)
            this.login(query_params["email"], query_params["token"])
        }

        this.__startTableHighlighting();
    },
    data() {
        var self = this;
        axios.interceptors.response.use(function (response) {
            return response;
        }, function (e) {
            if (401 == e.status) {
                self.__cleanupUserSession();
            }
            else {
                self.message(Object.values(e.response.data.messages).join("\n"), e.response.data.status);
            }
            
            return Promise.reject(e);
        });

        return {
            userLoggedIn: false,
            userId: false,
            userName: "",
            baseUrl: "<?php echo base_url(); ?>",
            registrationData: {
                firstname: "",
                lastname: "",
                email: "",
            },
            loginEmail: "",
            messageList: {},
            __nextMessageId: 1,
            config: {
                session: {
                    interval: 60 * 60,
                    offset: 0,
                },
            },
            weekStartTimestamp: self.getWeekStartTimestamp(),
            weekEndTimestamp: self.getWeekEndTimestamp(),
            bookedTimestamps: {},
            calendar: {
                show: false,
                shownMonth: (new Date()).getMonth(),
                shownYear: (new Date()).getFullYear(),
                selectedWeek: self.selectedWeekFromDate((new Date()).getDate()),
                selectedMonth: (new Date()).getMonth(),
                selectedYear: (new Date()).getFullYear(),
                monthNames: [
                    '<?= lang('Views.months.january'); ?>',
                    '<?= lang('Views.months.february'); ?>',
                    '<?= lang('Views.months.march'); ?>',
                    '<?= lang('Views.months.april'); ?>',
                    '<?= lang('Views.months.may'); ?>',
                    '<?= lang('Views.months.june'); ?>',
                    '<?= lang('Views.months.july'); ?>',
                    '<?= lang('Views.months.august'); ?>',
                    '<?= lang('Views.months.september'); ?>',
                    '<?= lang('Views.months.october'); ?>',
                    '<?= lang('Views.months.november'); ?>',
                    '<?= lang('Views.months.december'); ?>'
                ]
            }
        }
    },
    computed: {
        rowsTimestampsList() {
            var response = [];
            for (let rowTimestamp = this.config.session.offset; rowTimestamp < 24*60*60; rowTimestamp+=this.config.session.interval) {
                response.push(rowTimestamp);
            }

            return response;
        },
        calendarDayMatrix() {
            return this.generateCalendarDayMatrix(this.calendar.shownYear, this.calendar.shownMonth);
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
        selectPreviousWeek() {
            this.weekStartTimestamp -= 60*60*24*7;
            this.weekEndTimestamp -= 60*60*24*7;

            const date = new Date(this.weekStartTimestamp * 1000);
            this.calendar.shownYear = this.calendar.selectedYear = date.getFullYear();
            this.calendar.shownMonth = this.calendar.selectedMonth = date.getMonth();
            this.calendar.selectedWeek = this.getDayRow(date.getDate(), this.calendar.shownMonth, this.calendar.shownYear);

            this.fetchWeekBookings();
        },
        selectNextWeek() {
            this.weekStartTimestamp += 60*60*24*7;
            this.weekEndTimestamp += 60*60*24*7;

            const date = new Date(this.weekStartTimestamp * 1000);
            this.calendar.shownYear = this.calendar.selectedYear = date.getFullYear();
            this.calendar.shownMonth = this.calendar.selectedMonth = date.getMonth();
            this.calendar.selectedWeek = this.getDayRow(date.getDate(), this.calendar.shownMonth, this.calendar.shownYear);

            this.fetchWeekBookings();
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
            const dayOfTheWeek = new Date(this.calendar.selectedYear, this.calendar.selectedMonth, day)
            this.weekStartTimestamp = this.getWeekStartTimestamp(dayOfTheWeek);
            this.weekEndTimestamp = this.getWeekEndTimestamp(dayOfTheWeek);

            this.fetchWeekBookings();
        },
        selectedWeekFromDate(day) {
            const firstWeekday = (new Date()).getDay();
            for (let i=0; i<6; i++) {
                if (day <= 7-firstWeekday + i*7) return i+1;
            }
        },
        calendarSetToday() {
            const today = new Date();
            this.calendar.selectedMonth = this.calendar.shownMonth = today.getMonth();
            this.calendar.selectedYear = this.calendar.shownYear = today.getFullYear();
            this.calendar.selectedWeek = this.getDayRow(today.getDate(), this.calendar.shownMonth, this.calendar.shownYear);

            // Set week timestamps
            this.weekStartTimestamp = this.getWeekStartTimestamp(today);
            this.weekEndTimestamp = this.getWeekEndTimestamp(today);

            this.fetchWeekBookings();
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
        message(text, status=200, secondsToLive=10) {
            const id = this.__nextMessageId;
            this.__nextMessageId++;

            const timeoutId = setTimeout(() => {
                delete this.messageList[id];
            }, secondsToLive*1000);

            this.messageList[id] = {
                id: id,
                status: status,
                text: text,
                timeoutId: timeoutId,
            };
        },
        clearMessage(id) {
            clearTimeout(this.messageList[id].timeoutId);
            delete this.messageList[id];
        },
        timeBooked(weekStartTimestamp, rowTimestamp, day) {
            const startTime = weekStartTimestamp + rowTimestamp + day*24*60*60;
            if (!(startTime in this.bookedTimestamps)) return false;
            else if (false == this.bookedTimestamps[startTime].userId) return true;
            else return this.bookedTimestamps[startTime].userId;
        },
        weekRange() {
            const startDay = new Date(this.weekStartTimestamp * 1000);
            const endDay = new Date(this.weekEndTimestamp * 1000);
            return this.dayMonthFromTimestamp(startDay.valueOf() / 1000) + ' - ' + this.dayMonthFromTimestamp(endDay.valueOf() / 1000);
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
        getWeekStartTimestamp(date=null) {
            var d = date ? date : new Date();
            var day = d.getDay();
            var diff = d.getDate() - day + (day == 0 ? -6 : 1);
            const newTimestamp = (new Date(d.setDate(diff))).setHours(0, 0, 0, 0)
            return Math.floor(newTimestamp / 1000);
        },
        getWeekEndTimestamp(date=null) {
            var weekStartDate = new Date(this.getWeekStartTimestamp(date) * 1000);
            weekStartDate.setDate(weekStartDate.getDate() + 6);
            return Math.floor(weekStartDate.setHours(23, 59, 59, 0) / 1000);
        },
        getQueryParameters() {
            var query_vars_raw = window.location.search.substring(1).split("&");
            var query_vars = {};
            query_vars_raw.forEach((var_string) => {
                var var_string_split = var_string.split("=");
                if (2 != var_string_split.length) return;
                query_vars[var_string_split[0]] = decodeURIComponent(var_string_split[1]);
            });
            return query_vars;
        },
        register() {
            var self = this;
            axios.post(this.baseUrl + "users", {
                firstname: this.registrationData.firstname,
                lastname: this.registrationData.lastname,
                email: this.registrationData.email,
            })
            .then((response) => {
                if ("data" in response) {
                    if (typeof response.data !== 'object') self.message(response.data, response.status);
                    else if ("message" in response.data) self.message(response.data.message, response.status);
                }
                else self.message(response.statusText, response.status);

                self.loginEmail = self.registrationData.email;
                this.requestLoginLink();
            });
        },
        requestLoginLink() {
            var self = this;
            axios.post(this.baseUrl + "users/authentication", {
                email: this.loginEmail,
            })
            .then((response) => {
                if ("data" in response) {
                    if (typeof response.data !== 'object') self.message(response.data, response.status);
                    else if ("message" in response.data) self.message(response.data.message, response.status);
                }
                else self.message(response.statusText, response.status);
            });
        },
        getLoggedInUser() {
            var self = this;
            axios.get(this.baseUrl + "users/authentication/login")
            .then((response) => {
                var user_id = response.data;
                if (!user_id) {
                    self.__cleanupUserSession();
                }
                else {
                    self.userId = parseInt(user_id, 10);
                    self.userLoggedIn = true;

                    axios.get(this.baseUrl + "users/" + self.userId)
                    .then((response) => {
                        self.userName = response.data.firstname + " " + response.data.lastname;
                    });

                    self.fetchWeekBookings();
                }
            });
        },
        login(email, token) {
            var self = this;
            axios.post(this.baseUrl + "users/authentication/login", {
                email: email,
                token: token,
            })
            .then((response) => {
                if ("data" in response) {
                    if (typeof response.data !== 'object') self.message(response.data, response.status);
                    else if ("message" in response.data) self.message(response.data.message, response.status);
                }
                else self.message(response.statusText, response.status);
                
                self.getLoggedInUser();
            });
        },
        __cleanupUserSession() {
            this.userLoggedIn = false;
            this.userId = false;
            this.userName = "";
            this.registrationData.firstname = "";
            this.registrationData.lastname = "";
            this.registrationData.email = "";
            this.loginEmail = "";
            this.bookedTimestamps = {};

            this.fetchWeekBookings();
        },
        logout() {
            var self = this;
            axios.post(this.baseUrl + "users/authentication/logout", {})
            .then((response) => {
                self.__cleanupUserSession();

                if ("data" in response) {
                    if (typeof response.data !== 'object') self.message(response.data, response.status);
                    else if ("message" in response.data) self.message(response.data.message, response.status);
                }
                else self.message(response.statusText, response.status);
            });
        },
        bookSession(timestamp) {
            if (!this.userLoggedIn) return;
            var self = this;
            axios.post(this.baseUrl + "sessions/bookings", {
                "user_id": self.userId,
                "start_time": timestamp,
            })
            .then((response) => {
                self.fetchWeekBookings();
            });
        },
        deleteBookedSession(timestamp) {
            if (!this.userLoggedIn) return;
            var self = this;
            const bookingId = self.bookedTimestamps[timestamp].id;
            axios.delete(this.baseUrl + "sessions/bookings/" + bookingId)
            .then((response) => {
                self.fetchWeekBookings();
            });
        },
        fetchWeekBookings() {
            var self = this;
            axios.get(this.baseUrl + "sessions/bookings/" + this.weekStartTimestamp + "/" + this.weekEndTimestamp)
            .then((response) => {
                self.bookedTimestamps = {};
                for (const sessionBooking of response.data) {
                    self.bookedTimestamps[sessionBooking.start_time] = {
                        id: typeof sessionBooking.id != "undefined" ? sessionBooking.id : false,
                        userId: typeof sessionBooking.user_id != "undefined" ? sessionBooking.user_id : false,
                        startTime: sessionBooking.start_time,
                    };
                }
            });
        }
    },
}).mount('#app')
</script>

<style>
    /** General **/
    .container {
        overflow-x: auto;
    }

    a {
        cursor: pointer;
    }

    /** Calendar */
    .calendar-wrapper {
        position: relative;
    }

    .calendar {
        position: absolute;
        top: calc(100% + 10px);
        left: 50%;
        transform: translatex(-50%);
        z-index: 100;
        background-color: white;
        border: 2px solid lightgray;
    }

    .calendar::before {
        content: "";
        border-bottom: 20px solid lightgray;
        border-left: 20px solid transparent;
        border-right: 20px solid transparent;
        position: absolute;
        left: 50%;
        top: -20px;
        transform: translatex(-50%);
    }

    .calendar-navigation {
        border-bottom: 2px solid lightgray;
        padding: 4px;
    }

    .calendar-navigation > * {
        display: inline-block;
        width: 20%;
        margin: 0;
        text-align: center;
        font-size: 20px;
        padding: 3px 0;
        border-radius: 5px;
        color: #9d9d9d;
    }

    .calendar-navigation .today > svg {
        overflow: visible;
    }

    .calendar-navigation .today > .hover-show {
        display: none;   
    }

    .calendar-navigation .today:focus > .hover-hide, .calendar-navigation .today:hover > .hover-hide, .calendar-navigation .today:active > .hover-hide {
        display: none;
    }

    .calendar-navigation .today:focus > .hover-show, .calendar-navigation .today:hover > .hover-show, .calendar-navigation .today:active > .hover-show {
        display: initial;
    }

    .calendar-navigation .today > span {
        background-color: black;
        border-radius: 50%;
        height: 20px;
        width: 20px;
        display: block;
        position: absolute;
        top: 0;
        left: 50%;
        transform: translatex(-50%);
    }

    .calendar-navigation > *:focus, .calendar-navigation > *:hover, .calendar-navigation > *:active {
        background-color: darkgray;
        color: white;
    }

    .month-header {
        text-align: center;
        font-weight: bold;
        font-size: 22px;
    }

    .month-body thead {
        border-bottom: 1px solid;
    }

    .month-body thead th {
        padding: 0px 5px;
        font-weight: normal;
    }

    .month-body tbody {
        cursor: pointer;
    }

    .month-body tbody td {
        text-align: center;
        width: 40px;
        height: 40px;
        font-weight: bold;
        padding: 0;
    }

    .month-body tbody .day {
        width: 36px;
        height: 36px;
        display: block;
        line-height: 32px;
        padding: 2px;
    }

    .month-body tbody td .day:focus, .month-body tbody td .day:hover, .month-body tbody td .day:active {
        background-color: lightgray;
        border-radius: 50%;
    }

    .month-body tbody .day.previous-month, .month-body tbody .day.next-month {
        font-weight: normal;
    }

    .month-body tbody tr.selected .day {
        color: blue;
    }

    /** Notifications **/
    .notification-wrapper {
        padding: 10px 20px;
        min-height: 96px;
    }

    .notification-wrapper .alert {
        margin-right: 20px;
        white-space: pre;
    }

    .notification-wrapper .text {
        vertical-align: middle;
    }

    .notification-wrapper .text + button {
        vertical-align: middle;
        margin-left: 15px;
    }

    /** Session overview **/
    .session-overview {
        border-collapse: separate;
        table-layout: fixed;
    }
    .session-overview th, .session-overview td {
        border-width: 2px;
    }

    .session-overview thead td {
        border-width: 0 2px 2px 0;
        background-color: transparent;
        width: 80px;
    }

    .session-overview th.highlight {
        background-color: var(--bs-table-hover-bg);
    }

    .session-overview tbody th {
        text-align: center;
        vertical-align: middle;
    }

    .session-overview td {
        padding: 0;
    }

    .session-overview th {
        background-color: transparent;
    }

    .session-overview thead th {
        width: 175px;
    }

    .session-overview button, .session-overview span {
        background-color: transparent;
        background-repeat: no-repeat;
        background-size: auto 100%;
        background-position: center;
        border: none;
        padding: 0;
        margin: 0;
        min-width: 100%;
        min-height: 100%;
        height: 50px;
        display: block;
    }

    .session-overview button.own {
        background-color: #d1e7dd;
        background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%23198754" class="bi bi-check-lg" viewBox="0 0 16 16"><path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/></svg>');
    }

    .session-overview button.own:focus, .session-overview button.own:hover, .session-overview button.own:active {
        background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%23dc3545" class="bi bi-calendar-x-fill" viewBox="0 0 16 16"><path d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2M6.854 8.146 8 9.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 10l1.147 1.146a.5.5 0 0 1-.708.708L8 10.707l-1.146 1.147a.5.5 0 0 1-.708-.708L7.293 10 6.146 8.854a.5.5 0 1 1 .708-.708"/></svg>');
        background-size: auto 70%;
    }

    .session-overview button.free:focus, .session-overview button.free:active, .session-overview button.free:hover {
        background-color: #d1e7dd;
        background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%23198754" class="bi bi-calendar-check" viewBox="0 0 16 16"><path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/></svg>');
        background-size: auto 70%;
    }

    .session-overview span.booked {
        background-color: #fff3cd;
        background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="%23ffc107" class="bi bi-x" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/></svg>');
        background-size: auto 140%;
    }

    .session-overview span.free {
        /*background-image: url("<?php echo base_url(); ?>img/calendar-check.svg");*/
    }

</style>

</body>
</html>
