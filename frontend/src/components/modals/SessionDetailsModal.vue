<script setup>
defineEmits(['deleted']);

defineProps({
  lang: {
    type: Object,
    required: true,
  },
  configs: {
    type: Object,
    required: true,
  },
  userList: {
    type: Object,
    required: true,
  },
  sessionDetails: {
    type: [Object, null],
    required: true,
  }
});
</script>

<template>
    <div class="modal fade" id="sessionDetailsModal" tabindex="-1" aria-labelledby="sessionDetailsLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="sessionDetailsLabel">{{ lang['Admin.session_details.details'] }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" :aria-label="lang['Views.close']"></button>
                </div>
                <form action="#">
                    <div class="modal-body">
                        <div v-if="sessionDetails">
                            <div class="mb-3">
                                <label class="col-form-label">{{ lang['Admin.session_details.user'] }}:</label>
                                <input v-if="sessionDetails.user_id" type="text" class="form-control" name="user_name" :value="userList[sessionDetails.user_id].firstname + ' ' + userList[sessionDetails.user_id].lastname" disabled>
                                <input v-if="sessionDetails.user_id" type="text" class="form-control" name="user_email" :value="userList[sessionDetails.user_id].email" disabled>
                            </div>
                            <div class="mb-3">
                                <!-- TODO: load user on demand (and maybe chache?) -->
                                <label for="time" class="col-form-label">{{ lang['Admin.session_details.time'] }}:</label>
                                <input type="text" class="form-control" name="time" :value="startTimeFormatted" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="title" class="col-form-label">{{ lang['Admin.session_details.title'] }}:</label>
                                <input type="text" class="form-control" name="title" v-model="sessionDetails.title">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="title_is_public" v-model="sessionDetails.title_is_public">
                                <label for="title_is_public" class="form-check-label">{{ lang['Admin.session_details.title_is_public'] }}</label>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="col-form-label">{{ lang['Admin.session_details.description'] }}:</label>
                                <textarea class="form-control" name="description" v-model="sessionDetails.description"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="description_is_public" v-model="sessionDetails.description_is_public">
                                <label for="description_is_public" class="form-check-label">{{ lang['Admin.session_details.description_is_public'] }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" @click="deleteBookedSession()">{{ lang['Admin.session_details.delete'] }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    computed: {
        startTimeFormatted() {
            const date = new Date(this.$props.sessionDetails.start_time * 1000);
            return date.toLocaleString();
        },
    },
    methods: {
        deleteBookedSession() {
            if (!this.$props.sessionDetails) return;

            axios.delete(this.configs.baseURL + "sessions/bookings/" + this.$props.sessionDetails.id)
            .then((response) => {
                this.$emit('deleted');
            });
        },
    }
}
</script>
