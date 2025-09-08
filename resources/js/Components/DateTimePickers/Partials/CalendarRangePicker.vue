<script setup>
import { ref, computed, watch } from "vue";
import moment from "moment";

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({ startDate: null, endDate: null })
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
    }
});

const emit = defineEmits(["update:modelValue", "close", "reset"]);

// État local pour la plage de dates
const selectedRange = ref({
    startDate: props.modelValue.startDate ? moment(props.modelValue.startDate) : null,
    endDate: props.modelValue.endDate ? moment(props.modelValue.endDate) : null
});

// Surveiller les changements de la valeur externe
watch(() => props.modelValue, (newValue) => {
    selectedRange.value = {
        startDate: newValue.startDate ? moment(newValue.startDate) : null,
        endDate: newValue.endDate ? moment(newValue.endDate) : null
    };
}, { immediate: true });

// État de sélection (startDate ou endDate)
const selectionMode = ref('startDate');

// Obtenir le mois et l'année actuels
const currentMonth = ref(moment());
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
            isInRange: false,
            isStartDate: false,
            isEndDate: false,
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
        
        const isInRange = selectedRange.value.startDate && selectedRange.value.endDate && 
                         date.isBetween(selectedRange.value.startDate, selectedRange.value.endDate, 'day', '[]');
        const isStartDate = selectedRange.value.startDate && date.isSame(selectedRange.value.startDate, 'day');
        const isEndDate = selectedRange.value.endDate && date.isSame(selectedRange.value.endDate, 'day');
        
        
        days.push({
            date: day,
            moment: date,
            isCurrentMonth: true,
            isInRange: isInRange,
            isStartDate: isStartDate,
            isEndDate: isEndDate,
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
            isInRange: false,
            isStartDate: false,
            isEndDate: false,
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
        if (selectionMode.value === 'startDate') {
            // Date de début à 00:00:00
            selectedRange.value.startDate = day.moment.clone().startOf('day');
            selectedRange.value.endDate = null; // Réinitialiser la date de fin
            selectionMode.value = 'endDate';
            // Ne pas fermer le popup, attendre la sélection de la date de fin
        } else {
            if (selectedRange.value.startDate && day.moment.isBefore(selectedRange.value.startDate, 'day')) {
                // Si la date de fin est avant la date de début, inverser
                selectedRange.value.endDate = selectedRange.value.startDate.clone().endOf('day');
                selectedRange.value.startDate = day.moment.clone().startOf('day');
            } else {
                // Date de fin à 23:59:59
                selectedRange.value.endDate = day.moment.clone().endOf('day');
            }
            
            // Émettre la plage complète
            emit("update:modelValue", {
                startDate: selectedRange.value.startDate.toDate(),
                endDate: selectedRange.value.endDate.toDate()
            });
            
            // Réinitialiser le mode de sélection
            selectionMode.value = 'startDate';
            
            // Fermer le popup après un court délai
            setTimeout(() => {
                emit("close");
            }, 100);
        }
    }
};

// Options rapides en anglais
const quickOptions = [
    {
        label: "Today",
        action: () => {
            const today = moment();
            selectedRange.value = { 
                startDate: today.clone().startOf('day'), 
                endDate: today.clone().endOf('day') 
            };
            emit("update:modelValue", { 
                startDate: today.clone().startOf('day').toDate(), 
                endDate: today.clone().endOf('day').toDate() 
            });
            setTimeout(() => emit("close"), 100);
        }
    },
    {
        label: "Yesterday",
        action: () => {
            const yesterday = moment().subtract(1, 'day');
            selectedRange.value = { 
                startDate: yesterday.clone().startOf('day'), 
                endDate: yesterday.clone().endOf('day') 
            };
            emit("update:modelValue", { 
                startDate: yesterday.clone().startOf('day').toDate(), 
                endDate: yesterday.clone().endOf('day').toDate() 
            });
            setTimeout(() => emit("close"), 100);
        }
    },
    {
        label: "Last week",
        action: () => {
            const lastWeekStart = moment().subtract(1, 'week');
            const lastWeekEnd = moment();
            selectedRange.value = { 
                startDate: lastWeekStart.clone().startOf('day'), 
                endDate: lastWeekEnd.clone().endOf('day') 
            };
            emit("update:modelValue", { 
                startDate: lastWeekStart.clone().startOf('day').toDate(), 
                endDate: lastWeekEnd.clone().endOf('day').toDate() 
            });
            setTimeout(() => emit("close"), 100);
        }
    },
    {
        label: "Last month",
        action: () => {
            const lastMonthStart = moment().subtract(1, 'month');
            const lastMonthEnd = moment();
            selectedRange.value = { 
                startDate: lastMonthStart.clone().startOf('day'), 
                endDate: lastMonthEnd.clone().endOf('day') 
            };
            emit("update:modelValue", { 
                startDate: lastMonthStart.clone().startOf('day').toDate(), 
                endDate: lastMonthEnd.clone().endOf('day').toDate() 
            });
            setTimeout(() => emit("close"), 100);
        }
    },
    {
        label: "Last quarter",
        action: () => {
            const lastQuarterStart = moment().subtract(3, 'month');
            const lastQuarterEnd = moment();
            selectedRange.value = { 
                startDate: lastQuarterStart.clone().startOf('day'), 
                endDate: lastQuarterEnd.clone().endOf('day') 
            };
            emit("update:modelValue", { 
                startDate: lastQuarterStart.clone().startOf('day').toDate(), 
                endDate: lastQuarterEnd.clone().endOf('day').toDate() 
            });
            setTimeout(() => emit("close"), 100);
        }
    }
];

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

// Réinitialiser
const resetSelection = () => {
    selectedRange.value = { startDate: null, endDate: null };
    selectionMode.value = 'startDate';
    emit("reset");
    setTimeout(() => emit("close"), 100);
};
</script>

<template>
    <div class="calendar-popup bg-white rounded-xl shadow-md">
        <div class="flex flex-col sm:flex-row">
            <!-- Colonne gauche Quick ranges -->
            <div class="p-3 sm:p-4 border-b sm:border-b-0 sm:border-r border-gray-200 flex flex-col justify-between">
                <!-- Options rapides en grille sur mobile, liste sur desktop -->
                <div class="grid grid-cols-2 sm:grid-cols-1 gap-2 sm:gap-0">
                    <button
                        v-for="option in quickOptions"
                        :key="option.label"
                        @click="option.action"
                        class="text-left px-3 py-2 text-xs sm:text-sm text-gray-900 bg-gray-100 sm:bg-transparent hover:bg-gray-200 sm:hover:bg-gray-100 rounded-lg transition-colors"
                        :disabled="disabled"
                    >
                        {{ option.label }}
                    </button>
                </div>
                
                <div class="mt-3 sm:mt-auto sm:pt-4">
                    <button 
                        @click="resetSelection"
                        class="w-full text-center sm:text-left px-3 py-2 text-xs sm:text-sm text-blue-500 hover:text-blue-600 bg-blue-50 sm:bg-transparent hover:bg-blue-100 sm:hover:bg-blue-50 rounded-lg transition-colors"
                        :disabled="disabled"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Colonne droite - Calendrier -->
            <div class="p-3 sm:p-4 flex-1">
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

                                // Jours du mois précédent (gris clair) mais dans la plage
                                'bg-gray-200': (!day || !day.isCurrentMonth) && day.isInRange,
                                
                                // Jours du mois actuel - état normal
                                'text-gray-600 hover:text-blue-500 cursor-pointer': day && day.isCurrentMonth && !day.isDisabled && !day.isInRange && !day.isStartDate && !day.isEndDate && !day.isToday,
                                
                                // Aujourd'hui (bordure bleue)
                                'text-gray-900 border border-blue-500 rounded-full hover:text-blue-500': day && day.isToday && !day.isInRange && !day.isStartDate && !day.isEndDate,
                                
                                // Jour de début ou de fin (disque bleu plein)
                                'bg-transparent text-white rounded-start-end': day && (day.isStartDate || day.isEndDate),

                                // Jour de début
                                'rounded-start': day && day.isStartDate,

                                // Jour de fin
                                'rounded-end': day && day.isEndDate,
                                
                                // Jours dans la plage (fond bleu clair)
                                'bg-blue-100 text-blue-500': day && day.isInRange && !day.isStartDate && !day.isEndDate,
                                
                                // Jours désactivés
                                'text-gray-400 cursor-not-allowed': day && day.isDisabled
                            }
                        ]"
                    >
                    <span class="font-bold" >
                        {{ day ? day.date : '' }}
                    </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style lang="postcss" scoped>

.rounded-start-end span {
    position: relative;
    z-index: 2;
}

.rounded-start-end:before {
    content: '';
    position: absolute;
    width: calc(100% - 8px);
    height: calc(100% - 8px);
    top: 4px;
    left: 4px;
    right: 4px;
    bottom: 4px;
    z-index: 1;
    border-radius: 80px;
    background-color: #003cff;
}

.rounded-start-end::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    background-color: #003cff;
    opacity: 10%;
}

.rounded-start::after {
    border-top-left-radius: 80px;
    border-bottom-left-radius: 80px;
}
.rounded-end::after {
    border-top-right-radius: 80px;
    border-bottom-right-radius: 80px;
}

.calendar-popup {
    z-index: 50;
}

</style>
