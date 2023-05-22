<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 05 Oct 2022 07:06:15 Central European Summer Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup>
import { Head } from "@inertiajs/vue3"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faUserCircle, faKey, faClone, faPaintBrush, faMoonStars, faLightbulbOn, faCheck } from "@/../private/pro-light-svg-icons"
library.add(faUserCircle, faKey, faClone, faPaintBrush, faMoonStars, faLightbulbOn, faCheck)
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { useForm } from "@inertiajs/vue3"
import { ref } from "vue"
import Form from "@/Components/Forms/Form.vue"

import Password from "@/Components/Forms/Fields/Password.vue"
import Theme from "@/Components/Forms/Fields/Theme.vue"
import ColorMode from "@/Components/Forms/Fields/ColorMode.vue"
import FieldForm from '@/Components/Forms/FieldForm.vue'
import Avatar from '@/Components/Forms/Fields/Avatar.vue'

const profileForm = useForm({
    username: props.profile.username,
    about: props.profile.about,
    email: props.profile.email,
    avatar: null,
    _method: "patch",
})

const props = defineProps(["title", "pageHead", "profile", "pageBody"])

const passwordForm = useForm({ password: "" })
const darkModeForm = useForm({ darkMode: true })
const themeForm = useForm({ theme: "theme-blue" })

const current = ref(props["pageBody"].current)
</script>

<template layout="App">
    <Head :title="title" />
    <div class="ml-0 max-w-screen-xl">
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="divide-y divide-gray-200 lg:grid grid-flow-col lg:grid-cols-12 lg:divide-y-0 lg:divide-x">
                <aside class="py-0 lg:col-span-3 lg:h-full">
                    <div>
                        <h2
                            class="py-3 pl-2 font-bold leading-7 text-gray-900 sm:truncate lg:text-2xl sm:tracking-tight capitalize">
                            {{ pageHead.title }}
                        </h2>
                    </div>
                    <nav role="navigation" class="space-y-1">
                        <ul>
                            <li v-for="(item, key) in pageBody.layout" @click="current = key" :class="[
                                key === current
                                    ? 'bg-teal-50 border-teal-500 text-teal-700 hover:bg-teal-50 hover:text-teal-700'
                                    : 'border-transparent text-gray-900 hover:bg-gray-50 hover:text-gray-900',
                                'cursor-pointer group border-l-4 px-3 py-2 flex items-center text-sm font-medium',
                            ]" :aria-current="key === current ? 'page' : undefined">
                                <FontAwesomeIcon aria-hidden="true" :class="[
                                    key === current
                                        ? 'text-teal-500 group-hover:text-teal-500'
                                        : 'text-gray-400 group-hover:text-gray-500',
                                    'flex-shrink-0 -ml-1 mr-3 h-6 w-6',
                                ]" :icon="item.icon" />

                                <span class="truncate">{{ item.title }}</span>
                            </li>
                        </ul>
                    </nav>
                </aside>

                <Form v-show="current === 'profile'" :form="profileForm" :url="'/profile'" :method="'post'"
                    :layout="pageBody.layout.profile">
                    <div class="mt-6 flex flex-col lg:flex-row">
                        <div class="flex-grow space-y-6">
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">
                                    {{ pageBody.layout.profile.fields.username.label }}
                                </label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input v-model="profileForm.username" type="text" name="username" id="username"
                                        autocomplete="username"
                                        class="block w-full min-w-0 flex-grow rounded-none rounded-r-md border-gray-300 focus:border-sky-500 focus:ring-sky-500 sm:text-sm" />
                                </div>
                            </div>

                            <div>
                                <label for="about" class="block text-sm font-medium text-gray-700">
                                    {{ pageBody.layout.profile.fields.about.label }}
                                </label>
                                <div class="mt-1">
                                    <textarea v-model="profileForm.about" id="about" name="about" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500 sm:text-sm" />
                                </div>
                                <p class="mt-2 text-xs italic text-gray-500">
                                    {{ pageBody.layout.profile.fields.about.notes }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <Avatar class="mt-4" :profileForm="profileForm" :pageBody="props.pageBody" :profile="props.profile" />

                    <div class="mt-6 grid grid-cols-12 gap-6">
                        <div class="col-span-12">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input v-model="profileForm.email" type="text" name="email" id="email"
                                class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm" />
                            <p v-if="profileForm.errors.email" class="mt-2 text-sm text-red-700">
                                {{ profileForm.errors.email }}
                            </p>
                        </div>
                    </div>
                </Form>
                <!-- <Form v-show="current === 'password'" :form="passwordForm" :layout="pageBody.layout.password">
                    <div class="mt-6 grid grid-cols-12 gap-6">
                        <div class="col-span-12">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                {{ pageBody.layout.password.fields.password.label }}
                            </label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <Password id="password" name="password" v-model="passwordForm.password" />
                            </div>
                            <div v-if="passwordForm.errors.password" class="text-red-700">
                                {{ passwordForm.errors.password }}
                            </div>
                        </div>
                    </div>
                </Form> -->
                <div v-show="current === 'workplaces'" class="divide-y divide-gray-200 lg:col-span-9">Workplaces</div>

                <!-- Appearance -->
                <div v-show="current === 'appearance'" class="text-sm col-span-9 select-none p-6">

                    <!-- Turn on Dark Mode -->
                    <div class="grid grid-flow-col pl-4 bg-gray-100 grid-cols-3 py-2">
                        <div class="font-medium">Turn Dark Mode</div>
                        <!-- <FieldForm class=" pt-4 sm:pt-5 px-6 " v-for="(appearance, index ) in pageBody.layout.appearance"
                            field="appearance" args="" /> -->
                    </div>

                    <!-- Select Theme -->
                    <div class="grid grid-flow-col pl-4 grid-cols-3 py-2">
                        <div class="font-medium">Select Theme</div>
                        <Theme class="col-span-2" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
