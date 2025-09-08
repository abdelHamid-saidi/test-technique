<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import moment from "moment";
import CalendarRangePicker from "./Partials/CalendarRangePicker.vue";

const format = "DD MMM YY";

const emit = defineEmits(["update:modelValue", "reset"]);

const props = defineProps({
    modelValue: {
        type: Array,
        validator: (value) =>
            value?.every((date) => !date || moment.isMoment(date)),
        default: [null, null],
    },
    label: {
        type: String,
        default: "Plage de dates"
    },
    name: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: "Sélectionner une plage de dates"
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
    }
});

// État du popup
const showPopup = ref(false);

// Formater les dates pour l'affichage en anglais
const formatDate = (date) => {
    if (!date) return "";
    return moment(date).format(format);
};

// Valeur formatée pour l'affichage
const displayValue = computed(() => {
    const startDate = props.modelValue?.[0];
    const endDate = props.modelValue?.[1];
    
    if (!startDate && !endDate) {
        return props.placeholder;
    }
    
    if (startDate && endDate) {
        return `${formatDate(startDate)} – ${formatDate(endDate)}`;
    }
    
    if (startDate) {
        return `${formatDate(startDate)} – ...`;
    }
    
    if (endDate) {
        return `... – ${formatDate(endDate)}`;
    }
    
    return props.placeholder;
});

// Gérer le changement de plage de dates
const onRangeChange = (range) => {
    const startDate = range.startDate ? moment(range.startDate) : null;
    const endDate = range.endDate ? moment(range.endDate) : null;
    
    emit("update:modelValue", [startDate, endDate]);
    showPopup.value = false;
};

// Gérer la réinitialisation
const onReset = () => {
    emit("update:modelValue", [null, null]);
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


// Convertir le modelValue en format attendu par CalendarPopup
const calendarValue = computed(() => {
    return {
        startDate: props.modelValue?.[0]?.toDate() || null,
        endDate: props.modelValue?.[1]?.toDate() || null
    };
});

// Gestionnaire d'événements global pour fermer le popup
const handleClickOutside = (event) => {
    const component = document.querySelector('[data-date-range-picker]');
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
    <div class="w-full relative" data-date-range-picker>
        <!-- Label -->
        <label :for="name" class="font-medium text-sm text-gray-700 block mb-2">
            <span v-if="label">{{ label }}</span>
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
                <span :class="{ 'text-gray-500': !props.modelValue?.[0] && !props.modelValue?.[1] }">
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
            class="absolute top-full left-0 mt-1 z-50"
            @mousedown.prevent
            @click.stop
        >
            <CalendarRangePicker
                :model-value="calendarValue"
                :min-date="minDate"
                :max-date="maxDate"
                :disabled="disabled"
                @update:model-value="onRangeChange"
                @reset="onReset"
                @close="closePopup"
            />
        </div>

    </div>
</template>
