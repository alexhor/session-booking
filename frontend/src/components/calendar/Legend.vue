<script setup>
defineProps({
    eventMarkingList: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="legend-wrapper">
        <span class="legend-item" v-for="marking in eventMarkingList" :class="marking.cssClasses">{{ marking.title }}<small class="legend-description">{{ marking.description }}</small></span>
    </div>
</template>
