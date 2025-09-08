<script setup>
import { onMounted, ref, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
import moment from "moment";
import AppLayout from "@/Layouts/AppLayout";
import AddEditDialog from "./Partials/AddEditDialog";
import Button from "@/Components/Common/Button";
import Dialog from "@/Components/Common/DialogModal";
import Table from "@/Components/Common/Table";
import DateRangePicker from "@/Components/Common/DateTimePickers/DateRangePicker";

const format = "YYYY-MM-DD HH:mm";

const props = defineProps({
    events: {
        type: Array,
        default: [],
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

        <div class="card">
            <div class="mb-3">
                <div class="mb-6 flex flex-row justify-between items-end">
                        <AddEditDialog
                            :item-to-edit="itemToEdit"
                            @close="itemToEdit = null"
                        />
                        
                        <!-- Button pour ouvrir le modal de filtrage -->
                        <Button @click="openFilterModal" variant="secondary">
                            <vue-feather type="filter" />
                            <span class="ml-2">Filter</span>
                        </Button>
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
                        />
                    </div>
                    
                    <template #footer>
                        <Button
                            variant="secondary"
                            class="mr-3"
                            @click="showFilterModal = false"
                        >
                            Cancel
                        </Button> 
                        <Button @click="onFilterSubmit">
                            Apply Filter
                        </Button>
                    </template>
                </Dialog>
            </div>
            <Table 
                :data="events" 
                :headings="['Titre', 'Date de début', 'Date de fin', 'Actions']"
                :column-widths="['w-3/6', 'w-1/6', 'w-1/6', 'w-1/6']"
            >
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
            </Table>
        </div>
    </AppLayout>
</template>

<style scoped></style>
