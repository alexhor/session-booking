<script setup>
defineEmits(['updated'])

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
  adminView: {
    type: Boolean,
    default: false,
  },
  booking: {
    type: [Object, null],
    required: true,
  },
  timestamp: {
    type: Number,
    required: true,
  },
  userList: {
    type: Object,
    required: true,
  }
});
</script>

<template>
    <button v-if="user.id !== null && booking !== null && user.id === booking.user_id" class="own" @click="deleteBookedSession()" :class="{ no_background_image: hasTitleOrDescription() }">
        <b v-if="booking.title">{{ booking.title }}</b><br>
        <small v-if="booking.description">{{ booking.description }}</small>
    </button>
    <span v-else-if="null != booking && adminView"
        class="booked"
        @click="showBookingDetails()"
        :class="{ no_background_image: hasTitleOrDescription() }">
        <b v-if="booking.title">{{ booking.title }}</b><br>
        <small v-if="booking.description">{{ booking.description }}</small>
        <div class="user-info" v-if="booking.user_id && ">{{ userList[booking.user_id].firstname + " " + userList[booking.user_id].lastname }}</div>
        <div class="user-info" v-if="booking.user_id">{{ userList[booking.user_id].email }}</div>
    </span>
    <span v-else-if="null !== booking" class="booked" :class="{ no_background_image: hasTitleOrDescription() }">
        <b v-if="booking.title">{{ booking.title }}</b><br>
        <small v-if="booking.description">{{ booking.description }}</small>
    </span>
    <span v-else-if="null == user.id" class="free" data-bs-toggle="modal" data-bs-target="#registrationModal"></span>
    <button v-else class="free" @click="bookSession()"></button>
</template>

<script>
export default {
    methods: {
        bookSession() {
            if (!this.$props.user.loggedIn) return;

            axios.post(this.configs.baseURL + "sessions/bookings", {
                "user_id": this.user.id,
                "start_time": this.$props.timestamp,
            })
            .then((response) => {
                this.$emit('updated');
            });
        },
        deleteBookedSession() {
            if (!this.$props.user.loggedIn || !this.$props.booking) return;
            
            axios.delete(this.configs.baseURL + "sessions/bookings/" + this.$props.booking.id)
            .then((response) => {
                this.$emit('updated');
            });
        },
        hasTitleOrDescription() {
            if (!this.$props.booking) return false;

            if (typeof this.$props.booking.title !== "undefined" && this.$props.booking.title !== null && this.$props.booking.title != '') return true;
            else if (typeof this.$props.booking.description !== "undefined" && this.$props.booking.description != null && this.$props.booking.description != '') return true;
            else return false;
        },
        showBookingDetails() {

        },
    }
}
</script>
