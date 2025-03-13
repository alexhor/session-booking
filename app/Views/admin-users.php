<?php
$title = 'Buchungen - Gebetshaus Ravensburg - Admin';
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
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link disabled">{{ userName }}</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('admin'); ?>"><?= lang('Admin.sessions'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" @click="logout()" href="#"><?= lang('Views.logout'); ?></a></li>
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
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th><?= lang('Validation.user.id.label'); ?></th>
                    <th><?= lang('Validation.user.firstname.label'); ?></th>
                    <th><?= lang('Validation.user.lastname.label'); ?></th>
                    <th><?= lang('Validation.user.email.label'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in userList">
                    <td>{{ user.id }}</td>
                    <td>{{ user.firstname }}</td>
                    <td>{{ user.lastname }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                        <button type="button" class="btn btn-danger" @click="promptReallyDeleteUser(user)"><?= lang('Admin.delete'); ?></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- session details modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="userDetailsLabel"><?= lang('Admin.session_details.details'); ?></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= lang('Views.close'); ?>"></button>
                </div>
                <form action="#">
                    <div class="modal-body">
                        <div>
                            <div class="mb-3">
                                <!-- TODO: load user on demand (and maybe chache?) -->
                                <label for="userId" class="col-form-label"><?= lang('Admin.session_details.user'); ?>:</label>
                                <input type="text" class="form-control" name="userId" :value="userDetails.userId" disabled>
                            </div>
                            <div class="mb-3">
                                <!-- TODO: load user on demand (and maybe chache?) -->
                                <label for="time" class="col-form-label"><?= lang('Admin.session_details.time'); ?>:</label>
                                <input type="text" class="form-control" name="time" :value="userDetailsStartTimeFormatted" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="col-form-label"><?= lang('Admin.session_details.title'); ?>:</label>
                                <input type="text" class="form-control" name="title" v-model="userDetails.title">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="title_is_public" v-model="userDetails.title_is_public">
                                <label for="title_is_public" class="form-check-label"><?= lang('Admin.session_details.title_is_public'); ?></label>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="col-form-label"><?= lang('Admin.session_details.description'); ?>:</label>
                                <textarea class="form-control" name="description" v-model="userDetails.description"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="description_is_public" v-model="userDetails.description_is_public">
                                <label for="description_is_public" class="form-check-label"><?= lang('Admin.session_details.description_is_public'); ?></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click="deleteBookedSession(userDetails.start_time)"><?= lang('Admin.session_details.delete'); ?></button>
                    </div>
                </form>
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
        this.getUserList();
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
            userDetails: {},
            userList: [],
            userId: false,
            userName: "",
            baseUrl: "<?php echo base_url(); ?>",
            messageList: {},
            __nextMessageId: 1,
        }
    },
    computed: {
    },
    methods: {
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
        getUserList() {
            axios.get(this.baseUrl + "users")
            .then((response) => {
                this.userList = response.data;
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

                    // Get users name
                    axios.get(this.baseUrl + "users/" + self.userId)
                    .then((response) => {
                        self.userName = response.data.firstname + " " + response.data.lastname;
                    });
                }
            });
        },
        __cleanupUserSession() {
            window.location.replace(this.baseUrl);
        },
        logout() {
            var self = this;
            axios.post(this.baseUrl + "users/authentication/logout", {})
            .then((response) => {
                self.__cleanupUserSession();
            });
        },
        promptReallyDeleteUser(user) {
            var confirmMessage = '<?= lang("Admin.really_delete_user"); ?>';
            confirmMessage = confirmMessage.replace('{user}', user.firstname + ' ' + user.lastname).replace('{email}', user.email);
            if (confirm(confirmMessage)) {
                axios.delete(this.baseUrl + "users/" + user.id)
                .then((response) => {
                    this.getUserList();
                });
            }
        },
    },
}).mount('#app')
</script>

</body>
</html>
