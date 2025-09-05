<script setup>
import { onMounted, ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import moment from "moment";
import AppLayout from "@/Layouts/AppLayout";
import AddEditDialog from "./Partials/AddEditDialog";
import Button from "@/Components/Common/Button";
import Dialog from "@/Components/Common/DialogModal";
import Table from "@/Components/Common/Table";

const format = "YYYY-MM-DD";

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
    if (props.starts_at) {
        dateFilters.value[0] = moment(props.starts_at, format);
    }
    if (props.ends_at) {
        dateFilters.value[1] = moment(props.ends_at, format);
    }
});

const itemToEdit = ref(null);
const itemToDelete = ref(null);

const onDelete = () => {
    Inertia.delete(route("events.destroy", itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            itemToDelete.value = null;
        },
    });
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
            </div>
            <Table :data="events" :headings="['Titre', 'Date de début', 'Date de fin', 'Actions']">
                <template #row="{ item }">
                    <td class="px-6 py-4 text-left font-medium text-gray-900">
                        {{ item.title }}
                    </td>
                    <td class="px-6 py-4 text-left">
                        {{ moment.utc(item.starts_at).local().format("HH:mm DD/MM/YYYY") }}
                    </td>
                    <td class="px-6 py-4 text-left">
                        {{ moment.utc(item.ends_at).local().format("HH:mm DD/MM/YYYY") }}
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
