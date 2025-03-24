<script setup>
import Navbar from './components/Navbar.vue'
import Legend from './components/calendar/Legend.vue'
import WeekNavigation from './components/calendar/WeekNavigation.vue'
import SessionTable from './components/calendar/SessionTable.vue'
import UserList from './components/admin/UserList.vue'
import Settings from './components/admin/Settings.vue'
</script>

<template>
  <Navbar
    :lang="lang"
    :configs="configs"
    :user="user"
    :navigationPage="navigationPage"
    @message="(text, status) => message(text, status)"
    @navigate="(page) => navigationPage = page"/>

  <!-- notifications -->
  <div class="notification-wrapper">
    <span v-for="message in messageList">
      <span class="alert d-inline-block" :class="{ 'alert-success': message.status >= 200 && message.status < 300, 'alert-danger': message.status >= 300 }" role="alert">
        <span class="text">{{ message.text }}</span>
        <button type="button" class="btn-close" aria-label="{{ lang['Views.close'] }}" @click="clearMessage(message.id)"></button>
      </span>
    </span>
  </div>

  
  <div class="container">
    <div v-if="user.isAdmin && 'admin.sessions' == navigationPage" class="container">
      ADMIN.SESSIONS
      <Legend></Legend>

      <WeekNavigation />

      <SessionTable />
    </div>
    <div v-else-if="user.isAdmin && 'admin.users' == navigationPage">
      <UserList :lang="lang" :configs="configs" :user="user"/>
    </div>
    <div v-else-if="user.isAdmin && 'admin.settings' == navigationPage">
      <Settings
        :lang="lang"
        :configs="configs"
        :user="user"
        @setting-updated="updateSetting"/>
    </div>
    <div v-else class="container">
      ELSE
      <Legend></Legend>

      <WeekNavigation />

      <SessionTable />
    </div>
  </div>
</template>

<script>
export default {
  data() {
    const self = this;
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
      user: {
        loggedIn: false,
        id: null,
        name: "",
        isAdmin: false,
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
      navigationPage: "start",

      __weekStartTimestamp: null,
      bookedTimestamps: {},
      calendar: {/*
        show: false,
        shownMonth: (new Date()).getMonth(),
        shownYear: (new Date()).getFullYear(),
        selectedWeek: this.selectedWeekFromDate((new Date()).getDate()),
        selectedMonth: (new Date()).getMonth(),
        selectedYear: (new Date()).getFullYear(),
        mobileSelectedDay: 0,*/
      },
      eventMarkingList: document.php.eventMarkingList,
      configs: document.php.configs,
      lang: document.php.lang,
    }
  },
  mounted() {
    this.getLoggedInUser();

    document.php.pageLoadErrorMessageList.forEach(messageObject => {
      this.message(messageObject.message, messageObject.status, 0);
    });
  },
  methods: {
    getLoggedInUser() {
      axios.get(this.configs.baseURL + "users/authentication/login")
      .then((response) => {
        var user_id = response.data;
        if (user_id) {
          this.user.id = parseInt(user_id, 10);
          this.user.loggedIn = true;
          
          axios.get(this.configs.baseURL + "users/" + this.user.id)
          .then((response) => {
            this.user.name = response.data.firstname + " " + response.data.lastname;
          });

          axios.get(this.configs.baseURL + "users/admin")
          .then((response) => {
            this.user.isAdmin = response.data;
          });
        }
      });
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
    updateSetting(key) {
      axios.get(this.configs.baseURL + "settings/" + key)
      .then((response) => {
        this.configs[key] = response.data.value;
      });
    },
  },
}
</script>

<style scoped>
header {
  line-height: 1.5;
}

.logo {
  display: block;
  margin: 0 auto 2rem;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    padding-right: calc(var(--section-gap) / 2);
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }
}
</style>
