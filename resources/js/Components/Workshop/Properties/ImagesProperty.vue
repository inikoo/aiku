<script setup lang="ts">
import { faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter } from "@fad";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faLink, faUnlink } from "@fal";
import { faExclamation, faCaretDown, faCaretLeft } from "@fas";

import { ref, watch } from "vue";
import PureInputNumber from "@/Components/Pure/PureInputNumber.vue";
import SideEditor from "../SideEditor/SideEditor.vue";

library.add(faExclamation, faBorderTop, faBorderLeft, faBorderBottom, faBorderRight, faBorderOuter, faLink, faUnlink, faCaretDown, faCaretLeft);

const emit = defineEmits(['update:modelValue']);

const model = defineModel()

const blueprint = [
    {
        key: ["dimension"],
        label: "Dimension",
        type: "dimension",
        props_data: {
            defaultValue: {
                height: {
                    value: "100",
                    unit: "%"
                },
                width: {
                    value: "100",
                    unit: "%"
                }
            }
        }
    },
    {
        key: ["padding"],
        label: "Padding",
        type: "padding",
        props_data: {
            defaultValue: {
                unit: "px",
                top: {
                    "value": 0
                },
                left: {
                    "value": 0
                },
                right: {
                    "value": 0
                },
                bottom: {
                    "value": 0
                }
            },
        }
    },
    {
        key: ["margin"],
        label: "Margin",
        type: "margin",
        props_data: {
            defaultValue: {
                unit: "px",
                top: {
                    "value": 0
                },
                left: {
                    "value": 0
                },
                right: {
                    "value": 0
                },
                bottom: {
                    "value": 0
                }
            },
        }
    },
    {
        key: ["border"],
        label: "Border",
        type: "border",
        props_data: {
            defaultValue: {
                color: "#000000",
                unit: "px",
                rounded: {
                    unit: "px",
                    topright: {
                        value: 0
                    },
                    topleft: {
                        value: 0
                    },
                    bottomright: {
                        value: 0
                    },
                    bottomleft: {
                        value: 0
                    }
                },
                top: {
                    value: 0
                },
                left: {
                    value: 0
                },
                right: {
                    value: 0
                },
                bottom: {
                    value: 0
                }
            }
        }
    },
]

</script>

<template>
    <div>
        <SideEditor 
            :blueprint="blueprint" 
            v-model="model" 
            @input="(e) => (console.log('imagesProperty', e))" />
    </div>
</template>

<style scoped></style>
