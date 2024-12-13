<script setup lang="ts">
import { onMounted, defineExpose, ref } from "vue";
import axios from "axios"
import Bee from "@mailupinc/bee-plugin";
import { routeType } from "@/types/route";
import { router } from "@inertiajs/vue3"
import EmptyState from "@/Components/Utils/EmptyState.vue";

const props = withDefaults(defineProps<{
    updateRoute: routeType;
    imagesUploadRoute: routeType
    snapshot: any
    apiKey:{
        client_id : string,
        client_secret : string,
        grant_type : string
    }
}>(), {});

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

const beeConfig = () => {
    const beeInstance = new Bee();
    var endpoint = "https://auth.getbee.io/apiauth";
    var payload = {
        client_id: props.apiKey.client_id,
        client_secret: props.apiKey.client_secret,
        grant_type:
            `Basic ${props.apiKey.grant_type}`,
    };
    axios
        .post(endpoint, payload)
        .then((response) => {
            const token = response.data;
            const config = {
                uid: token.userName,
                container: "bee-plugin-container",
                language: "en-US",
                customCss: "https://bee-plugin-demos.getbee.io/themes/coral.css",
                loadingSpinnerDisableOnDialog: true,
                saveRows: true,
                disableBaseColors: true,
                disableColorHistory: true,
                templateLanguageAutoTranslation: true,
                mergeTags: mergeTags,
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
                autosave: 30,
                onAutoSave: function (jsonFile) {
                    console.log('autosave')
                    router.post(
                        route(props.updateRoute.name, props.updateRoute.parameters),
                        jsonFile,
                        {
                            onStart: () => {
                                console.log("Upload started...");
                                // Perform any loading state updates here
                            },
                            onFinish: () => {
                                console.log("Upload finished.");
                                // Perform any cleanup or state updates here
                            },
                            onError: (e) =>{
                                console.log("Upload finished.",e);
                            }
                        }
                    );

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
    if(props.apiKey.client_id && props.apiKey.client_secret && props.apiKey.grant_type){
        showBee.value = true
        beeConfig()
    }else{
        showBee.value = false
    }
   
});

defineExpose({})

</script>

<template>
    <div v-if="showBee" id="app">
        <div id="bee-plugin-container" class="unlayer"></div>
    </div>

    <div v-else>
        <EmptyState 
            :data="{
                title  : 'You Need Register Your Beefree api key',
                action : {
                    tooltip: 'Setting',
                    type : 'create',
                    route: {
                        name : 'grp.sysadmin.settings.edit'
                    },
                    icon : ['fas', 'user-cog'] 
                }
            }"
        />
    </div>
</template>


<style>
.unlayer {
    height: calc(100vh - 177px);
}
</style>