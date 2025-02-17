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
        <table class="table table-hover session-overview" cellspacing="0">
            <thead style="position: sticky; top: 0">
                <tr style="background-color: white;">
                    <td></td>
                    <th scope="col" :col-id="0"><?= lang('Views.monday'); ?></th>
                    <th scope="col" :col-id="1"><?= lang('Views.tuesday'); ?></th>
                    <th scope="col" :col-id="2"><?= lang('Views.wednesday'); ?></th>
                    <th scope="col" :col-id="3"><?= lang('Views.thursday'); ?></th>
                    <th scope="col" :col-id="4"><?= lang('Views.friday'); ?></th>
                    <th scope="col" :col-id="5"><?= lang('Views.saturday'); ?></th>
                    <th scope="col" :col-id="6"><?= lang('Views.sunday'); ?></th>
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
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="register()"><?= lang('Validation.register'); ?></button>
            </div>
            </div>
        </div>
    </div>


    <!-- login modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="loginModalLabel"><?= lang('Validation.login'); ?></h1>
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
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="requestLoginLink()"><?= lang('Validation.requst_login_link'); ?></button>
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
            console.log(e);
            self.message(Object.values(e.response.data.messages).join("\n"), e.response.data.status);
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
            bookedTimestamps: {}
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
        getWeekStartTimestamp() {
            var d = new Date();
            var day = d.getDay(),
            diff = d.getDate() - day + (day == 0 ? -6 : 1);
            return Math.floor((new Date(d.setDate(diff))).setHours(0, 0, 0, 0) / 1000);
        },
        getWeekEndTimestamp() {
            var d = new Date();
            var day = d.getDay(),
            diff = d.getDate() - day + 7;
            return Math.floor((new Date(d.setDate(diff))).setHours(23, 59, 59, 0) / 1000);
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
                    self.userId  = false;
                    self.fetchWeekBookings();
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
        logout() {
            var self = this;
            axios.post(this.baseUrl + "users/authentication/logout", {})
            .then((response) => {
                self.userLoggedIn = false;
                self.userId = false;
                self.userName = "";
                self.registrationData.firstname = "";
                self.registrationData.lastname = "";
                self.registrationData.email = "";
                self.loginEmail = "";
                self.bookedTimestamps = {};

                if ("data" in response) {
                    if (typeof response.data !== 'object') self.message(response.data, response.status);
                    else if ("message" in response.data) self.message(response.data.message, response.status);
                }
                else self.message(response.statusText, response.status);

                self.fetchWeekBookings();
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
