<script setup>
import Navbar from './components/Navbar.vue'
</script>

<template>
  <Navbar :lang="lang" :configs="configs" :user="user"/>
</template>

<script>
export default {
  data() {
    /*
    axios.interceptors.response.use(function (response) {
      return response;
    }, function (e) {
      if (401 == e.status) {
        this.logout();
      }
      else {
        this.message(Object.values(e.response.data.messages).join("\n"), e.response.data.status);
      }
      
      return Promise.reject(e);
    });
    */

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
      __weekStartTimestamp: null,
      config: {
        session: {
          interval: 60 * 60,
          offset: 0,
        },
      },
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
