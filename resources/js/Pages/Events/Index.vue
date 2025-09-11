<script setup>
import { onMounted, ref, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
import moment from "moment";
import AppLayout from "@/Layouts/AppLayout";
import AddEditDialog from "./Partials/AddEditDialog";
import Button from "@/Components/Common/Button";
import Dialog from "@/Components/Common/DialogModal";
import Table from "@/Components/Common/Table";
import DateRangePicker from "@/Components/DateTimePickers/DateRangePicker";

const format = "YYYY-MM-DD HH:mm";

const props = defineProps({
    events: {
        type: Object,
        default: () => ({ data: [], links: [], meta: {} }),
    },
    starts_at: String,
    ends_at: String,
});

const dateFilters = ref([null, null]);

// Load url params into state if any existed on mount
onMounted(() => {
    // Toujours initialiser à null par défaut
    dateFilters.value = [null, null];
    
    if (props.starts_at && props.starts_at.trim() !== '') {
        const momentDate = moment(props.starts_at, format);
        if (momentDate.isValid()) {
            dateFilters.value[0] = momentDate;
        }
    }
    if (props.ends_at && props.ends_at.trim() !== '') {
        const momentDate = moment(props.ends_at, format);
        if (momentDate.isValid()) {
            dateFilters.value[1] = momentDate;
        }
    }
});

// Watch for changes in props to update dateFilters
watch(() => [props.starts_at, props.ends_at], ([newStartsAt, newEndsAt]) => {
    if (newStartsAt && newStartsAt.trim() !== '') {
        const momentDate = moment(newStartsAt, format);
        dateFilters.value[0] = momentDate.isValid() ? momentDate : null;
    } else {
        dateFilters.value[0] = null;
    }
    
    if (newEndsAt && newEndsAt.trim() !== '') {
        const momentDate = moment(newEndsAt, format);
        dateFilters.value[1] = momentDate.isValid() ? momentDate : null;
    } else {
        dateFilters.value[1] = null;
    }
}, { immediate: true });

const itemToEdit = ref(null);
const itemToDelete = ref(null);
// Modal de filtrage
const showFilterModal = ref(false);

const onDelete = () => {
    Inertia.delete(route("events.destroy", itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            itemToDelete.value = null;
        },
    });
};

const onFilterSubmit = () => {
    const params = {};
    
    if (dateFilters.value[0]) {
        params.starts_at = dateFilters.value[0].format(format);
    }
    if (dateFilters.value[1]) {
        params.ends_at = dateFilters.value[1].format(format);
    }
    
    Inertia.get(route("events.index"), params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showFilterModal.value = false;
        }
    });
}; 

const openFilterModal = () => {
    showFilterModal.value = true;
};

</script>

<template>
    <AppLayout title="Events">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Events
            </h2>
        </template>

        <div class="card lg:mx-auto mx-4 rounded-lg py-6 px-4 sm:py-12 sm:px-8">
            <div class="mb-3">
                <!-- Actions header - responsive -->
                <div class="mb-6 flex flex-col sm:flex-row gap-4 sm:justify-between sm:items-end">
                    <div class="flex-1">
                        <AddEditDialog
                            :item-to-edit="itemToEdit"
                            @close="itemToEdit = null"
                        />
                    </div>
                    
                    <!-- Button pour ouvrir le modal de filtrage -->
                    <div class="flex-shrink-0">
                        <Button @click="openFilterModal" variant="secondary" class="w-full sm:w-auto">
                            <vue-feather type="filter" />
                            <span class="ml-2">Filter</span>
                        </Button>
                    </div>
                </div>
                <Dialog
                    :show="itemToDelete != null"
                    @close="itemToDelete = null"
                >
                    <template #header>Deleting event</template>
                    Are you sure you want to delete this event ?
                    <template #footer>
                        <Button
                            variant="secondary"
                            class="mr-3"
                            @click="itemToDelete = null"
                            >Cancel</Button
                        >
                        <Button variant="danger" @click="onDelete"
                            >Submit</Button
                        >
                    </template>
                </Dialog>

                <!-- Modal de filtrage -->
                <Dialog
                    :show="showFilterModal"
                    @close="showFilterModal = false"
                >
                    <template #header>Events filter</template> 
                    
                    <div class="mt-4">
                        <DateRangePicker
                            name="filter_date_range_natif"
                            label="Date range"
                            v-model="dateFilters"
                            placeholder="Select a date range"
                            first-day-of-week="0"
                        />
                    </div>
                    
                    <template #footer>
                        <div class="flex flex-row gap-3 justify-end">
                            <Button
                                variant="secondary"
                                class="w-full sm:w-auto sm:mr-3"
                                @click="showFilterModal = false"
                            >
                                Cancel
                            </Button> 
                            <Button @click="onFilterSubmit" class="w-full sm:w-auto">
                                Apply
                            </Button>
                        </div>
                    </template>
                </Dialog>
            </div>
            <Table 
                :data="events.data" 
                :headings="['Title', 'Start date', 'End date', 'Actions']"
                :column-widths="['w-3/6', 'w-1/6', 'w-1/6', 'w-1/6']"
            >
                <!-- Version desktop -->
                <template #row="{ item }">
                    <td class="px-6 py-4 text-left font-medium text-gray-900">
                        {{ item.title }}
                    </td>
                    <td class="px-6 py-4 text-left">
                        {{ moment.utc(item.starts_at).local().format("DD/MM/YYYY  HH:mm") }}
                    </td>
                    <td class="px-6 py-4 text-left">
                        {{ moment.utc(item.ends_at).local().format("DD/MM/YYYY  HH:mm") }}
                    </td>
                    <td class=" px-6 py-4 text-center">
                        <span
                            class="px-2 text-gray-700 hover:text-blue-500 cursor-pointer transition"
                        >
                            <vue-feather
                                type="edit"
                                size="1.3rem"
                                @click="itemToEdit = item"
                            />
                        </span>
                        <span
                            class="px-2 text-gray-700 hover:text-red-500 cursor-pointer transition"
                        >
                            <vue-feather
                                type="trash"
                                size="1.3rem"
                                @click="itemToDelete = item"
                            />
                        </span>
                    </td>
                </template>

                <!-- Version mobile -->
                <template #mobile-row="{ item, headings }">
                    <div class="space-y-3">
                        <!-- Titre -->
                        <div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                                {{ headings[0] }}
                            </div>
                            <div class="text-lg font-semibold text-gray-900">
                                {{ item.title }}
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                                    {{ headings[1] }}
                                </div>
                                <div class="text-sm text-gray-700">
                                    {{ moment.utc(item.starts_at).local().format("DD/MM/YYYY HH:mm") }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                                    {{ headings[2] }}
                                </div>
                                <div class="text-sm text-gray-700">
                                    {{ moment.utc(item.ends_at).local().format("DD/MM/YYYY HH:mm") }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3 pt-2 border-t border-gray-100">
                            <button
                                @click="itemToEdit = item"
                                class="flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                            >
                                <vue-feather type="edit" size="1rem" class="mr-2" />
                                Edit
                            </button>
                            <button
                                @click="itemToDelete = item"
                                class="flex items-center px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition-colors"
                            >
                                <vue-feather type="trash" size="1rem" class="mr-2" />
                                Delete
                            </button>
                        </div>
                    </div>
                </template>
            </Table>

            <!-- Pagination -->
            <div v-if="events.links && events.links.length > 3" class="mt-6">
                <div class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:space-y-0">
                    <!-- Info sur les résultats -->
                    <div class="flex justify-center lg:justify-start">
                        <p class="text-sm text-gray-500 text-center lg:text-left">
                            Displaying {{ events.from || 0 }} to {{ events.to || 0 }} of {{ events.total || 0 }} entries
                        </p>
                    </div>
                    <nav aria-label="Pagination" class="flex justify-center items-center text-gray-600 mt-8 lg:mt-0">
                        <button
                            v-for="(link, index) in events.links"
                            :key="index"
                            @click="link.url && Inertia.get(link.url)"
                            :disabled="!link.url"
                            :class="[
                                'px-4 py-2 rounded transition-colors',
                                link.active
                                    ? 'bg-gray-200 text-gray-900 font-medium hover:bg-gray-100'
                                    : link.url
                                    ? 'hover:bg-gray-100'
                                    : 'opacity-50 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        ></button>
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>
