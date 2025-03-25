<script setup>
defineEmits(['created']);

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
  timestamp: {
    type: Number,
    required: true,
  },
});
</script>

<template>
    <div class="modal fade" id="newSessionModal" tabindex="-1" aria-labelledby="sessionDetailsLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="sessionDetailsLabel">{{ lang['Admin.session_details.add'] }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ lang['Views.close'] }}"></button>
                </div>
                <form action="#">
                    <div class="modal-body">
                        <div>
                            <div class="mb-3">
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
                        <button type="button" class="btn btn-primary" @click="bookSession()">{{ lang['Admin.session_details.add'] }}</button>
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
            const date = new Date(this.$props.timestamp * 1000);
            return date.toLocaleString();
        },
    },
    data() {
        return {
            sessionDetails: {},
        };
    },
    methods: {
        bookSession() {
            this.sessionDetails.user_id = this.$props.user.id;
            this.sessionDetails.title_is_public = this.sessionDetails.title_is_public == true ? 1 : 0;
            this.sessionDetails.description_is_public = this.sessionDetails.description_is_public == true ? 1 : 0;
            this.sessionDetails.start_time = this.$props.timestamp;
            
            axios.post(this.$props.configs.baseURL + "sessions/bookings", this.sessionDetails)
            .then((response) => {
                this.$emit('created');
            });
        },
    }
}
</script>
