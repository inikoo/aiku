<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { computed } from "vue"
import { Head, router } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import { library } from "@fortawesome/fontawesome-svg-core"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { faExternalLink, faPlug } from "@fal"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import Button from "@/Components/Elements/Buttons/Button.vue"
import { capitalize } from "@/Composables/capitalize"
import { ctrans } from "@/Composables/useTrans"
import { useFormatTime } from "@/Composables/useFormatTime"
import { PageHeadingTypes } from "@/types/PageHeading"
import { routeType } from "@/types/route"

library.add(faExternalLink, faPlug)

type Container = {
    id: number
    name: string
    public_id: string
    usage_context: string | null
    domain_names: string[]
    tag_manager_url: string | null
}

type Account = { account_id: string, account_name: string, containers: Container[] }

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    data: {
        is_connected: boolean
        connected_at: string | null
        fetched_at: string | null
        can_connect: boolean
        connect_route: routeType
        accounts: Account[]
    }
}>()

const containerCount = computed(() => props.data.accounts.reduce((total, account) => total + account.containers.length, 0))

const connect = () => router.get(route(props.data.connect_route.name, props.data.connect_route.parameters))

const usageLabels: Record<string, string> = {
    web: ctrans("Web"),
    ios: "iOS",
    android: "Android",
    amp: "AMP",
    server: ctrans("Server"),
}

const usageLabel = (usage: string | null) => usage ? (usageLabels[usage] ?? usage) : "-"
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template v-if="data.can_connect" #other>
            <Button
                type="tertiary"
                :label="data.is_connected ? ctrans('Reconnect Google account') : ctrans('Connect Google account')"
                @click="connect"
            >
                <template #icon>
                    <FontAwesomeIcon :icon="faPlug" class="text-green-600" fixed-width aria-hidden="true" />
                </template>
            </Button>
        </template>
    </PageHeading>

    <section v-if="!data.is_connected" class="m-4 rounded-xl bg-white px-5 py-6 ring-1 ring-gray-200">
        <h2 class="text-sm font-medium text-gray-900">{{ ctrans("Google Tag Manager is not connected") }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ data.can_connect
                ? ctrans("Connect the Google account that has access to your Tag Manager accounts. Only read access is requested.")
                : ctrans("Ask a group admin to connect the Google account that has access to the Tag Manager accounts.") }}
        </p>
    </section>

    <section v-else-if="!data.accounts.length" class="m-4 rounded-xl bg-white px-5 py-6 ring-1 ring-gray-200">
        <h2 class="text-sm font-medium text-gray-900">{{ ctrans("No containers yet") }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ ctrans("The accounts are being fetched, or the connected Google account has none. Check again in a few minutes.") }}</p>
    </section>

    <div v-else class="m-4 space-y-4">
        <p class="text-xs text-gray-500">
            {{ ctrans(":accounts accounts, :containers containers", { accounts: data.accounts.length, containers: containerCount }) }}
            <template v-if="data.fetched_at"> · {{ ctrans("fetched :time", { time: useFormatTime(data.fetched_at, { formatTime: "dd MMM yyyy HH:mm" }) }) }}</template>
        </p>

        <section
            v-for="account in data.accounts"
            :key="account.account_id"
            :aria-labelledby="`gtm-account-${account.account_id}`"
            class="rounded-xl bg-white ring-1 ring-gray-200"
        >
            <h2 :id="`gtm-account-${account.account_id}`" class="border-b border-gray-100 px-5 py-3 text-sm font-medium text-gray-900">
                {{ account.account_name }}
                <span class="ml-2 text-xs font-normal text-gray-500">{{ account.account_id }}</span>
            </h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs text-gray-500">
                        <th scope="col" class="px-5 py-2 font-medium">{{ ctrans("Container") }}</th>
                        <th scope="col" class="px-3 py-2 font-medium">{{ ctrans("Type") }}</th>
                        <th scope="col" class="px-3 py-2 font-medium">{{ ctrans("Domains") }}</th>
                        <th scope="col" class="px-5 py-2 font-medium">{{ ctrans("Container ID") }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="container in account.containers" :key="container.id">
                        <td class="px-5 py-2 text-gray-900">
                            <a
                                v-if="container.tag_manager_url"
                                :href="container.tag_manager_url"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1.5 underline-offset-2 hover:underline focus-visible:underline"
                            >
                                {{ container.name }}
                                <FontAwesomeIcon :icon="faExternalLink" class="text-xs text-gray-400" aria-hidden="true" />
                            </a>
                            <template v-else>{{ container.name }}</template>
                        </td>
                        <td class="px-3 py-2 text-gray-600">{{ usageLabel(container.usage_context) }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ container.domain_names.join(", ") || "-" }}</td>
                        <td class="px-5 py-2 tabular-nums text-gray-900">{{ container.public_id }}</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
</template>
