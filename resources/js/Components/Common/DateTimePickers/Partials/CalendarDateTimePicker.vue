<script setup>
import { ref, computed, watch } from "vue";
import moment from "moment";

const props = defineProps({
    modelValue: {
        type: Date,
        default: null
    },
    minDate: {
        type: Date,
        default: undefined
    },
    maxDate: {
        type: Date,
        default: undefined
    },
    disabled: {
        type: Boolean,
        default: false
    },
    type: {
        type: String,
        validator: (val) => ["date", "datetime", "time"].includes(val),
        default: "datetime",
    }
});

const emit = defineEmits(["update:modelValue", "update:time", "close", "reset"]);

// État local pour la date sélectionnée
const selectedDate = ref(props.modelValue ? moment(props.modelValue) : null);

// État pour l'heure sélectionnée
const selectedTime = ref({
    hours: selectedDate.value ? selectedDate.value.hour() : 0,
    minutes: selectedDate.value ? Math.round(selectedDate.value.minute() / 5) * 5 : 0
});

// Surveiller les changements de la valeur externe
watch(() => props.modelValue, (newValue) => {
    if (newValue) {
        selectedDate.value = moment.isMoment(newValue) ? newValue : moment(newValue);
        selectedTime.value = {
            hours: selectedDate.value.hour(),
            minutes: Math.round(selectedDate.value.minute() / 5) * 5
        };
    } else {
        selectedDate.value = null;
        selectedTime.value = { hours: 0, minutes: 0 };
    }
}, { immediate: true });

// Obtenir le mois et l'année actuels
const currentMonth = ref(selectedDate.value || moment());
const currentYear = ref(currentMonth.value.year());
const currentMonthIndex = ref(currentMonth.value.month());

// Générer les jours pour un mois donné avec les jours du mois précédent/suivant
const generateDaysForMonth = (year, month) => {
    const startOfMonth = moment().year(year).month(month).startOf('month');
    const endOfMonth = moment().year(year).month(month).endOf('month');
    const days = [];
    
    // Ajouter les jours du mois précédent
    const firstDayOfWeek = startOfMonth.day();
    const prevMonth = startOfMonth.clone().subtract(1, 'month');
    const daysInPrevMonth = prevMonth.daysInMonth();
    
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        const date = prevMonth.clone().date(day);
        days.push({
            date: day,
            moment: date,
            isCurrentMonth: false,
            isSelected: false,
            isToday: false,
            isDisabled: true
        });
    }
    
    // Ajouter tous les jours du mois actuel
    for (let day = 1; day <= endOfMonth.date(); day++) {
        const date = moment().year(year).month(month).date(day);
        const today = moment();
        const minDate = props.minDate ? moment(props.minDate) : null;
        const maxDate = props.maxDate ? moment(props.maxDate) : null;
        
        const isSelected = selectedDate.value && date.isSame(selectedDate.value, 'day');
        
        days.push({
            date: day,
            moment: date,
            isCurrentMonth: true,
            isSelected: isSelected,
            isToday: date.isSame(today, 'day'),
            isDisabled: (minDate && date.isBefore(minDate, 'day')) || 
                       (maxDate && date.isAfter(maxDate, 'day'))
        });
    }
    
    // Ajouter les jours du mois suivant pour compléter la grille
    const remainingDays = 42 - days.length; // 6 semaines * 7 jours
    const nextMonth = endOfMonth.clone().add(1, 'month');
    
    for (let day = 1; day <= remainingDays; day++) {
        const date = nextMonth.clone().date(day);
        days.push({
            date: day,
            moment: date,
            isCurrentMonth: false,
            isSelected: false,
            isToday: false,
            isDisabled: true
        });
    }
    
    return days;
};

// Générer les jours pour le mois actuel
const currentMonthDays = computed(() => generateDaysForMonth(currentYear.value, currentMonthIndex.value));

// Noms des jours de la semaine en anglais
const weekDays = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];

// Noms des mois en anglais
const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

// Sélectionner une date
const selectDate = (day) => {
    if (day && !day.isDisabled && day.isCurrentMonth) {
        // Créer une nouvelle date avec l'heure sélectionnée
        const newDate = day.moment.clone()
            .hour(selectedTime.value.hours)
            .minute(selectedTime.value.minutes)
            .second(0)
            .millisecond(0);
        
        selectedDate.value = newDate;
        
        // Émettre la date complète
        emit("update:modelValue", newDate.toDate());
        
        // Fermer le popup après un court délai
        setTimeout(() => {
            emit("close");
        }, 100);
    }
};

// Mettre à jour l'heure
const updateTime = (field, value) => {
    if (field === 'hours') {
        selectedTime.value.hours = Math.max(0, Math.min(23, parseInt(value) || 0));
    } else if (field === 'minutes') {
        // Arrondir les minutes au multiple de 5 le plus proche
        const minutes = parseInt(value) || 0;
        const roundedMinutes = Math.round(minutes / 5) * 5;
        selectedTime.value.minutes = Math.max(0, Math.min(55, roundedMinutes));
    }
    
    // Mettre à jour la date sélectionnée si elle existe
    if (selectedDate.value) {
        const newDate = selectedDate.value.clone()
            .hour(selectedTime.value.hours)
            .minute(selectedTime.value.minutes);
        
        selectedDate.value = newDate;
        // Utiliser l'événement update:time au lieu de update:modelValue pour éviter la fermeture du popup
        emit("update:time", newDate.toDate());
    }
};

// Navigation entre les mois
const previousMonth = () => {
    if (currentMonthIndex.value === 0) {
        currentMonthIndex.value = 11;
        currentYear.value--;
    } else {
        currentMonthIndex.value--;
    }
};

const nextMonth = () => {
    if (currentMonthIndex.value === 11) {
        currentMonthIndex.value = 0;
        currentYear.value++;
    } else {
        currentMonthIndex.value++;
    }
};

</script>

<template>
    <div class="calendar-popup bg-white rounded-xl shadow-md">
        <div class="flex flex-col">
            <!-- Calendrier -->
            <div class="flex-1 p-4">
                <!-- En-tête du calendrier -->
                <div class="flex items-center justify-between mb-4">
                    <!-- Titre du mois -->
                    <div class="text-center">
                        <span class="text-lg font-semibold text-gray-900">
                            {{ monthNames[currentMonthIndex] }} {{ currentYear }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Bouton précédent -->
                        <button 
                            @click="previousMonth"
                            class="p-1 hover:bg-gray-100 rounded-md transition-colors"
                            :disabled="disabled"
                        >
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        
                        <!-- Bouton suivant -->
                        <button 
                            @click="nextMonth"
                            class="p-1 hover:bg-gray-100 rounded-md transition-colors"
                            :disabled="disabled"
                        >
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Jours de la semaine -->
                <div class="grid grid-cols-7 mb-2">
                    <div 
                        v-for="day in weekDays" 
                        :key="day"
                        class="text-center font-bold text-xs text-gray-500 py-2"
                    >
                        {{ day }}
                    </div>
                </div>

                <!-- Grille des jours -->
                <div class="grid grid-cols-7">
                    <button
                        v-for="(day, index) in currentMonthDays"
                        :key="index"
                        @click="selectDate(day)"
                        :disabled="!day || disabled"
                        :class="[
                            'h-9 w-9 text-sm relative',
                            {
                                // Jours hors mois (gris clair)
                                'text-gray-300 cursor-not-allowed': !day || !day.isCurrentMonth,

                                // Jours du mois actuel - état normal
                                'text-gray-600 hover:text-blue-500 cursor-pointer': day && day.isCurrentMonth && !day.isDisabled && !day.isSelected && !day.isToday,
                                
                                // Aujourd'hui (bordure bleue)
                                'text-gray-900 border border-blue-500 rounded-full hover:text-blue-500': day && day.isToday && !day.isSelected,
                                
                                // Jour sélectionné (disque bleu plein)
                                'bg-blue-500 text-white rounded-full': day && day.isSelected,
                                
                                // Jours désactivés
                                'text-gray-400 cursor-not-allowed': day && day.isDisabled
                            }
                        ]"
                    >
                        <span class="font-bold">
                            {{ day ? day.date : '' }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Sélecteur d'heure (si type datetime ou time) - Mobile: en haut, Desktop: à gauche -->
            <div v-if="type === 'datetime' || type === 'time'" class="border-t border-gray-200 flex flex-col align-center p-4">                
                <!-- Layout responsive : horizontal sur mobile, vertical sur desktop -->
                <div class="flex flex-row items-center justify-center gap-2">
                    <!-- Heures -->
                    <div class="flex flex-col items-center w-2/5 text-center">
                        <label class="text-xs text-gray-500 mb-2 font-medium text-center">Hours</label>
                        <div class="flex flex-col gap-1 w-full">
                            <button
                                @click.stop="updateTime('hours', Math.min(23, selectedTime.hours + 1))"
                                :disabled="disabled || selectedTime.hours >= 23"
                                class="w-8 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors w-full"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            
                            <input
                                type="number"
                                :value="selectedTime.hours"
                                @input="updateTime('hours', $event.target.value)"
                                min="0"
                                max="23"
                                class="w-full h-10 text-center text-lg font-semibold border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors appearance-none"
                                :disabled="disabled"
                            />
                            
                            <button
                                @click.stop="updateTime('hours', Math.max(0, selectedTime.hours - 1))"
                                :disabled="disabled || selectedTime.hours <= 0"
                                class="w-8 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors w-full"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Séparateur -->
                    <div class="text-2xl font-bold text-gray-300 w-1/5 text-center mt-3">:</div>

                    <!-- Minutes -->
                    <div class="flex flex-col items-center w-2/5 text-center">
                        <label class="text-xs text-gray-500 mb-2 font-medium text-center">Minutes</label>
                        <div class="flex flex-col gap-1 w-full">
                            <button
                                @click.stop="updateTime('minutes', Math.min(55, (selectedTime.minutes + 5)))"
                                :disabled="disabled || selectedTime.minutes >= 55"
                                class="w-8 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors w-full"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                            
                            <input
                                type="number"
                                :value="selectedTime.minutes"
                                @input="updateTime('minutes', $event.target.value)"
                                min="0"
                                max="55"
                                step="5"
                                class="w-full h-10 text-center text-lg font-semibold border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors appearance-none"
                                :disabled="disabled"
                            />
                            
                            <button
                                @click.stop="updateTime('minutes', Math.max(0, selectedTime.minutes - 5))"
                                :disabled="disabled || selectedTime.minutes <= 0"
                                class="w-8 h-6 flex items-center justify-center text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors w-full"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</template>

<style lang="postcss" scoped>
.calendar-popup {
    z-index: 50;
}

.appearance-none::-webkit-outer-spin-button,
.appearance-none::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
.appearance-none {
  -moz-appearance: textfield;
}
</style>
