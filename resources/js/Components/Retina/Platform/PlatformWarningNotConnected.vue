<script setup lang="ts">
import Button from '@/Components/Elements/Buttons/Button.vue'
import { CustomerSalesChannel } from '@/types/customer-sales-channel'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faCheck, faTimes, faRedoAlt, faExternalLink, faSpinnerThird } from '@fal'
import { notify } from '@kyvg/vue3-notification'
import axios from 'axios'
import { ctrans } from '@/Composables/useTrans'
import { Message } from 'primevue'
import ButtonWithLink from "@/Components/Elements/Buttons/ButtonWithLink.vue"
import { router } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from "vue";

library.add(faCheck, faTimes, faRedoAlt, faExternalLink, faSpinnerThird)

const props = defineProps<{
    platform: any
    customer_sales_channel: CustomerSalesChannel
    error_captcha: any
}>()

// Method: Platform reconnect
const onClickReconnect = async () => {
    const reconnectTab = window.open('', '_blank')

    try {
        const response = await axios[props.customer_sales_channel.reconnect_route.method || 'get'](
            route(
                props.customer_sales_channel.reconnect_route.name,
                props.customer_sales_channel.reconnect_route.parameters
            )
        )
        if (response.status !== 200) {
            throw new Error('Something went wrong. Try again later.')
        } else if (response.data.id) {
            reconnectTab?.close()
            window.location.href = ''
        } else if (reconnectTab) {
            reconnectTab.location.href = response.data
        } else {
            window.location.href = response.data
        }
    } catch (error: any) {
        reconnectTab?.close()
        notify({
            title: 'Something went wrong',
            text: error.message || 'Please try again later.',
            type: 'error'
        })
    }
}

const errorCaptcha = ref(null)
const isChecking = ref(false)
const serverProbe = computed(() => props.customer_sales_channel.woo_server_probe)

const POLL_EVERY_MS = 5000
const POLL_FOR_MS = 90000
let pollTimer: ReturnType<typeof setTimeout> | null = null
let isUnmounted = false

const notifyServerProbe = () => {
    const servers = serverProbe.value?.servers ?? []

    if (serverProbe.value?.checking) {
        notify({
            title: ctrans('Still checking'),
            text: ctrans('Some of our servers have not answered yet, come back to this page in a few minutes.'),
            type: 'info'
        })
    } else if (servers.length && servers.every((server) => server.ok)) {
        notify({
            title: ctrans('Connection successful'),
            text: ctrans('Your store answers all our servers, your uploads start again.'),
            type: 'success'
        })
    } else {
        notify({
            title: ctrans('Connection failed'),
            text: ctrans('Your store does not answer all our servers yet, your uploads stay paused.'),
            type: 'error'
        })
    }
}

const pollServerProbe = (startedAt: number) => {
    router.reload({
        only: ['customer_sales_channel', 'is_platform_connected', 'platform_status', 'can_connect_to_platform'],
        onFinish: () => {
            if (isUnmounted) {
                return
            }

            if (serverProbe.value?.checking && Date.now() - startedAt < POLL_FOR_MS) {
                pollTimer = setTimeout(() => pollServerProbe(startedAt), POLL_EVERY_MS)
            } else {
                isChecking.value = false
                notifyServerProbe()
            }
        }
    })
}

onBeforeUnmount(() => {
    isUnmounted = true
    if (pollTimer) {
        clearTimeout(pollTimer)
    }
})

// Method: ask the store from every one of our servers
const onClickTestConnection = async () => {
    isChecking.value = true

    try {
        const response = await axios[props.customer_sales_channel.test_route.method || 'get'](
            route(
                props.customer_sales_channel.test_route.name,
                props.customer_sales_channel.test_route.parameters
            )
        )

        errorCaptcha.value = response.data?.data?.error_data

        if (response.data?.checking) {
            pollTimer = setTimeout(() => pollServerProbe(Date.now()), POLL_EVERY_MS)

            return
        }

        notify({
            title: ctrans('Connection successful'),
            text: ctrans('The connection to your WooCommerce store is working.'),
            type: 'success'
        })
        router.reload()
    } catch (error: any) {
        notify({
            title: ctrans('Connection failed'),
            text: error.response?.data?.message || ctrans('Please try again later.'),
            type: 'error'
        })
        router.reload({ only: ['customer_sales_channel'] })
    }

    isChecking.value = false
}

</script>

<template>
    <Message severity="error" class="mt-8" v-if="customer_sales_channel.user_data?.length > 0">
        <span>{{ ctrans('Error message from your woocommerce store when we access the API: ') }}</span>
        <div class="ml-2 font-normal flex flex-col gap-x-4 items-center sm:flex-row justify-between w-full">
            <div>
                <FontAwesomeIcon icon="fad fa-exclamation-triangle" class="text-xl" fixed-width aria-hidden="true"/>
                <div class="inline items-center gap-x-2">
                    {{ JSON.parse(customer_sales_channel.user_data)?.code }} : {{ JSON.parse(customer_sales_channel.user_data)?.message }}
                </div>
            </div>
        </div>
    </Message>

    <Message severity="error" class="mt-8 ">
        <div class="ml-2 font-normal flex flex-col gap-x-4 items-center sm:flex-row justify-between w-full">
            <div>
                <FontAwesomeIcon icon="fad fa-exclamation-triangle" class="text-xl" fixed-width aria-hidden="true"/>
                <div class="inline items-center gap-x-2">
                    {{
                        customer_sales_channel.not_ready_reason
                            || ctrans("Your channel is not connected yet to the platform. Please connect it to be able to synchronize your products.")
                    }}
                </div>
                <div v-if="customer_sales_channel.woo_uploads_waiting" class="mt-2">
                    {{
                        ctrans(":count products are waiting to be uploaded. They are paused while your store does not answer, and start again by themselves once it does.", { count: customer_sales_channel.woo_uploads_waiting })
                    }}
                </div>
            </div>
            <div class="w-full sm:w-fit h-fit shrink-0">
                <Button v-if="customer_sales_channel?.reconnect_route?.name"
                    @click="() => onClickReconnect()"
                    :label="ctrans('Try to reconnect')" type="tertiary" full
                >
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-external-link" class="text-gray-500" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </div>
    </Message>

    <Message severity="warn" class="mt-8" v-if="customer_sales_channel.type === 'woocommerce'">
        <div class="ml-2 font-normal flex flex-col gap-x-4 items-center sm:flex-row justify-between w-full">
            <div>
                <div class="inline items-center gap-x-2">
                    {{
                        ctrans("Once your store is back, or your hosting allows our servers, try again: we check from every one of our servers.")
                    }}
                </div>
            </div>

            <div class="w-full sm:w-fit h-fit shrink-0">
                <Button
                    :label="ctrans('Try again')"
                    type="tertiary"
                    :loading="isChecking"
                    :disabled="isChecking"
                    @click="() => onClickTestConnection()"
                >
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-redo-alt" class="text-green-600" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </div>
        </div>
        <ul v-if="serverProbe?.servers?.length || serverProbe?.checking" class="ml-2 mt-3 space-y-1 text-sm font-normal">
            <li v-for="(server, index) in serverProbe.servers" :key="index" class="flex items-start gap-x-2">
                <FontAwesomeIcon v-if="server.ok" icon="fal fa-check" class="mt-0.5 text-green-600" fixed-width aria-hidden="true" />
                <FontAwesomeIcon v-else icon="fal fa-times" class="mt-0.5 text-red-500" fixed-width aria-hidden="true" />
                <div>
                    <span class="whitespace-nowrap font-medium">{{ server.ip || ctrans('One of our servers') }}</span>
                    <span v-if="server.ok"> {{ ctrans('answered') }}</span>
                    <span v-else>: {{ server.reason || ctrans('no answer') }}</span>
                </div>
            </li>
            <li v-if="serverProbe?.checking && isChecking" class="flex items-center gap-x-2 text-gray-500">
                <FontAwesomeIcon icon="fal fa-spinner-third" class="animate-spin" fixed-width aria-hidden="true" />
                {{ ctrans('Checking from our servers') }}
            </li>
        </ul>
        <div v-if="errorCaptcha" class="ml-2">
            <small class="text-red-500">{{errorCaptcha}}</small>
        </div>
    </Message>

    <Message severity="error" class="mt-8 ">
        <div class="ml-2 font-normal flex flex-col gap-x-4 items-center sm:flex-row justify-between w-full">
            <div>
                <div class="inline items-center gap-x-2">
                    {{
                        ctrans("Or delete the channel and try again")
                    }}
                </div>
            </div>

            <div class="w-full sm:w-fit h-fit shrink-0">

                <ButtonWithLink
                    :label="ctrans('Delete')"
                    type="delete"
                    :routeTarget="customer_sales_channel?.delete_route"
                />
            </div>
        </div>
    </Message>


</template>
