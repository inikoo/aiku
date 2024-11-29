<script setup lang="ts">
import { onMounted, defineExpose, } from "vue";
import axios from "axios"
import Bee from "@mailupinc/bee-plugin";
import data from '@/Components/CMS/Website/Outboxes/Unlayer/UnlayerJsonExample.json'


import { routeType } from "@/types/route";

const props = withDefaults(defineProps<{
    updateRoute?: routeType;
    loadRoute?: routeType;
    imagesUploadRoute?: routeType
    mailshot?: object
}>(), {});

console.log(data)
onMounted(() => {
    const beeInstance = new Bee();
    var endpoint = "https://auth.getbee.io/apiauth";
    var payload = {
        client_id: "4b5904bb-6352-43f4-b283-c20ff8fefff5",
        client_secret: "URLe7euPSiwpvBv3ZvKwTrkOUqneeGNjN93QsmesVY4mhFdfxvgF",
        grant_type:
            "Basic VGhpc0lzTXlTdXBlckxvbmdUZXN0VXNlcm5hbWU6VGhpc1Bhc3N3b3JkSXNBTGll",
    };

    axios
        .post(endpoint, payload)
        .then((response) => {
            const token = response.data;
            console.log("token: ", token);

            const config = {
                uid: token.userName,
                container: "bee-plugin-container",
                language: "en-US",
                customCss: "https://bee-plugin-demos.getbee.io/themes/coral.css",
                userColor: "#F1EDFF",
                loadingSpinnerDisableOnDialog: true,
                saveRows: true,
                disableBaseColors: true,
                disableColorHistory: true,
                templateLanguageAutoTranslation: true,
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
                onSave: (jsonFile, htmlFile) => {
                    const params = {
                        layout_json: jsonFile,
                        layout_html: htmlFile,
                        campaign_id: 1,
                    };
                    console.log("saving...", params);
                },
            };

           /*  var template = JSON.parse(data) */
            beeInstance
                .getToken(payload.client_id, payload.client_secret)
                .then(() => beeInstance.start(config, data));
        })
        .catch((error) => {
            console.error("Error authenticating:", error);
        });
});



defineExpose({})

</script>

<template>
    <div id="app">
        <div id="bee-plugin-container" class="unlayer"></div>
    </div>
</template>


<style>
.unlayer {
    height: calc(100vh - 177px);
}
</style>