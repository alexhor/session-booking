<script setup>
defineEmits(['registered', 'message']);

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
</script>

<template>
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="registrationModalLabel">{{ lang['Views.register_new_account'] }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ lang['Views.close'] }}"></button>
                </div>
                <form action="#">
                    <div class="modal-body">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">{{ lang['Views.already_have_an_account_then_login'] }}</a>
                        <br><br>
                        <div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">{{ lang['Validation.user.firstname.label'] }}:</label>
                                <input type="text" class="form-control" name="firstname" v-model="registrationData.firstname" required>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">{{ lang['Validation.user.lastname.label'] }}:</label>
                                <input type="text" class="form-control" name="lastname" v-model="registrationData.lastname" required>
                            </div>
                            <div class="mb-3">
                                <label for="recipient-name" class="col-form-label">{{ lang['Validation.user.email.label'] }}:</label>
                                <input type="email" class="form-control" email="email" v-model="registrationData.email" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="register()">{{ lang['Views.register'] }}</button>
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
            registrationData: {
                firstname: "",
                lastname: "",
                email: "",
            },
        };
    },
    methods: {
        register() {
            axios.post(this.configs.baseURL + "users", {
                firstname: this.registrationData.firstname,
                lastname: this.registrationData.lastname,
                email: this.registrationData.email,
            })
            .then((response) => {
                if ("data" in response) {
                    if (typeof response.data !== 'object') this.$emit('message', response.data, response.status);
                    else if ("message" in response.data) this.$emit('message', response.data.message, response.status);
                }
                else this.$emit('message', response.statusText, response.status);

                this.$emit('registered', this.registrationData.email);
            });
        },
    }
}
</script>
