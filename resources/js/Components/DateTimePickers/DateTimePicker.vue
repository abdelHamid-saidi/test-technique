<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import moment from "moment";
import CalendarDateTimePicker from "./Partials/CalendarDateTimePicker.vue";

const format = "DD MMM YY HH:mm";

const emit = defineEmits(["update:modelValue", "reset"]);

const props = defineProps({
    modelValue: {
        type: [Object, String, Date],
        default: null,
    },
    label: {
        type: String,
        default: "Date and time"
    },
    name: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: "Select a date and time"
    },
    disabled: {
        type: Boolean,
        default: false
    },
    minDate: {
        type: Date,
        default: undefined
    },
    maxDate: {
        type: Date,
        default: undefined
    },
    type: {
        type: String,
        validator: (val) => ["date", "datetime", "time"].includes(val),
        default: "datetime",
    },
    required: {
        type: Boolean,
        default: false
    }
});

// État du popup
const showPopup = ref(false);

// Formater la date pour l'affichage
const formatDate = (date) => {
    if (!date) return "";
    // Convertir en moment si ce n'est pas déjà le cas
    const momentDate = moment.isMoment(date) ? date : moment(date);
    return momentDate.format(format);
};

// Valeur formatée pour l'affichage
const displayValue = computed(() => {
    if (!props.modelValue) {
        return props.placeholder;
    }
    
    return formatDate(props.modelValue);
});

// Gérer le changement de date
const onDateChange = (date) => {
    const momentDate = date ? moment(date) : null;
    emit("update:modelValue", momentDate);
    showPopup.value = false;
};

// Gérer la mise à jour de l'heure (sans fermer le popup)
const onTimeUpdate = (date) => {
    const momentDate = date ? moment(date) : null;
    emit("update:modelValue", momentDate);
    // Ne pas fermer le popup lors de la modification de l'heure
};

// Gérer la réinitialisation
const onReset = () => {
    emit("update:modelValue", null);
    emit("reset");
    showPopup.value = false;
};

// Basculer l'état du popup
const togglePopup = () => {
    if (!props.disabled) {
        showPopup.value = !showPopup.value;
    }
};

// Fermer le popup
const closePopup = () => {
    showPopup.value = false;
};

// Convertir le modelValue en format attendu par CalendarDateTimePicker
const calendarValue = computed(() => {
    if (!props.modelValue) return null;
    // Si c'est déjà un objet Moment, utiliser toDate()
    if (moment.isMoment(props.modelValue)) {
        return props.modelValue.toDate();
    }
    // Sinon, convertir en Date
    return moment(props.modelValue).toDate();
});

// Gestionnaire d'événements global pour fermer le popup
const handleClickOutside = (event) => {
    const component = document.querySelector(`[data-date-time-picker="${props.name}"]`);
    if (component && !component.contains(event.target)) {
        showPopup.value = false;
    }
};

// Ajouter/supprimer le gestionnaire d'événements
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="w-full relative" :data-date-time-picker="name">
        <!-- Label -->
        <label :for="name" class="font-medium text-sm text-gray-700 block mb-2">
            <span v-if="label">{{ label }}</span>
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>

        <!-- Bouton principal -->
        <button
            type="button"
            @click="togglePopup"
            :disabled="disabled"
            :class="[
                'w-full px-3 py-2 h-12 text-left border border-gray-300 rounded-md shadow-sm transition-colors',
                'focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500',
                {
                    'opacity-50 cursor-not-allowed': disabled,
                    'border-indigo-300': showPopup
                }
            ]"
        >
            <div class="flex items-center justify-between">
                <span :class="{ 'text-gray-500': !props.modelValue }">
                    {{ displayValue }}
                </span>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </button>

        <!-- Popup calendrier -->
        <div 
            v-if="showPopup" 
            class="absolute top-full left-0 mt-1 z-50 w-full sm:w-auto"
            @mousedown.prevent
            @click.stop
        >
            <div class="w-full sm:w-auto">
                <CalendarDateTimePicker
                    :model-value="calendarValue"
                    :min-date="minDate"
                    :max-date="maxDate"
                    :disabled="disabled"
                    :type="type"
                    @update:model-value="onDateChange"
                    @update:time="onTimeUpdate"
                    @reset="onReset"
                    @close="closePopup"
                />
            </div>
        </div>

    </div>
</template>
