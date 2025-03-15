<?php
function getSessionErrorsJson() {
    $error_list = [];

    if (session()->has('error')) {
        array_push($error_list, [
            'message' => session()->get('error'),
            'status' => 400
        ]);
    }

    if (session()->has('errors')) {
        foreach (session()->get('errors') as $key => $message) {
            array_push($error_list, [
                'message' => $message,
                'status' => 400
            ]);
        }
    }

    return json_encode($error_list);
}

$title = 'Buchungen - Gebetshaus Ravensburg';
$additionalStyles= '';

$configs = [
    'daysInAWeek' => 8,
    'weekStartTimestamp' => strtotime("06.04.2025 0:0:0"),
];

$eventMarkingList = [
    [
        'startTimestamp' => strtotime("06.04.2025 18:0:0"),
        'endTimestamp' => strtotime("13.04.2025 19:0:0"),
        'color' => '#02cbb8',
        'title' => '24/7 Gebet',
        'description' => ""
    ]
];

foreach($eventMarkingList as $i => &$marking) {
    $marking['cssClasses'] = 'event-marking-' . $i;
    $additionalStyles .= "." . $marking['cssClasses'] . " {
        border-color: " . $marking['color'] . " !important;
    }";
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= site_url('style.css'); ?>" rel="stylesheet">
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
        <div class="legend-wrapper">
            <span class="legend-item" v-for="marking in eventMarkingList" :class="marking.cssClasses">{{ marking.title }}<small class="legend-description">{{ marking.description }}</small></span>
        </div>
        <br>

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
                    <div class="month-header">{{ lang.monthNames[calendar.shownMonth] }} {{ calendar.shownYear }}</div>
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
                    <th scope="col" v-for="(day, dayIndex) in configs.daysInAWeek" :col-id="dayIndex">{{ weekDayNameFromTimestamp(weekStartTimestamp + 60*60*24*dayIndex) }} - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*dayIndex) }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="rowTimestamp in rowsTimestampsList">
                    <th scope="row">{{ timeFromRowTimestamp(rowTimestamp) }}</th>
                    <td v-for="(_, addDay) in configs.daysInAWeek" :col-id="addDay" :class="getEventMarkingClass(weekStartTimestamp + rowTimestamp + addDay*24*60*60)">
                        <button v-if="userId === timeBooked(weekStartTimestamp, rowTimestamp, addDay)" class="own" @click="deleteBookedSession(weekStartTimestamp + rowTimestamp + addDay*24*60*60)" :class="{ no_background_image: bookedTimeHasTitleOrDescription(weekStartTimestamp, rowTimestamp, addDay) }">
                            <b v-if="getBookedTime(weekStartTimestamp, rowTimestamp, addDay).title">{{ getBookedTime(weekStartTimestamp, rowTimestamp, addDay).title }}</b><br>
                            <small v-if="getBookedTime(weekStartTimestamp, rowTimestamp, addDay).description">{{ getBookedTime(weekStartTimestamp, rowTimestamp, addDay).description }}</small>
                        </button>
                        <span v-else-if="false !== timeBooked(weekStartTimestamp, rowTimestamp, addDay)" class="booked" :class="{ no_background_image: bookedTimeHasTitleOrDescription(weekStartTimestamp, rowTimestamp, addDay) }">
                            <b v-if="getBookedTime(weekStartTimestamp, rowTimestamp, addDay).title">{{ getBookedTime(weekStartTimestamp, rowTimestamp, addDay).title }}</b><br>
                            <small v-if="getBookedTime(weekStartTimestamp, rowTimestamp, addDay).description">{{ getBookedTime(weekStartTimestamp, rowTimestamp, addDay).description }}</small>
                        </span>
                        <span v-else-if="null == userId" class="free" data-bs-toggle="modal" data-bs-target="#registrationModal"></span>
                        <button v-else class="free" @click="bookSession(weekStartTimestamp + rowTimestamp + addDay*24*60*60)"></button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="session-overview-mobile">
            <div class="day" v-for="(_, addDay) in configs.daysInAWeek" :class="{ active: addDay == calendar.mobileSelectedDay }">
                <div class="row heading">
                    <div class="time"></div>
                    <div class="booking">
                        <button @click="sessionOverviewMobilePreviousDay()">&lt;</button>
                        <span>{{ weekDayNameFromTimestamp(weekStartTimestamp + 60*60*24*addDay) }} - {{ dayMonthFromTimestamp(weekStartTimestamp + 60*60*24*addDay) }}</span>
                        <button @click="sessionOverviewMobileNextDay()">&gt;</button>
                    </div>
                </div>
                <div v-for="rowTimestamp in rowsTimestampsList" class="row">
                    <div class="time">{{ timeFromRowTimestamp(rowTimestamp) }}</div>
                    <div class="booking" :class="getEventMarkingClass(weekStartTimestamp + rowTimestamp + addDay*24*60*60)">
                        <button v-if="userId === timeBooked(weekStartTimestamp, rowTimestamp, addDay)" class="own" @click="deleteBookedSession(weekStartTimestamp + rowTimestamp + addDay*24*60*60)" :class="{ no_background_image: bookedTimeHasTitleOrDescription(weekStartTimestamp, rowTimestamp, addDay) }">
                            <b v-if="getBookedTime(weekStartTimestamp, rowTimestamp, addDay).title">{{ getBookedTime(weekStartTimestamp, rowTimestamp, addDay).title }}</b><br>
                            <small v-if="getBookedTime(weekStartTimestamp, rowTimestamp, addDay).description">{{ getBookedTime(weekStartTimestamp, rowTimestamp, addDay).description }}</small>
                        </button>
                        <span v-else-if="false !== timeBooked(weekStartTimestamp, rowTimestamp, addDay)" class="booked" :class="{ no_background_image: bookedTimeHasTitleOrDescription(weekStartTimestamp, rowTimestamp, addDay) }">
                            <b v-if="getBookedTime(weekStartTimestamp, rowTimestamp, addDay).title">{{ getBookedTime(weekStartTimestamp, rowTimestamp, addDay).title }}</b><br>
                            <small v-if="getBookedTime(weekStartTimestamp, rowTimestamp, addDay).description">{{ getBookedTime(weekStartTimestamp, rowTimestamp, addDay).description }}</small>
                        </span>
                        <span v-else-if="null == userId" class="free" data-bs-toggle="modal" data-bs-target="#registrationModal"></span>
                        <button v-else class="free" @click="bookSession(weekStartTimestamp + rowTimestamp + addDay*24*60*60)"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- registration modal -->
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="registrationModalLabel"><?= lang('Views.register_new_account'); ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('Views.close'); ?>"></button>
                </div>
                <form action="#">
                    <div class="modal-body">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal"><?= lang('Views.already_have_an_account_then_login'); ?></a>
                        <br><br>
                        <div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label"><?= lang('Validation.user.firstname.label'); ?>:</label>
                                <input type="text" class="form-control" name="firstname" v-model="registrationData.firstname" required>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label"><?= lang('Validation.user.lastname.label'); ?>:</label>
                                <input type="text" class="form-control" name="lastname" v-model="registrationData.lastname" required>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label"><?= lang('Validation.user.email.label'); ?>:</label>
                                <input type="email" class="form-control" email="email" v-model="registrationData.email" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="register()"><?= lang('Views.register'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- login modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= site_url('users/authentication'); ?>" method="POST" id="login-form">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="loginModalLabel"><?= lang('Views.login'); ?></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('Views.close'); ?>"></button>
                    </div>
                    <div class="modal-body">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#registrationModal"><?= lang('Views.no_account_yet_then_register'); ?></a>
                        <br><br>
                        <div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label"><?= lang('Validation.user.email.label'); ?>:</label>
                                <input type="text" class="form-control" name="email" v-model="loginEmail" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary"><?= lang('Views.request_login_link'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
<script type="module">


const { createApp, ref } = Vue

document.app = createApp({
    mounted() {
        this.getLoggedInUser();
        this.fetchWeekBookings();
        this.__startTableHighlighting();

        const pageLoadErrorMessageList = <?= getSessionErrorsJson(); ?>;
        pageLoadErrorMessageList.forEach(messageObject => {
            this.message(messageObject.message, messageObject.status, 0);
        });
    },
    data() {
        var self = this;
        axios.interceptors.response.use(function (response) {
            return response;
        }, function (e) {
            if (401 == e.status) {
                self.logout();
            }
            else {
                self.message(Object.values(e.response.data.messages).join("\n"), e.response.data.status);
            }
            
            return Promise.reject(e);
        });

        return {
            userLoggedIn: false,
            userId: null,
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
            __weekStartTimestamp: null,
            config: {
                session: {
                    interval: 60 * 60,
                    offset: 0,
                },
            },
            bookedTimestamps: {},
            calendar: {
                show: false,
                shownMonth: (new Date()).getMonth(),
                shownYear: (new Date()).getFullYear(),
                selectedWeek: self.selectedWeekFromDate((new Date()).getDate()),
                selectedMonth: (new Date()).getMonth(),
                selectedYear: (new Date()).getFullYear(),
                mobileSelectedDay: 0,
            },
            eventMarkingList: <?= json_encode($eventMarkingList); ?>,
            configs: <?= json_encode($configs); ?>,
            lang: {
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
                ],
                dayNames: [
                    '<?= lang('Views.weekdays.long.monday'); ?>',
                    '<?= lang('Views.weekdays.long.tuesday'); ?>',
                    '<?= lang('Views.weekdays.long.wednesday'); ?>',
                    '<?= lang('Views.weekdays.long.thursday'); ?>',
                    '<?= lang('Views.weekdays.long.friday'); ?>',
                    '<?= lang('Views.weekdays.long.saturday'); ?>',
                    '<?= lang('Views.weekdays.long.sunday'); ?>',
                ],
            },
        }
    },
    computed: {
        weekStartTimestamp: {
            get() {
                if (null == this.__weekStartTimestamp) {
                    this.__weekStartTimestamp = this.configs.weekStartTimestamp;

                    const date = new Date(this.__weekStartTimestamp * 1000);
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
            return this.weekStartTimestamp + this.configs.daysInAWeek*24*60*60 + 23*60*60;
        },
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
        sessionOverviewMobilePreviousDay() {
            if (0 == this.calendar.mobileSelectedDay) {
                this.calendar.mobileSelectedDay = this.configs.daysInAWeek - 1 - (this.configs.daysInAWeek - 7);
                this.selectPreviousWeek();
            }
            else {
                this.calendar.mobileSelectedDay--;
            }
        },
        sessionOverviewMobileNextDay() {
            if (this.configs.daysInAWeek - 1 == this.calendar.mobileSelectedDay) {
                this.calendar.mobileSelectedDay = this.configs.daysInAWeek - 7;
                this.selectNextWeek();
            }
            else {
                this.calendar.mobileSelectedDay++;
            }
        },
        weekDayNameFromTimestamp(timestamp) {
            const date = new Date(timestamp * 1000);
            var weekday = date.getDay() == 0 ? 7 : date.getDay();
            weekday--;
            return this.lang.dayNames[weekday];
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
        selectPreviousWeek() {
            this.weekStartTimestamp -= 60*60*24*7;

            const date = new Date(this.weekStartTimestamp * 1000);
            this.calendar.shownYear = this.calendar.selectedYear = date.getFullYear();
            this.calendar.shownMonth = this.calendar.selectedMonth = date.getMonth();
            this.calendar.selectedWeek = this.getDayRow(date.getDate(), this.calendar.shownMonth, this.calendar.shownYear);

            this.fetchWeekBookings();
        },
        selectNextWeek() {
            this.weekStartTimestamp += 60*60*24*7;

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

            this.fetchWeekBookings();
        },
        selectedWeekFromDate(day) {
            const firstWeekday = (new Date()).getDay();
            for (let i=0; i<6; i++) {
                if (day <= 7-firstWeekday + i*7) return i+1;
            }
        },
        calendarSetToday() {
            const today = new Date(this.configs.weekStartTimestamp * 1000);
            this.calendar.selectedMonth = this.calendar.shownMonth = today.getMonth();
            this.calendar.selectedYear = this.calendar.shownYear = today.getFullYear();
            this.calendar.selectedWeek = this.getDayRow(today.getDate(), this.calendar.shownMonth, this.calendar.shownYear);

            // Set week timestamps
            this.weekStartTimestamp = this.configs.weekStartTimestamp

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

            this.messageList[id] = {
                id: id,
                status: status,
                text: text,
            };

            if (0 < secondsToLive) {
                const timeoutId = setTimeout(() => {
                    delete this.messageList[id];
                }, secondsToLive*1000);
                this.messageList[id].timeoutId = timeoutId;
            }
        },
        clearMessage(id) {
            if (typeof this.messageList[id].timeoutId != "undefined") {
                clearTimeout(this.messageList[id].timeoutId);
            }
            
            delete this.messageList[id];
        },
        bookedTimeHasTitleOrDescription(weekStartTimestamp, rowTimestamp, day) {
            const booking = this.getBookedTime(weekStartTimestamp, rowTimestamp, day);
            if (typeof booking.title !== "undefined" && booking.title !== null && booking.title != '') return true;
            else if (typeof booking.description !== "undefined" && booking.description != null && booking.description != '') return true;
            else return false;
        },
        getBookedTime(weekStartTimestamp, rowTimestamp, day) {
            const startTime = weekStartTimestamp + rowTimestamp + day*24*60*60;
            if (!(startTime in this.bookedTimestamps)) {
                return false;
            }
            else {
                return this.bookedTimestamps[startTime];
            }
        },
        timeBooked(weekStartTimestamp, rowTimestamp, day) {
            const bookedTime = this.getBookedTime(weekStartTimestamp, rowTimestamp, day);
            if (false == bookedTime) {
                return false;
            }
            else if (bookedTime.user_id === false) {
                return null;
            }
            else {
                return bookedTime.user_id;
            }
        },
        weekRange() {
            return this.dayMonthFromTimestamp(this.weekStartTimestamp) + ' - ' + this.dayMonthFromTimestamp(this.weekEndTimestamp);
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

                // Redirect to login link fetching
                Vue.nextTick(function () {
                    document.getElementById("login-form").submit();
                });
            });
        },
        getLoggedInUser() {
            var self = this;
            axios.get(this.baseUrl + "users/authentication/login")
            .then((response) => {
                var user_id = response.data;
                if (user_id) {
                    self.userId = parseInt(user_id, 10);
                    self.userLoggedIn = true;

                    axios.get(this.baseUrl + "users/" + self.userId)
                    .then((response) => {
                        self.userName = response.data.firstname + " " + response.data.lastname;
                    });

                }
            });
        },
        logout() {
            window.location.replace(this.baseUrl + "users/authentication/logout");
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
                for (var sessionBooking of response.data) {
                    if (typeof sessionBooking.id == "undefined") sessionBooking.id = false;
                    if (typeof sessionBooking.user_id == "undefined") sessionBooking.user_id = false;
                    if (typeof sessionBooking.title == "undefined") sessionBooking.title = '';
                    if (typeof sessionBooking.description == "undefined") sessionBooking.description = '';

                    self.bookedTimestamps[sessionBooking.start_time] = sessionBooking;
                }
            });
        }
    },
}).mount('#app')
</script>

<style>
<?= $additionalStyles ?>
</style>

</body>
</html>
