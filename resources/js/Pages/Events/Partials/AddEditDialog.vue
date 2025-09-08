<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/inertia-vue3";
import moment from "moment";
import Dialog from "@/Components/Common/DialogModal";
import Button from "@/Components/Common/Button";
import Input from "@/Components/Common/Input";
import DateTimePicker from "@/Components/Common/DateTimePickers/DateTimePicker";

const emit = defineEmits(["close"]);

const props = defineProps({
    itemToEdit: {
        type: Object,
        default: null,
    },
});

const show = ref(false);
const editing = ref(false);

const form = useForm({
    title: "",
    starts_at: null,
    ends_at: null,
});

// Called when the user clicks on the "Add new" button
const onAddNew = () => {
    form.reset();
    show.value = true;
    editing.value = false;
};

// Watch pour les modifications de l'itemToEdit
watch(() => props.itemToEdit, (newItem) => {
    if (newItem) {
        editing.value = true;
        show.value = true;
        form.title = newItem.title;
        
        // Convertir les dates UTC vers local pour l'affichage
        form.starts_at = moment.utc(newItem.starts_at).local();
        form.ends_at = moment.utc(newItem.ends_at).local();
    }
}, { immediate: true });

// Called when the user submits the form
const onSubmit = () => {
    const transform = (data) => {
        const transformedData = { ...data };
        
        // Convertir le format moment vers le format serveur (UTC)
        if (data.starts_at && moment.isMoment(data.starts_at)) {
            // S'assurer que la date est en UTC avant de la formater
            transformedData.starts_at = data.starts_at.clone().utc().format("YYYY-MM-DD HH:mm");
        } else {
            transformedData.starts_at = null;
        }
        
        if (data.ends_at && moment.isMoment(data.ends_at)) {
            // S'assurer que la date est en UTC avant de la formater
            transformedData.ends_at = data.ends_at.clone().utc().format("YYYY-MM-DD HH:mm");
        } else {
            transformedData.ends_at = null;
        }
        
        return transformedData;
    };

    const requestParams = {
        preserveScroll: true,
        onSuccess: onClose,
    };

    // Stores or updates the item
    if (editing.value) {
        form.transform(transform).put(
            route("events.update", props.itemToEdit.id),
            requestParams,
        );
    } else {
        form.transform(transform).post(route("events.store"), requestParams);
    }
};

// Called when the dialog closes
const onClose = () => {
    form.reset();
    show.value = false;
    editing.value = false;
    emit("close");
};

</script>

<template>
    <div>
        <Button @click="onAddNew">
            <vue-feather type="plus" />
            <span class="ml-2">Add new</span>
        </Button>
        <Dialog :show="show" @close="onClose">
            <template #header>{{
                editing ? "Edit event" : "Add new event"
            }}</template>

            <Input
                name="title"
                label="Title"
                v-model="form.title"
                class="mb-2"
                required
            />
            <div v-if="form.errors.title" class="mb-4 text-red-600 text-sm">
                {{ form.errors.title }}
            </div>

            <DateTimePicker
                name="starts_at"
                label="Start date and time"
                :model-value="form.starts_at"
                @update:model-value="form.starts_at = $event"
                type="datetime"
                class="mb-2"
            />
            <div v-if="form.errors.starts_at" class="mb-4 text-red-600 text-sm">
                {{ form.errors.starts_at }}
            </div>

            <DateTimePicker
                name="ends_at"
                label="End date and time"
                :model-value="form.ends_at"
                @update:model-value="form.ends_at = $event"
                type="datetime"
                class="mb-2"
            />
            <div v-if="form.errors.ends_at" class="mb-4 text-red-600 text-sm">
                {{ form.errors.ends_at }}
            </div>

            <template #footer>
                <Button variant="secondary" class="mr-3" @click="onClose">Cancel</Button>
                <Button @click="onSubmit">Submit</Button>
            </template>
        </Dialog>
    </div>
</template>

<style scoped></style>
