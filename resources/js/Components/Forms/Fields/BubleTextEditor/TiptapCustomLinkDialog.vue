<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import Dialog from "./Dialog.vue";
import { useForm } from "@inertiajs/vue3";
import Button from "@/Components/Elements/Buttons/Button.vue";
import PureInput from "@/Components/Pure/PureInput.vue";
import SelectQuery from "@/Components/SelectQuery.vue";

const props = defineProps<{
    show: boolean;
    attribut?: {
        url: string,
        type: string,
        id: string,
        workshop: string,
        content?: string // Added content to props
    }
}>();
const emit = defineEmits(["close", "update"]);

// Reactive form data
const form = useForm({
    url: "",
    type: "internal",
    workshop: null,
    id: null,
    content: '',
});

// Watch for changes in the attribut prop
watch(() => props.attribut, (newValue) => {
    if (newValue) {
        form.url = newValue.url || "";
        form.type = newValue.type || "internal";
        form.workshop = newValue.workshop || null;
        form.id = newValue.id || null;
        form.content = newValue.content || ''; // Ensure to include content
    }
}, { immediate: true }); // Immediate to set initial values

// Function to close the dialog
function closeDialog() {
    emit("close");
}

// Function to update the form and emit events
function update() {
    emit("update", form.data());
    emit("close");
}

// Function to handle link changes
const onChangeLink = (value) => {
    form.url = value.url;
    form.id = value.id;
    form.workshop = value.url_workshop;
};

// Options for link types
const options = [
    { label: 'Internal', value: 'internal' },
    { label: 'External', value: 'external' }
];
</script>

<template>
    <Dialog title="Link Setting" :show="show" @close="closeDialog">
        <div class="flex flex-col space-y-3">
            <div>
                <div class="select-none text-sm text-gray-600 mb-2">Type</div>
                <div class="flex space-x-4">
                    <label v-for="option in options" :key="option.value" class="flex items-center space-x-2">
                        <input type="radio" :value="option.value" v-model="form.type" class="form-radio" />
                        <span>{{ option.label }}</span>
                    </label>
                </div>
            </div>

            <div>
                <div class="select-none text-sm text-gray-600 mb-2">Label</div>
                <PureInput v-model="form.content" />
            </div>

            <div v-if="form.type === 'internal'">
                <div class="select-none text-sm text-gray-600 mb-2">Link</div>
                <SelectQuery 
                    fieldName="id" 
                    :object="true"
                    :urlRoute="route('grp.org.shops.show.web.webpages.index', {
                        organisation: 'aw',
                        shop: 'uk',
                        website: route().params['website']
                    })" 
                    :value="form" 
                    :closeOnSelect="true" 
                    label="url" 
                    :onChange="onChangeLink"
                />
            </div>

            <div v-if="form.type === 'external'">
                <div class="select-none text-sm text-gray-600 mb-2">Link</div>
                <PureInput v-model="form.url" />
            </div>

            <!-- Buttons -->
            <div class="flex flex-row justify-end space-x-3">
                <Button type="white" label="cancel" @click="closeDialog">
                    Batal
                </Button>
                <Button @click="update" type="black" label="Apply" />
            </div>
        </div>
    </Dialog>
</template>
