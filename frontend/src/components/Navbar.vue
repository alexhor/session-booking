<script setup>
import RegistrationModal from './modals/RegistrationModal.vue'

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
})
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" :href="configs.baseURL">{{ configs.title }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" :aria-label="lang['Views.toggle_navigation']">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <span class="me-auto"></span>
                <ul v-if="user.loggedIn" class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link disabled">{{ user.name }}</a></li>
                    <li class="nav-item" v-if="user.isAdmin"><a class="nav-link" :href="configs.baseURL + 'admin'">{{ lang['Admin.admin'] }}</a></li>
                    <li class="nav-item"><a class="nav-link" @click="logout()" href="#">{{ lang['Views.logout'] }}</a></li>
                </ul>
                <ul v-else class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="modal" data-bs-target="#registrationModal" href="#">{{ lang['Views.register'] }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="modal" data-bs-target="#loginModal" href="#">{{ lang['Views.login'] }}</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <RegistrationModal @registered="requestLoginLinkAfterRegistration" :configs="configs" :lang="lang" />
</template>

<script>
export default {
    methods: {
        logout() {
            window.location.replace(this.configs.baseURL + "users/authentication/logout");
        },
        requestLoginLinkAfterRegistration(email) {

            console.log(email + "");
            return;
            // Redirect to login link fetching
            Vue.nextTick(function () {
                document.getElementById("login-form").submit();
            });
        }
        
    }
}
</script>
