<script setup lang="ts">
import { onMounted, defineExpose, ref, inject } from "vue";
import axios from "axios"
import Bee from "@mailupinc/bee-plugin";
import { routeType } from "@/types/route";
import EmptyState from "@/Components/Utils/EmptyState.vue";
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
import { mergetags } from "@/Composables/variableList";
import { notify } from "@kyvg/vue3-notification";


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
const token = ref(null)
const beeInstance = ref(null)


const emits = defineEmits<{
    (e: 'onSave', value: string | number): void
    (e: 'sendTest', value: string | number): void
    (e: 'saveTemplate', value: string | number): void
    (e: 'autoSave', value: string | number): void
}>()


/* const onSaveEmail = (jsonFile, htmlFile) => {
    axios
        .patch(
            route(props.updateRoute.name, props.updateRoute.parameters),
            {
                layout: JSON.parse(jsonFile),
            },
        )
        .then((response) => {
            console.log("autosave successful:", response.data);
        })
        .catch((error) => {
            console.error("autosave failed:", error);
            notify({
                title: "Failed to save",
			    type: "error",   
            })
        })
        .finally(() => {
            console.log("autosave finished.");
        });
} */

const getCatalog = () => {
    console.log(beeInstance.value)
};

const beeConfig = () => {
    beeInstance.value = new Bee();
    var endpoint = "https://auth.getbee.io/apiauth";
    var payload = {
        client_id: props.apiKey.client_id,
        client_secret: props.apiKey.client_secret,
    };
    var headers = {
        Authorization: token.value ? `Bearer ${token.value.access_token}` : null,
        'Content-Type': 'application/json',
    }
    axios
        .post(endpoint,payload,headers)
        .then((response) => {
            token.value = response.data;
            const config = {
                uid: token.value.userName,
                container: "bee-plugin-container",
                language: "en-US",
                loadingSpinnerDisableOnDialog: true,
                saveRows: true,
                disableBaseColors: true,
                disableColorHistory: true,
                templateLanguageAutoTranslation: true,
                mergeTags: mergetags,
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
                onSend: (htmlFile, jsonFile) => {
                    emits('sendTest', { jsonFile, htmlFile })
                },
                onSave: function (jsonFile, htmlFile) {
                    emits('onSave', { jsonFile, htmlFile })
                },
                onSaveAsTemplate: (jsonFile, htmlFile) => {
                    emits('saveTemplate', { jsonFile, htmlFile })
                },
                onAutoSave: function (jsonFile) {
                    /* onSaveEmail(jsonFile, null) */
                    emits('autoSave',jsonFile)
                }
            };
            beeInstance.value
                .getToken(payload.client_id, payload.client_secret)
                .then(() => {
                    beeInstance.value.start(config, JSON.stringify(props.snapshot.layout))
                    getCatalog()
                })
        })
        .catch((error) => {
            console.error("Error authenticating:", error);
        });
}


onMounted(() => { 
    if (!token.value) {
        if (props.apiKey.client_id && props.apiKey.client_secret) {
            showBee.value = true
            beeConfig()
        } else {
            showBee.value = false
        }
    }else{
        token.value = beeInstance.value.updateToken() 
    }
});

defineExpose({
    beeInstance,
})

</script>

<template>
    <div class="bg-yellow-500 font-bold text-white flex justify-center">
        This is Real Beefree api key from Ourora becarefull
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


<style scoped>
.unlayer {
    height: calc(100vh - 177px);
}

.top-bar {
    display: none;
}
</style>