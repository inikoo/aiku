<!--
  - Author: Raul Perusquia <raul@inikoo.com>
  - Created: Mon, 18 Mar 2024 13:45:06 Malaysia Time, Mexico City, Mexico
  - Copyright (c) 2024, Raul A Perusquia Flores
  -->

<script setup lang="ts">
import Button from "@/Components/Elements/Buttons/Button.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { Link, router } from "@inertiajs/vue3";
import Table from "@/Components/Table/Table.vue";
import { FulfilmentCustomer } from "@/types/Customer";
import AddressLocation from "@/Components/Elements/Info/AddressLocation.vue";
import { useFormatTime } from "@/Composables/useFormatTime";
import { useLocaleStore } from "@/Stores/locale";
import { ref } from "vue";
import { routeType } from "@/types/route";
import { trans } from "laravel-vue-i18n"
import ConfirmPopup from 'primevue/confirmpopup'
import { useConfirm } from "primevue/useconfirm"
import { notify } from "@kyvg/vue3-notification"

const confirm = useConfirm()
const props = defineProps<{
    data: object,
    tab?: string,
    detachRoute?: routeType
}>();

const locale = useLocaleStore();
const isModalUploadOpen = ref(false)

function mediaRoute(attachment: {}) {
    return route(
        "grp.media.download",
        [
            attachment.media_ulid
        ]);;
}

// function customerRoute(customer: FulfilmentCustomer) {
//     switch (route().current()) {
//         case "shops.show.customers.index":
//             return route(
//                 "grp.org.shops.show.crm.customers.show",
//                 [customer.shop_slug, customer.slug]);
//         case "grp.fulfilment.customers.index":
//             return route(
//                 "grp.fulfilment.customers.show",
//                 [customer.slug]);
//         default:
//             return route(
//                 "grp.org.shops.show.crm.customers.show",
//                 [
//                     route().params["organisation"],
//                     route().params["shop"],
//                     customer.slug
//                 ]);
//     }
// }

// function shopRoute(customer: FulfilmentCustomer) {
//     switch (route().current()) {
//         case "shops.show.customers.index":
//             return route(
//                 "shops.show",
//                 [customer.shop_slug]);
//         default:
//             return route(
//                 "shops.show",
//                 [customer.shop_slug]);
//     }
// }

const isLoading = ref<number[]>([])
const onSendData = (routeLink: routeType, media_id: number, id: number) => {
    router.delete(
        route(props.detachRoute?.name, {...props.detachRoute?.parameters, attachment: media_id}),
        {
            onStart: () => {
                isLoading.value.push(id)
            },
            onFinish: () => {
                const index = isLoading.value.indexOf(id);
                if (index > -1) {
                    isLoading.value.splice(index, 1);
                }
            },
            preserveScroll: true,
            preserveState: true
        }
    )
}

const confirmDelete = (event, media_id: number, id: number) => {
    confirm.require({
        target: event.currentTarget,
        message: trans('Are you sure you want to delete?'),
        group: 'headless',
        // rejectProps: {
        //     label: 'Cancel',
        //     severity: 'secondary',
        //     outlined: true
        // },
        // acceptProps: {
        //     severity: 'danger',
        //     label: 'Delete'
        // },
        accept: () => {

            onSendData(route, media_id, id)
        },
        // reject: () => {
        //     notify({
        //         title: trans('Something went wrong'),
        //         text: trans('Failed to delete attachment'),
        //         type: 'error',
        //     })
        // }
    });
}
</script>

<template>
    <Table :resource="data" :name="tab" class="mt-5">
        <template #cell(scope)="{ item: attachment }">
            {{ attachment["scope"] }}
        </template>
        <template #cell(caption)="{ item: attachment }">
            {{ attachment["caption"] }}
        </template>
        <template #cell(action)="{ item: attachment }">
            <div class="flex gap-x-2">
                <Link :href="mediaRoute(attachment)">
                    <Button
                        type="tertiary"
                        icon="fal fa-download"
                        v-tooltip="trans('Download attachment')"
                    />
                </Link>


                <!-- <Link
                    v-if="detachRoute?.name"
                    :href="route(detachRoute?.name, {...detachRoute?.parameters, attachment: attachment.media_id})" :method="detachRoute?.method"
                    
                    preserve-scroll
                    as="div"
                > -->
                <Button
                    @click="(e) => confirmDelete(e, attachment.media_id, attachment.id)"
                    type="negative"
                    icon="fal fa-trash-alt"
                    :loading="isLoading.includes(attachment.id)"
                    v-tooltip="trans('Delete attachment')"
                />
                
                <!-- </Link> -->
                <ConfirmPopup group="headless">
                    <template #container="{ message, acceptCallback, rejectCallback }">
                        <div class="rounded p-4">
                            <span>{{ message.message }}</span>
                            <div class="flex items-center gap-2 mt-4">
                                <Button label="Cancel" :style="'tertiary'" full @click="rejectCallback" />
                                <Button label="Delete" :style="'red'" @click="acceptCallback" />
                            </div>
                        </div>
                    </template>
                </ConfirmPopup>
            </div>
        </template>
    </Table>
</template>
