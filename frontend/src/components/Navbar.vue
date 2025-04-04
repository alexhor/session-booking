<script setup>
import RegistrationModal from './modals/RegistrationModal.vue';
import LoginModal from './modals/LoginModal.vue';
import { ref, useTemplateRef } from 'vue';

defineEmits(['message', 'navigate']);

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
  navigationPage: {
    type: String,
    required: true,
  }
});

const loginModalRef = useTemplateRef('loginModalRef');
const requestLoginLink = (email) => {
    loginModalRef.value.login(email);
};
</script>

<template>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" @click="$emit('navigate', 'start')">{{ configs.title }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" :aria-label="lang['Views.toggle_navigation']">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <span class="me-auto"></span>
                <ul v-if="user.loggedIn" class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link disabled">{{ user.name }}</a></li>
                    <li class="nav-item" v-if="user.isAdmin"><a class="nav-link" :class="{ active: 'admin.sessions' == navigationPage }" @click="$emit('navigate', 'admin.sessions')">{{ lang['Admin.sessions'] }}</a></li>
                    <li class="nav-item" v-if="user.isAdmin"><a class="nav-link" :class="{ active: 'admin.users' == navigationPage }" @click="$emit('navigate', 'admin.users')">{{ lang['Admin.users'] }}</a></li>
                    <li class="nav-item" v-if="user.isAdmin"><a class="nav-link" :class="{ active: 'admin.settings' == navigationPage }" @click="$emit('navigate', 'admin.settings')">{{ lang['Admin.settings'] }}</a></li>
                    <li class="nav-item" v-if="user.isAdmin"><a class="nav-link" :class="{ active: 'admin.emails' == navigationPage }" @click="$emit('navigate', 'admin.emails')">{{ lang['Admin.emails'] }}</a></li>
                    <li class="nav-item"><a class="nav-link" @click="logout()" href="#">{{ lang['Views.logout'] }}</a></li>
                </ul>
                <ul v-else class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="modal" data-bs-target="#registrationModal" href="#">{{ lang['Views.register'] }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="modal" data-bs-target="#loginModal" href="#">{{ lang['Views.login'] }}</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <RegistrationModal @registered="requestLoginLink" :configs="configs" :lang="lang" @message="emitMessage" />
    <LoginModal :configs="configs" :lang="lang" ref="loginModalRef" />
</template>

<script>
export default {
    methods: {
        logout() {
            window.location.replace(this.configs.baseURL + "users/authentication/logout");
        },
        requestLoginLinkAfterRegistration(email) {
            console.log(this.$refs['login-modal-ref'])
            return;
            const loginModalRef = ref('loginModalRef');
            console.log(loginModalRef);
            loginModalRef.login(email);
        },
        emitMessage(text, status) {
            this.$emit('message', text, status);
        },
    }
}
</script>
