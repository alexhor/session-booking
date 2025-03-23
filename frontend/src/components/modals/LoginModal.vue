<script setup>
defineProps({
  lang: {
    type: Object,
    required: true,
  },
  configs: {
    type: Object,
    required: true,
  },
});

const login = (email) => {
    document.getElementById("email-input").value = email;
    document.getElementById("login-form").submit();
};

defineExpose({ login });
</script>


<template>
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form :action="configs.baseURL + 'users/authentication'" method="POST" id="login-form">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="loginModalLabel">{{ lang['Views.login'] }}</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ lang['Views.close'] }}"></button>
                    </div>
                    <div class="modal-body">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#registrationModal">{{ lang['Views.no_account_yet_then_register'] }}</a>
                        <br><br>
                        <div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">{{ lang['Validation.user.email.label'] }}:</label>
                                <input id="email-input" type="text" class="form-control" name="email" v-model="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ lang['Views.request_login_link'] }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>


<script>
export default {
    data() {
        return {
            email: "",
        };
    },
}
</script>
