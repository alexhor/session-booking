<script setup>
defineEmits(['message']);

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
    <div style="padding-bottom: 50px;">
        <select v-model="selectedEmailTemplate" @change="templateSelected" class="form-select" style="display: inline-block; width: auto; margin-right: 30px;">
            <option v-for="templateName in emailTemplateList" :value="templateName">{{ lang["Emails." + templateName + ".label"] }}</option>
        </select>
        <select v-model="selectedLanguage" @change="templateSelected" class="form-select" style="display: inline-block; width: auto; margin-right: 30px;">
            <option v-for="language in configs.supportedLocales" :value="language" :selected="selectedLanguage == language">{{ language }}</option>
        </select>
        <button class="btn btn-primary" v-on:click="saveDesign" style="margin-right: 15px;">{{ lang['Admin.save'] }}</button>
        <button class="btn btn-danger" v-on:click="resetDesign">{{ lang['Admin.reset'] }}</button>
    </div>

    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text" style="border-radius: var(--bs-border-radius) 0 0 var(--bs-border-radius);">{{ lang['Admin.subject'] }}:</span>
        </div>
        <input class="form-control" style="max-width: 500px;" v-model="emailTemplateSubject" :disabled="emailTemplateSubjectDisabled">
    </div>

    <EmailEditor
        ref="emailEditor"
        v-on:load="editorLoaded"
        v-on:ready="editorReady"
    />
</template>

<script>
import { EmailEditor } from 'vue-email-editor';

export default {
    components: {
      EmailEditor,
    },
    data() {
        return {
            selectedEmailTemplate: "magicLink",
            selectedLanguage: this.$props.configs.defaultLocale,
            emailTemplateSubject: "",
            emailTemplateSubjectDisabled: true,
            emailTemplateList: [
                'magicLink',
                'sessionBooked',
                'sessionBookingCanceled',
                'sessionBookingReminder',
            ]
        };
    },
    methods: {
        templateSelected() {
            this.loadSelectedTemplate();
        },
        editorLoaded() {
            document.querySelector(".unlayer-editor > iframe").style.height = "768px";
            this.loadSelectedTemplate();
        },
        saveDesign() {
            let data = {
                subject: this.emailTemplateSubject,
            };
            const jsonPromise = new Promise((resolve, reject) => {
                this.$refs.emailEditor.editor.saveDesign((json) => {
                    data['json'] = JSON.stringify(json);
                    resolve();
                });
            });
            const htmlPromise = new Promise((resolve, reject) => {
                this.$refs.emailEditor.editor.exportHtml((html) => {
                    data['html'] = html.html;
                    resolve();
                });
            });
            Promise.all([jsonPromise, htmlPromise]).then((values) => {
                console.log(JSON.stringify(data['json']));
                console.log(data['html']);
                
                axios.put(this.$props.configs.baseURL + "settings/email/" + this.selectedLanguage + "/" + this.selectedEmailTemplate, data)
                .then((response) => {
                    this.$emit('message', response.data, response.status);
                });
            });
        },
        resetDesign() {
            this.$refs.emailEditor.editor.loadBlank();
            axios.delete(this.$props.configs.baseURL + "settings/email/" + this.selectedLanguage + "/" + this.selectedEmailTemplate)
            .then((response) => {
                this.loadSelectedTemplate();
            });
        },
        loadSelectedTemplate() {
            this.$refs.emailEditor.editor.loadBlank();
            axios.get(this.$props.configs.baseURL + "settings/email/" + this.selectedLanguage + "/" + this.selectedEmailTemplate)
            .then((response) => {
                this.emailTemplateSubject = response.data.subject;
                this.emailTemplateSubjectDisabled = response.data.subjectDisabled;
                this.$refs.emailEditor.editor.loadDesign(response.data);
            });
        },
    },
};
</script>

<style lang="css" scoped>
.unlayer-editor > iframe {
    min-height: 500px !important;
}
</style>