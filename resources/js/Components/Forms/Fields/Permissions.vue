<script setup lang="ts">
import { Ref, ref } from 'vue'
import { Collapse } from 'vue-collapsed'
import CardPermissions from './Components/Permissions/Card.vue'
import { get } from 'lodash'

const props = defineProps<{
    form: {
        [key: string]: {
            position_name: string  // Administrator
            organisations: {
                [key: string]: string[]  // aw: [uk, de, fr]
            }
            shops: string[]
            warehouses: string[]
            fulfilments: string[]
        }[]
    }
    fieldName: string
    options?: any
    fieldData?: {
        
    }
}>()

const handleBox = (shopsSelected: string[], shopSlug: string) => {
    // console.log('ffff', shopsSelected)
    // if (shopsSelected.includes(shopSlug)) {
        
    //     const indexShopSlug = shopsSelected.indexOf(shopSlug)
    //     if (indexShopSlug !== -1) {
    //         shopsSelected.splice(indexShopSlug, 1)
    //     } else {
    //         shopsSelected.push(shopSlug)
    //     }
    // }
}


</script>

<template>
    <div class="flex flex-col gap-y-6">
        <!-- <pre>{{ form[fieldName] }}</pre> -->
        <template v-for="permissions  in form[fieldName]" >

            <!-- Permission Position -->
            <!-- {{ permission }} -->
                <div class="flex flex-col gap-y-2">
                    <div class="font-medium text-xl">{{ permissions.position_name }}</div>
                    <!-- Org and his shops -->
                    <div  v-for="shopsSelected, orgSlug in permissions.organisations" class="flex flex-col">
                        <div>{{ orgSlug }}:</div>
                
                        <!-- Shop list -->
                        <div class="flex flex-wrap gap-x-1 gap-y-2">
                            <div v-for="shopSlug in permissions.shops"
                                @click="() => handleBox(shopsSelected, shopSlug)"
                                class="w-min px-2 py-1 hover:bg-gray-200"
                                :class="[shopsSelected.includes(shopSlug) ? 'bg-indigo-500 text-white rounded' : 'bg-gray-100']"
                            >
                                {{ shopSlug }}
                            </div>
                        </div>
                    </div>
                
                </div>
        </template>

                <!-- <Collapse v-model="question.isExpanded" :when="question.isExpanded" class="Collapse"
                    style="border-top: 1px solid #d9d9d9;">
                    <div class="Content">
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="(item, key) in question.branchShop" :key="key">
                                <CardPermissions :data="item" :checkboxValues="checkboxValues"
                                    :toggleCheckbox="toggleCheckbox" />
                            </div>
                        </div>
                    </div>
                </Collapse> -->
    </div>
</template>

<style scoped>
.Content {
    padding: 15px;
}

.Panel {
    width: 100%;
    font-size: 1rem;
    color: var(--ForegroundColor);
    text-align: left;
    font-weight: 600;
}

.Panel:hover {
    color: indigo;
}

.Active {
    color: indigo;
    background: #eef2ff;
}

.Section {
    background: #fafafa;
    width: 100%;
    border: 1px solid #d9d9d9;
    margin: 0;
    border-radius: 6px;
    margin-bottom: 5px;
}

.Section button {
    width: 100%;
    padding: 10px 10px;
    cursor: pointer;
}

.Collapse {
    transition: height var(--vc-auto-duration) cubic-bezier(0.37, 0, 0.63, 1);
}

.Collapse {
    background: #ffff;
    border-top: 1px solid #d9d9d9;
}
</style>
