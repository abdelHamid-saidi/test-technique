<script setup>
import { computed, ref } from "vue";
import moment from "moment";
import Button from "../Button";
import Calendar from "./Partials/CalendarPopup";

defineEmits(["update:modelValue"]);

const props = defineProps({
    modelValue: {
        type: Object,
        validator: moment.isMoment,
    },
    label: String,
    type: {
        type: String,
        validator: (val) => ["date", "datetime", "time"].includes(val),
        default: "datetime",
    },
});

// Represents whether or not the datepicker popup is open
const showPopup = ref(false);

const onBlur = (event) => {
    // Check if the clicked item was inside the current component
    if (!event.currentTarget.contains(event.relatedTarget)) {
        showPopup.value = false;
    } else {
        event.currentTarget.focus();
    }
};

// Whether or not time or date fields need to be shown
const hasTime = computed(() => props.type !== "date");
const hasDate = computed(() => props.type !== "time");

// Format to be used to show the selected value
const format = computed(() => {
    switch (props.type) {
        case "time":
            return "HH:mm";
        case "date":
            return "DD/MM/YYYY";
        default:
            return "HH:mm DD/MM/YYYY"; // datetime
    }
});
</script>

<template></template>

<style lang="postcss" scoped></style>
