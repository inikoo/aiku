<script setup lang="ts">
import { onMounted, defineExpose, ref, inject } from "vue";
import axios from "axios"
import Bee from "@mailupinc/bee-plugin";
import { routeType } from "@/types/route";
import { router } from "@inertiajs/vue3"
import EmptyState from "@/Components/Utils/EmptyState.vue";
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'

const props = withDefaults(defineProps<{
    updateRoute: routeType;
    imagesUploadRoute: routeType
    snapshot: any
    apiKey: {
        client_id: string,
        client_secret: string,
        grant_type: string
    }
}>(), {});

const locale = inject('locale', aikuLocaleStructure)
const showBee = ref(false)
var mergeTags = [
    {
        name: 'First Name',
        value: '[first-name]'
    }, {
        name: 'Last Name',
        value: '[last-name]'
    }, {
        name: 'Email',
        value: '[email]'
    }, {
        name: 'Latest order date',
        value: '[order-date]'
    }
];


const onSaveEmail = (jsonFile, htmlFile) => {
    axios
        .patch(
            route(props.updateRoute.name, props.updateRoute.parameters), // Constructed URL
            {
                layout: JSON.parse(jsonFile),
                compiled_layout: htmlFile
            }, // Payload
            {
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    console.log(`Upload progress: ${percentCompleted}%`);
                },
            }
        )
        .then((response) => {
            console.log("autosave successful:", response.data);
            // Handle success (equivalent to onFinish)
        })
        .catch((error) => {
            console.error("autosave failed:", error);
        })
        .finally(() => {
            console.log("autosave finished.");
        });
}


const beeConfig = () => {
    const beeInstance = new Bee();
    var endpoint = "https://auth.getbee.io/apiauth";
    var payload = {
        client_id: props.apiKey.client_id,
        client_secret: props.apiKey.client_secret,
    };
    axios
        .post(endpoint, payload)
        .then((response) => {
            const token = response.data;
            const config = {
                uid: token.userName,
                container: "bee-plugin-container",
                language: "en-US",
                /*   customCss: "https://bee-plugin-demos.getbee.io/themes/coral.css", */
                loadingSpinnerDisableOnDialog: true,
                saveRows: true,
                disableBaseColors: true,
                disableColorHistory: true,
                templateLanguageAutoTranslation: true,
                mergeTags: mergeTags,
                /*  contentDialog, */
                customAttributes: {
                    attributes: [
                        {
                            key: "data-segment",
                            value: [
                                "1.2",
                                "1.3"
                            ],
                            target: "link"
                        },
                        {
                            key: "class",
                            target: "tag"
                        },
                        {
                            key: "class",
                            target: "link"
                        }
                    ]
                },
                autosave: 20,
                onFilePickerInsert: function (data) {
                    // Handle the selected file data
                    console.log("File Inserted:", data);
                    // Perform any necessary actions with the file data
                },
                onSave: function (jsonFile, htmlFile) {
                    onSaveEmail(jsonFile,null)
                },
                onAutoSave: function (jsonFile) {
                    onSaveEmail(jsonFile,null)
                }
            };
            beeInstance
                .getToken(payload.client_id, payload.client_secret)
                .then(() => beeInstance.start(config, JSON.stringify(props.snapshot.layout)))
        })
        .catch((error) => {
            console.error("Error authenticating:", error);
        });
}

onMounted(() => {
    if (props.apiKey.client_id && props.apiKey.client_secret) {
        showBee.value = true
        beeConfig()
    } else {
        showBee.value = false
    }

});

defineExpose({})

</script>

<template>
    <div class="bg-yellow-500 font-bold text-white flex justify-center">
        This is Real Beefree api key from Ourora beecarefull
    </div>

    <div v-if="showBee" id="app">
        <div id="bee-plugin-container" class="unlayer"></div>
    </div>

    <div v-else>
        <EmptyState :data="{
            title: 'You Need Register Your Beefree api key',
            action: {
                tooltip: 'Setting',
                type: 'create',
                route: {
                    name: 'grp.sysadmin.settings.edit'
                },
                icon: ['fas', 'user-cog']
            }
        }" />
    </div>
</template>


<style>
.unlayer {
    height: calc(100vh - 177px);
}
</style>