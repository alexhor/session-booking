<?php
$title = 'Buchungen - Gebetshaus Ravensburg - Admin';

function getSettingsList() {
    $settingsList = [];

    foreach (service('settings')->get('App.apiAllowedSettingKeys') as $settingKey => $validation) {
        if (is_callable($validation)) $validation = $validation();
        $value = service('settings')->get($settingKey);
        $setting = [
            'key' => $settingKey,
            'origValue' => $value,
            'value' => $value,
            'validation' => $validation,
        ];
        $settingsList[$settingKey] = $setting;
    }

    return json_encode($settingsList);
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
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link disabled">{{ userName }}</span></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('admin'); ?>"><?= lang('Admin.sessions'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('admin/users'); ?>"><?= lang('Admin.users'); ?></a></li>
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
                    <th style="width: 300px"><?= lang('Admin.setting'); ?></th>
                    <th style="width: 800px"><?= lang('Admin.value'); ?></th>
                    <th style="width: 300px"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="setting in settingsList">
                    <td>{{ setting.key }}</td>
                    <td>
                       <input v-if="typeof setting.validation !== 'object'" v-model="setting.value" class="form-control">
                       <select v-else v-model="setting.value" class="form-select">
                            <option v-for="option in setting.validation" :selected="option == setting.origValue">{{ option }}</option>
                        </select>
                    </td>
                    <td>
                        <button v-if="setting.value != setting.origValue" type="button" class="btn btn-success" @click="saveSetting(setting)"><?= lang('Admin.save'); ?></button>
                        <button v-if="setting.value != setting.origValue" type="button" class="btn btn-warning" @click="setting.value = setting.origValue"><?= lang('Admin.discard'); ?></button>
                        <button type="button" class="btn btn-danger" @click="clearSetting(setting)"><?= lang('Admin.reset'); ?></button>
                    </td>
                </tr>
            </tbody>
        </table>
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
        this.getsettingsList();
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
            settingsList: <?= getSettingsList(); ?>,
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
        getsettingsList() {
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
        clearSetting(setting) {
            var confirmMessage = '<?= lang("Admin.really_clear_setting"); ?>';
            confirmMessage = confirmMessage.replace('{setting}', setting.key);
            if (confirm(confirmMessage)) {
                console.log(setting.key);
                axios.delete(this.baseUrl + "settings/" + setting.key)
                .then((response) => {
                    this.updateConfigValue(setting);
                });
            }
        },
        saveSetting(setting) {
            console.log(setting.value);
            axios.put(this.baseUrl + 'settings/' + setting.key, { 'value': setting.value })
            .then((response) => {
                this.updateConfigValue(setting);
            });
        },
        updateConfigValue(setting) {
            axios.get(this.baseUrl + 'settings/' + setting.key)
            .then((response) => {
                this.settingsList[setting.key].value = response.data.value;
                this.settingsList[setting.key].origValue = response.data.value;
            });
        },
    },
}).mount('#app')
</script>

</body>
</html>
