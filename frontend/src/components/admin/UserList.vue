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
  user: {
    type: Object,
    required: true,
  },
});
</script>

<template>
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th>{{ lang['Validation.user.id.label'] }}</th>
                <th>{{ lang['Validation.user.firstname.label'] }}</th>
                <th>{{ lang['Validation.user.lastname.label'] }}</th>
                <th>{{ lang['Validation.user.email.label'] }}</th>
                <th>{{ lang['Admin.admin'] }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in userList" :id="'user-row-' + user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.firstname }}</td>
                <td>{{ user.lastname }}</td>
                <td>{{ user.email }}</td>
                <td>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_admin" :checked="user.groups.includes('admin')" @input="toggleAdminGroupForUser(user)">
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-danger" @click="promptReallyDeleteUser(user)">{{ lang['Admin.delete'] }}</button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    mounted() {
        this.getUserList();
    },
    data() {
        return {
            userList: [],
        };
    },
    methods: {
        getUserList() {
            axios.get(this.configs.baseURL + "users")
            .then((response) => {
                this.userList = response.data;
            });
        },
        promptReallyDeleteUser(user) {
            var confirmMessage = this.$props.lang["Admin.really_delete_user"];
            confirmMessage = confirmMessage.replace('{user}', user.firstname + ' ' + user.lastname).replace('{email}', user.email);
            if (confirm(confirmMessage)) {
                axios.delete(this.configs.baseURL + "users/" + user.id)
                .then((response) => {
                    this.getUserList();
                });
            }
        },
        toggleAdminGroupForUser(user) {
            if (this.userId == user.id) {
                if (!confirm(this.$props.lang["Admin.really_remove_self_from_admin"])) {
                    document.querySelector("#user-row-" + user.id + " input[name=is_admin]").checked = true;
                    return;
                }
            }
            if (user.groups.includes('admin')) {
                axios.delete(this.configs.baseURL + 'users/' + user.id + '/groups/admin')
                .then((response) => {
                    this.getUserList();
                });
            }
            else {
                axios.put(this.configs.baseURL + 'users/' + user.id + '/groups/admin')
                .then((response) => {
                    this.getUserList();
                });
            }
        },
    }
}
</script>
