<script setup>
import ContactUsGreenStuff from "@/../../public/Images/ContactUsGreenStuff.png";
import {ref} from "vue";
const agreed = ref(false)
import {Switch} from '@headlessui/vue'

defineProps(['data'])


</script>

<template>
    <div class="relative bg-white md:h-screen">
        <div class="relative h-56 bg-indigo-600 hidden md:block md:absolute md:left-0 md:h-full md:w-1/2">
            <img class="h-full w-full object-cover"
                 :src="ContactUsGreenStuff"
                 alt=""/>
        </div>
        <div class="relative mx-auto max-w-md  px-6 sm:max-w-7xl py-10 lg:px-1">
            <div class="md:ml-auto md:w-1/2 md:pl-10 ">
                <div class="overflow-hidden bg-white  px-6 lg:px-8 ">
                    <div class="relative mx-auto max-w-xl">
                        <svg class="absolute left-full translate-x-1/2 transform" width="404" height="404"
                             fill="none" viewBox="0 0 404 404" aria-hidden="true">
                            <defs>
                                <pattern id="85737c0e-0916-41d7-917f-596dc7edfa27" x="0" y="0" width="20"
                                         height="20" patternUnits="userSpaceOnUse">
                                    <rect x="0" y="0" width="4" height="4" class="text-gray-200"
                                          fill="currentColor"/>
                                </pattern>
                            </defs>
                            <rect width="404" height="404" fill="url(#85737c0e-0916-41d7-917f-596dc7edfa27)"/>
                        </svg>
                        <svg class="absolute right-full bottom-0 -translate-x-1/2 transform" width="404"
                             height="404" fill="none" viewBox="0 0 404 404" aria-hidden="true">
                            <defs>
                                <pattern id="85737c0e-0916-41d7-917f-596dc7edfa27" x="0" y="0" width="20"
                                         height="20" patternUnits="userSpaceOnUse">
                                    <rect x="0" y="0" width="4" height="4" class="text-gray-200"
                                          fill="currentColor"/>
                                </pattern>
                            </defs>
                            <rect width="404" height="404" fill="url(#85737c0e-0916-41d7-917f-596dc7edfa27)"/>
                        </svg>
                        <div class="text-center">
                            <h2 class="text-6xl font-bold tracking-tight text-gray-900 sm:text-4xl md:text-5xl lg:text-6xl">
                                {{data.title}}</h2>
                        </div>
                        <div class="mt-12">
                            <form action="#" method="POST"
                                  class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
                                <div>
                                    <label for="first-name" class="block text-sm font-medium text-gray-700">{{data.form.name}}</label>
                                    <div class="mt-1">
                                        <input type="text" name="first-name" id="first-name"
                                               autocomplete="given-name"
                                               class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                                    </div>
                                </div>
                                <div>
                                    <label for="last-name" class="block text-sm font-medium text-gray-700">{{data.form.lastName}}</label>
                                    <div class="mt-1">
                                        <input type="text" name="last-name" id="last-name"
                                               autocomplete="family-name"
                                               class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="email" class="block text-sm font-medium text-gray-700">{{data.form.email}}</label>
                                    <div class="mt-1">
                                        <input id="email" name="email" type="email" autocomplete="email"
                                               class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="phone-number" class="block text-sm font-medium text-gray-700">{{data.form.phoneNumber}}</label>
                                    <div class="relative mt-1 rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 flex items-center">
                                            <label for="country" class="sr-only">Country</label>
                                            <select id="country" name="country"
                                                    class="h-full rounded-md border-transparent bg-transparent py-0 pl-4 pr-8 text-gray-500 focus:border-indigo-500 focus:ring-indigo-500">
                                                <option>US</option>
                                                <option>CA</option>
                                                <option>EU</option>
                                            </select>
                                        </div>
                                        <input type="text" name="phone-number" id="phone-number" autocomplete="tel"
                                               class="block w-full rounded-md border-gray-300 py-3 px-4 pl-20 focus:border-indigo-500 focus:ring-indigo-500"
                                               placeholder="+1 (555) 987-6543"/>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <label for="message"
                                           class="block text-sm font-medium text-gray-700">{{data.form.message}}</label>
                                    <div class="mt-1">
                                            <textarea id="message" name="message" rows="4"
                                                      class="block w-full rounded-md border-gray-300 py-3 px-4 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <Switch v-model="agreed"
                                                    :class="[agreed ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2']">
                                                <span class="sr-only">Agree to policies</span>
                                                <span aria-hidden="true"
                                                      :class="[agreed ? 'translate-x-5' : 'translate-x-0', 'inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']"/>
                                            </Switch>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-base text-gray-500">
                                                {{data.privacyPolicy}}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:col-span-2">
                                    <button type="submit"
                                            class="inline-flex w-full text-blac items-center justify-center rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        {{data.button}}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>


