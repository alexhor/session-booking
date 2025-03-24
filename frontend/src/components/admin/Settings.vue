<script setup>
defineEmits(['settingUpdated']);

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
                <th style="width: 300px">{{ lang['Admin.setting'] }}</th>
                <th style="width: 800px">{{ lang['Admin.value'] }}</th>
                <th style="width: 300px"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="setting in settingsList">
                <td>{{ setting.key }}</td>
                <td>
                    <div v-if="setting.validation == 'timestamp'">
                        <input v-if="!setting.value.usenow" type="date" v-model="setting.value.date" class="form-control" style="display: inline-block; width: auto;">
                        <span class="form-check form-control" style="display: inline-block; width: auto; padding-left: 50px;">
                            <input :id="setting.key + '_usenow'"type="checkbox" v-model="setting.value.usenow" class="form-check-input">
                            <label :for="setting.key + '_usenow'"class="form-check-label">Use current time</label>
                        </span>
                    </div>
                    <input v-else-if="setting.validation == 'integer'" v-model="setting.value" class="form-control" type="number">
                    <input v-else-if="typeof setting.validation !== 'object'" v-model="setting.value" class="form-control">
                    <select v-else v-model="setting.value" class="form-select">
                        <option v-for="option in setting.validation" :selected="option == setting.origValue">{{ option }}</option>
                    </select>
                </td>
                <td>
                    <button v-if="JSON.stringify(setting.value) != JSON.stringify(setting.origValue)" type="button" class="btn btn-success" @click="saveSetting(setting)">{{ lang['Admin.save'] }}</button>
                    <button v-if="JSON.stringify(setting.value) != JSON.stringify(setting.origValue)" type="button" class="btn btn-warning" @click="setting.value = setting.origValue">{{ lang['Admin.discard'] }}</button>
                    <button type="button" class="btn btn-danger" @click="clearSetting(setting)">{{ lang['Admin.reset'] }}</button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>
export default {
    mounted() {
        this.getSettingsList();
    },
    data() {
        return {
            __settingsList: {},
            __propsConfigsBefore: [],
        };
    },
    computed: {
        settingsList() {
            Object.keys(this.$props.configs).forEach((key, i) => {
                if (key in this.__settingsList) {
                    if (this.$props.configs[key] == this.__propsConfigsBefore[key]) return;

                    this.__settingsList[key].value = this.$props.configs[key];
                    this.__settingsList[key].origValue = this.$props.configs[key];

                    if (this.__settingsList[key].validation == 'timestamp') {
                        var tmpValue = {
                            'usenow': false,
                        };
                        if (this.__settingsList[key].value == 'now') {
                            tmpValue.usenow = true;
                        }
                        else {
                            const date = new Date(this.__settingsList[key].value * 1000);
                            tmpValue.date = date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                        }
                        this.__settingsList[key].value = JSON.parse(JSON.stringify(tmpValue));

                        tmpValue = {
                            'usenow': false,
                        };
                        if (this.__settingsList[key].origValue == 'now') {
                            tmpValue.usenow = true;
                        }
                        else {
                            const date = new Date(this.__settingsList[key].origValue * 1000);
                            tmpValue.date = date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                        }
                        this.__settingsList[key].origValue = JSON.parse(JSON.stringify(tmpValue));
                    }

                    if ('App.title' == key) {
                        document.title = this.settingsList[key].origValue;
                    }
                }
            });

            this.__propsConfigsBefore = JSON.parse(JSON.stringify(this.$props.configs));

            return this.__settingsList;
        },
    },
    methods: {
        getSettingsList() {
            axios.get(this.$props.configs.baseURL + 'settings/validation')
            .then((response) => {
                this.__settingsList = {};

                Object.keys(response.data).forEach((key, i) => {
                    const element = response.data[key];
                    element.origValue = element.value;
                    if (element.validation == 'timestamp') {
                        const tmpValue = {
                            'usenow': false,
                        };
                        if (element.value == 'now') {
                            tmpValue.usenow = true;
                        }
                        else {
                            const date = new Date(element.value * 1000);
                            tmpValue.date = date.getFullYear() + '-' + String(date.getMonth()+1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                        }
                        element.value = JSON.parse(JSON.stringify(tmpValue));
                        element.origValue = JSON.parse(JSON.stringify(tmpValue));
                    }

                    this.__settingsList[key] = element;
                });
            });
        },
        clearSetting(setting) {
            var confirmMessage = this.$props.lang['Admin.really_clear_setting'];
            confirmMessage = confirmMessage.replace('{setting}', setting.key);
            if (confirm(confirmMessage)) {
                axios.delete(this.$props.configs.baseURL + "settings/" + setting.key)
                .then((response) => {
                    this.updateConfigValue(setting);
                });
            }
        },
        saveSetting(setting) {
            var value = setting.value;
            if ('timestamp' == setting.validation) {
                if (true == value.usenow) {
                    value = 'now';
                }
                else {
                    const date = new Date(value.date + ' 0:0');
                    value = date.valueOf() / 1000;
                }
            }
            axios.put(this.$props.configs.baseURL + 'settings/' + setting.key, { 'value': value })
            .then((response) => {
                this.$emit('settingUpdated', setting.key);
            });
        },
    }
}
</script>
