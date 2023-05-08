<!--
  -  Author: Raul Perusquia <raul@inikoo.com>
  -  Created: Wed, 05 Oct 2022 07:06:15 Central European Summer Time, Kuala Lumpur, Malaysia
  -  Copyright (c) 2022, Raul A Perusquia Flores
  -->
<script setup>
import { Head } from "@inertiajs/vue3"
const props = defineProps(["title", "pageHead", "profile", "pageBody"])
import { trans } from "laravel-vue-i18n"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faUserCircle, faKey, faClone, faPaintBrush, faMoonStars, faLightbulbOn } from "@/../private/pro-light-svg-icons"
library.add(faUserCircle, faKey, faClone, faPaintBrush, faMoonStars, faLightbulbOn)
import Password from "@/Components/Password.vue"
import { useForm } from "@inertiajs/vue3"
import { ref } from "vue"
import Form from "@/Components/Forms/Form.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Switch } from '@headlessui/vue'

const isDark = ref(false)
const profileForm = useForm({
    username: props.profile.username,
    about: props.profile.about,
    email: props.profile.email,
    avatar: null,
    _method: "patch",
})

const passwordForm = useForm({ password: "" })

const current = ref(props["pageBody"].current)

const avatarUploaded = (file) => {
    profileForm.avatar = file
    const reader = new FileReader()
    reader.readAsDataURL(file)
    reader.onload = (e) => {
        props.profile.avatar = e.target.result
    }
}
</script>

<template layout="App">
    <Head :title="title" />
    <div class="ml-0 max-w-screen-xl">
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="divide-y divide-gray-200 lg:grid lg:grid-cols-12 lg:divide-y-0 lg:divide-x">
                <aside class="py-0 lg:col-span-3 lg:h-screen">
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
                                <p class="mt-2 text-sm text-gray-500">
                                    {{ pageBody.layout.profile.fields.about.notes }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex-grow lg:mt-0 lg:ml-6 lg:flex-shrink-0 lg:flex-grow-0">
                            <p class="text-sm font-medium text-gray-700" aria-hidden="true">
                                {{ pageBody.layout.profile.fields["photo"].label }}
                            </p>
                            <div class="mt-1 lg:hidden">
                                <div class="flex items-center">
                                    <div class="inline-block h-12 w-12 flex-shrink-0 overflow-hidden rounded-full"
                                        aria-hidden="true">
                                        <img id="avatar_mobile" class="h-full w-full rounded-full"
                                            :src="route('media.group.show', 1)" alt="" />
                                    </div>
                                    <div class="ml-5 rounded-md shadow-sm">
                                        <div
                                            class="group relative flex items-center justify-center rounded-md border border-gray-300 py-2 px-3 focus-within:ring-2 focus-within:ring-sky-500 focus-within:ring-offset-2 hover:bg-gray-50">
                                            <label for="mobile-user-photo"
                                                class="pointer-events-none relative text-sm font-medium leading-4 text-gray-700">
                                                <span>{{ trans("Change") }}</span>
                                                <span class="sr-only">
                                                    {{
                                                        pageBody.layout.profile.fields["photo"].info
                                                    }}</span>
                                            </label>
                                            <input id="mobile-user-photo" name="user-photo" type="file"
                                                @input="avatarUploaded($event.target.files[0])"
                                                class="absolute h-full w-full cursor-pointer rounded-md border-gray-300 opacity-0" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="relative hidden overflow-hidden rounded-full lg:block">
                                <img class="relative h-40 w-40 rounded-full" :src="route('media.group.show', 1)" alt="" />
                                <label id="desktop-user-photo-mask" for="desktop-user-photo"
                                    class="absolute inset-0 flex h-full w-full items-center justify-center bg-black bg-opacity-50 text-sm font-medium text-white opacity-0 hover:opacity-100">
                                    <span>{{ trans("Change") }}</span>
                                    <span class="sr-only">
                                        {{ pageBody.layout.profile.fields["photo"].info }}</span>
                                    <input type="file" @input="avatarUploaded($event.target.files[0])"
                                        id="desktop-user-photo" name="user-photo"
                                        class="absolute inset-0 h-full w-full cursor-pointer rounded-md border-gray-300 opacity-0" />
                                </label>
                            </div>
                            <div v-if="profileForm.errors.avatar" class="text-red-700">
                                {{ profileForm.errors.avatar }}
                            </div>
                        </div>
                    </div>
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
                <Form v-show="current === 'password'" :form="passwordForm" :layout="pageBody.layout.password">
                    <div class="mt-6 grid grid-cols-12 gap-6">
                        <div class="col-span-12">
                            <label for="email" class="block text-sm font-medium text-gray-700">{{
                                pageBody.layout.password.fields.password.label
                            }}</label>
                            <div class="mt-1 flex rounded-md shadow-sm">
                                <Password id="password" name="password" v-model="passwordForm.password" />
                            </div>
                            <div v-if="passwordForm.errors.password" class="text-red-700">
                                {{ passwordForm.errors.password }}
                            </div>
                        </div>
                    </div>
                </Form>
                <div v-show="current === 'workplaces'" class="divide-y divide-gray-200 lg:col-span-9"></div>

                <!-- Appearance -->
                <div v-show="current === 'appearance'" class="text-sm col-span-9">
                    <div class="grid grid-flow-col pl-4 bg-gray-100">
                        <div class="bg-red-500">Select Mode</div>
                        <div class="bg-yellow-500">
                            <Switch v-model="isDark"
                                :class="[isDark ? 'bg-slate-800' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-1 focus:ring-slate-600 focus:ring-offset-2']">
                                <span class="sr-only">Use setting</span>
                                <span
                                    :class="[isDark ? 'translate-x-5  bg-gray-100' : 'translate-x-0 bg-slate-800', 'pointer-events-none relative inline-block h-5 w-5 transform rounded-full shadow ring-0 transition duration-200 ease-in-out']">
                            
                                    <!-- Light -->
                                    <span
                                        :class="[isDark ? 'opacity-0 duration-100 ease-out' : 'opacity-100 duration-200 ease-in', 'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity']"
                                        aria-hidden="true">
                                        <FontAwesomeIcon aria-hidden="true" icon="fa-light fa-moon-stars" class="text-xs text-gray-100"/>
                                    </span>
                            
                                    <!-- Dark -->
                                    <span
                                        :class="[isDark ? 'opacity-100 duration-200 ease-in' : 'opacity-0 duration-100 ease-out', 'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity']"
                                        aria-hidden="true">
                                        <FontAwesomeIcon aria-hidden="true" icon="fa-light fa-lightbulb-on" class="text-xs text-slate-800"/>
                                    </span>
                                </span>
                            </Switch>
                        </div>
                    </div>
                    <div class="grid grid-flow-col pl-4">
                        <div class="w-64">Select Theme</div>
                        <div class="grid grid-flow-col gap-x-4">
                            <div class="w-6 h-6 rounded-full bg-teal-400"></div>
                            <div class="w-6 h-6 rounded-full bg-sky-400"></div>
                            <div class="w-6 h-6 rounded-full bg-amber-400"></div>
                            <div class="w-6 h-6 rounded-full bg-orange-400"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
