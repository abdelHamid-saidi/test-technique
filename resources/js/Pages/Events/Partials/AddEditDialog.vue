<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/inertia-vue3";
import moment from "moment";
import Dialog from "@/Components/Common/DialogModal";
import Button from "@/Components/Common/Button";
import Input from "@/Components/Common/Input";
import InputDate from "@/Components/Common/InputDate";

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
        form.starts_at = newItem.starts_at ? moment(newItem.starts_at).format("YYYY-MM-DD HH:mm") : '';
        form.ends_at = newItem.ends_at ? moment(newItem.ends_at).format("YYYY-MM-DD HH:mm") : '';
    }
}, { immediate: true });

// Called when the user submits the form
const onSubmit = () => {
    const transform = (data) => ({
        ...data,
        starts_at: data.starts_at ? moment(data.starts_at).format("YYYY-MM-DD HH:mm") : null,
        ends_at: data.ends_at ? moment(data.ends_at).format("YYYY-MM-DD HH:mm") : null,
    });

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

            <InputDate
                name="starts_at"
                label="Start date and time"
                v-model="form.starts_at"
                class="mb-2"
                required
            />
            <div v-if="form.errors.starts_at" class="mb-4  text-red-600 text-sm">
                {{ form.errors.starts_at }}
            </div>

            <InputDate
                name="ends_at"
                label="End date and time"
                v-model="form.ends_at"
                class="mb-2"
                required
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
