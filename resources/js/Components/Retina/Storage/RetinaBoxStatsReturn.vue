<script setup lang="ts">
import { ctrans } from "@/Composables/useTrans";
import BoxStatPallet from "@/Components/Pallet/BoxStatPallet.vue";
import DatePicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { useFormatTime, retinaUseDaysLeftFromToday } from "@/Composables/useFormatTime";
import { notify } from "@kyvg/vue3-notification";
import { router } from "@inertiajs/vue3";
import Popover from "@/Components/Popover.vue";
import { PalletDelivery, BoxStats, PalletReturn, PDRNotes } from "@/types/Pallet";
import Modal from "@/Components/Utils/Modal.vue";
import ToggleSwitch from "primevue/toggleswitch";
import RadioButton from "primevue/radiobutton";
import Select from "primevue/select";
import { computed, inject, onMounted, onUnmounted, ref } from "vue";
import { capitalize } from "@/Composables/capitalize";
import { routeType } from "@/types/route";
import LoadingIcon from "@/Components/Utils/LoadingIcon.vue";
import { layoutStructure } from "@/Composables/useLayoutStructure";
import RetinaBoxNote from "@/Components/Retina/Storage/RetinaBoxNote.vue";
import OrderSummary from "@/Components/Summary/OrderSummary.vue";
import CustomerAddressManagementModal from "@/Components/Utils/CustomerAddressManagementModal.vue";
import DeliveryAddressManagementModal from "@/Components/Utils/DeliveryAddressManagementModal.vue";
import Textarea from "primevue/textarea";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { faBuilding, faIdCardAlt, faMapMarkerAlt, faPencil, faPenSquare, faCalendarDay, faShippingFast } from "@fal";
import { library } from "@fortawesome/fontawesome-svg-core";
import PalletEditCustomerReference from "@/Components/Pallet/PalletEditCustomerReference.vue";
import { AddressManagement } from "@/types/PureComponent/Address";

library.add(faBuilding, faIdCardAlt, faMapMarkerAlt, faPenSquare, faPencil, faCalendarDay, faShippingFast);

const props = defineProps<{
  box_stats: BoxStats
  data_pallet: PalletDelivery | PalletReturn
  notes_data: { [key: string]: PDRNotes }
  address_management: {
    updateRoute: routeType
    addresses: AddressManagement
    address_update_route: routeType
    address_modal_title: string
  },
}>();

const layout = inject("layout", layoutStructure);
const deliveryListError = inject("deliveryListError", []);

// Section: Box Stats loading state
const isBoxStatsLoading = ref(false)
let removeStartListener: (() => void) | null = null
let removeFinishListener: (() => void) | null = null
onMounted(() => {
  removeStartListener = router.on('start', (event) => {
    if (event.detail.visit.only?.includes('box_stats')) {
      isBoxStatsLoading.value = true
    }
  })
  removeFinishListener = router.on('finish', () => {
    isBoxStatsLoading.value = false
  })
})
onUnmounted(() => {
  removeStartListener?.()
  removeFinishListener?.()
})

const isLoadingSetEstimatedDate = ref<string | boolean>(false);
const isModalAddress = ref(false);
const isDeliveryAddressManagementModal = ref(false);
const enabled = ref(props.data_pallet?.is_collection || false);
const isLoading = ref<string | boolean>(false);
const textValue = ref(props.box_stats?.collection_notes);

// ✅ Auto-select collectionBy based on existing notes
const collectionBy = ref(props.box_stats?.collection_notes ? "thirdParty" : "myself");

// Computed property for switch toggle (Collection switch)
const computedEnabled = computed({
  get() {
    return enabled.value;
  },
  set(newValue: boolean) {
    const addressID = props.box_stats.fulfilment_customer.address?.address_customer?.value.id;
    const address = props.box_stats.fulfilment_customer.address?.address_customer?.value;

    if (!newValue) {
      // If NOT collection, reset to the selected address
      const filterDataAddress = { ...address };
      delete filterDataAddress.formatted_address;
      delete filterDataAddress.country;
      delete filterDataAddress.id;

      router[
      props.box_stats.fulfilment_customer.address.routes_address.store.method || "post"
        ](
        route(
          props.box_stats.fulfilment_customer.address.routes_address.store.name,
          props.box_stats.fulfilment_customer.address.routes_address.store.parameters
        ),
        {
          delivery_address_id: props.address_management.addresses?.current_selected_address_id || props.address_management.addresses?.pinned_address_id || props.address_management.addresses?.home_address_id
        },
        {
          preserveScroll: true,
          onSuccess: () => {
            notify({
              title: ctrans("Success"),
              text: ctrans("Set the address to selected address."),
              type: "success"
            });
          },
          onError: () =>
            notify({
              title: ctrans("Something went wrong"),
              text: ctrans("Failed to submit the address, try again"),
              type: "error"
            })
        }
      );
    } else {
      try {
        router.delete(
          route(props.box_stats.fulfilment_customer.address.routes_address.delete.name, {
            ...props.box_stats.fulfilment_customer.address.routes_address.delete.parameters
          }),
          {
            preserveScroll: true,
            onStart: () => (isLoading.value = "onDelete" + addressID),
            onFinish: () => {
              isLoading.value = false;
            }
          }
        );
        notify({
          title: ctrans("Success"),
          text: ctrans("Set the address to follow collection."),
          type: "success"
        });
      } catch (error) {
        console.error("Error disabling collection:", error);
        notify({
          title: ctrans("Something went wrong"),
          text: ctrans("Failed to disable collection."),
          type: "error"
        });
      }
    }
    enabled.value = newValue;
  }
});

// Update collection type (myself or thirdParty)
function updateCollectionType() {
  const payload: Record<string, any> = {
    collection_by: collectionBy.value
  };

  // Logic:
  // - If "myself", clear the notes (set to null)
  // - If "thirdParty", preserve whatever is in textValue (even empty string)
  if (collectionBy.value === "myself") {
    payload.collection_notes = null;
    textValue.value = null; // also clear in frontend
  }

  router.patch(
    route(props.address_management.updateRoute.name, props.address_management.updateRoute.parameters),
    payload,
    {
      preserveScroll: true,
      onSuccess: () => {
        notify({
          title: ctrans("Success"),
          text: ctrans("Collection type updated successfully"),
          type: "success"
        });
      },
      onError: () => {
        notify({
          title: ctrans("Something went wrong"),
          text: ctrans("Failed to update collection type"),
          type: "error"
        });
      }
    }
  );
}


const selectedCourier = ref<string | null>(props.box_stats?.customer_shipping?.courier ?? null);
const isLoadingCourier = ref(false);

function updateCourier() {
  router.patch(
    route(props.address_management.updateRoute.name, props.address_management.updateRoute.parameters),
    { customer_courier: selectedCourier.value },
    {
      preserveScroll: true,
      onStart: () => (isLoadingCourier.value = true),
      onSuccess: () => {
        notify({
          title: ctrans("Success"),
          text: ctrans("Courier updated"),
          type: "success"
        });
      },
      onError: () => {
        selectedCourier.value = props.box_stats?.customer_shipping?.courier ?? null;
        notify({
          title: ctrans("Something went wrong"),
          text: ctrans("Failed to update the courier, try again"),
          type: "error"
        });
      },
      onFinish: () => (isLoadingCourier.value = false)
    }
  );
}

// Update notes for thirdParty
function updateCollectionNotes() {
  router.patch(
    route(props.address_management.updateRoute.name, props.address_management.updateRoute.parameters),
    { collection_notes: textValue.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        notify({
          title: ctrans("Success"),
          text: ctrans("Text updated successfully"),
          type: "success"
        });
      },
      onError: () => {
        notify({
          title: ctrans("Something went wrong"),
          text: ctrans("Failed to update text"),
          type: "error"
        });
      }
    }
  );
}

// Estimated delivery date change
const onChangeEstimateDate = async (close: Function) => {
  try {
    router.patch(
      route(props.address_management.updateRoute.name, props.address_management.updateRoute.parameters),
      {
        estimated_delivery_date: props.data_pallet.estimated_delivery_date
      },
      {
        onStart: () => isLoadingSetEstimatedDate.value = true,
        onError: () => {
          notify({
            title: "Failed",
            text: "Failed to update the Delivery date, try again.",
            type: "error"
          });
        },
        onSuccess: () => {
          const index = deliveryListError?.indexOf("estimated_delivery_date");
          if (index > -1) {
            deliveryListError?.splice(index, 1);
          }
          close();
        },
        onFinish: () => isLoadingSetEstimatedDate.value = false
      }
    );
  } catch (error) {
    notify({
      title: "Failed",
      text: "Failed to update the Delivery date, try again.",
      type: "error"
    });
  }
};

// Disable selecting past dates
const disableBeforeToday = (date: Date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return date < today;
};
</script>


<template>
  <div class="relative">
  <Transition name="fade">
    <div v-if="isBoxStatsLoading" class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 flex items-center justify-center z-10 pointer-events-none">
      <FontAwesomeIcon icon="fad fa-spinner-third" class="animate-spin text-2xl text-gray-400" fixed-width aria-hidden="true" />
    </div>
  </Transition>
  <div class="h-min grid sm:grid-cols-2 lg:grid-cols-4 border-t border-b border-gray-200 divide-x divide-gray-300">
    <!-- Box: Detail -->
    <BoxStatPallet :color="{ bgColor: layout.app.theme[0], textColor: layout.app.theme[1] }" class="pb-2 py-5 px-3"
                   :tooltip="ctrans('Detail')" :label="capitalize(data_pallet.state)" icon="fal fa-truck-couch">
      <!-- Field: Reference -->
      <dl as="a" v-if="box_stats.fulfilment_customer.customer.reference"
           class="flex items-center w-fit flex-none gap-x-2">
        <dt v-tooltip="'Company name'" class="flex-none">
          <span class="sr-only">Reference</span>
          <FontAwesomeIcon icon="fal fa-id-card-alt" size="xs" class="text-gray-400" fixed-width aria-hidden="true" />
        </dt>
        <dd class="text-gray-500">{{ box_stats.fulfilment_customer.customer.reference }}</dd>
      </dl>
      <!-- Field: Contact name -->
      <dl v-if="box_stats.fulfilment_customer.customer.contact_name"
           class="flex items-center w-full flex-none gap-x-2">
        <dt v-tooltip="'Contact name'" class="flex-none">
          <span class="sr-only">Contact name</span>
          <FontAwesomeIcon icon="fal fa-user" size="xs" class="text-gray-400" fixed-width aria-hidden="true" />
        </dt>
        <dd class="text-gray-500">{{ box_stats.fulfilment_customer.customer.contact_name }}</dd>
      </dl>
      <!-- Field: Company name -->
      <dl v-if="box_stats.fulfilment_customer.customer.company_name"
           class="flex items-center w-full flex-none gap-x-2">
        <dt v-tooltip="'Company name'" class="flex-none">
          <span class="sr-only">Company name</span>
          <FontAwesomeIcon icon="fal fa-building" size="xs" class="text-gray-400" fixed-width aria-hidden="true" />
        </dt>
        <dd class="text-gray-500">{{ box_stats.fulfilment_customer.customer.company_name }}</dd>
      </dl>
      <!-- Estimated Delivery Date -->
      <dl class="flex items-center w-full flex-none gap-x-2" :class="deliveryListError.includes('estimated_delivery_date') ? 'errorShake' : ''">
        <dt class="flex-none">
          <span class="sr-only">{{ box_stats.delivery_state.tooltip }}</span>
          <FontAwesomeIcon :icon="['fal', 'calendar-day']" :class="box_stats?.delivery_status?.class" fixed-width aria-hidden="true" size="xs" />
        </dt>
        <Popover v-if="data_pallet.state === 'in_process'" position="">
          <template #button>
            <div v-if="data_pallet?.estimated_delivery_date"
                 v-tooltip="retinaUseDaysLeftFromToday(data_pallet?.estimated_delivery_date)"
                 class="group text-sm text-gray-500">
              {{ useFormatTime(data_pallet?.estimated_delivery_date) }}
              <FontAwesomeIcon icon="fal fa-pencil" size="sm" class="text-gray-400 group-hover:text-gray-600" fixed-width aria-hidden="true" />
            </div>
            <div v-else class="text-sm text-gray-500 hover:text-gray-600 underline">
              {{ ctrans("Set estimated delivery") }}
            </div>
          </template>
          <template #content="{ close }">
            <DatePicker v-model="data_pallet.estimated_delivery_date"
                        @update:modelValue="() => onChangeEstimateDate(close)" inline auto-apply
                        :xdisabled-dates="disableBeforeToday" :enable-time-picker="false" />
            <div v-if="isLoadingSetEstimatedDate" class="absolute inset-0 bg-white/70 flex items-center justify-center">
              <LoadingIcon class="text-5xl" />
            </div>
          </template>
        </Popover>
        <div v-else>
          <dd class="text-sm text-gray-500">
            {{ data_pallet?.estimated_delivery_date ? useFormatTime(data_pallet?.estimated_delivery_date) : ctrans("Not Set") }}
          </dd>
        </div>
      </dl>
      <!-- Delivery Address / Collection by Section -->
      <div class="flex flex-col w-full gap-y-2 mb-1">
        <!-- Top Row: Icon dan Switch -->
        <dl class="flex items-center gap-x-2">
          <dt v-tooltip="ctrans(`Pallet Return's address`)" class="flex-none">
            <span class="sr-only">Delivery address</span>
            <FontAwesomeIcon icon="fal fa-map-marker-alt" size="xs" class="text-gray-400" fixed-width aria-hidden="true" />
          </dt>
          <div class="flex items-center">
            <ToggleSwitch v-model="computedEnabled" inputId="return-for-collection" class="return-toggle" />
            <label for="return-for-collection" class="ml-3 text-sm font-medium text-gray-900 cursor-pointer">
              {{ ctrans("For collection") }}
            </label>
          </div>
        </dl>

        <div v-if="data_pallet.is_collection" class="w-full">
          <span class="block mb-1">{{ ctrans("Collection by:") }}</span>
          <div class="flex space-x-4">
            <div class="inline-flex items-center">
              <RadioButton v-model="collectionBy" inputId="collection-by-myself" value="myself" class="return-radio" @change="updateCollectionType" />
              <label for="collection-by-myself" class="ml-2 cursor-pointer">{{ ctrans("My Self") }}</label>
            </div>
            <div class="inline-flex items-center">
              <RadioButton v-model="collectionBy" inputId="collection-by-third-party" value="thirdParty" class="return-radio" @change="updateCollectionType" />
              <label for="collection-by-third-party" class="ml-2 cursor-pointer">{{ ctrans("Third Party") }}</label>
            </div>
          </div>

          <div v-if="collectionBy === 'thirdParty'" class="mt-3">
						<Textarea
              v-model="textValue"
              @blur="updateCollectionNotes"
              autoResize
              rows="5"
              class="w-full"
              cols="30"
              placeholder="Type additional notes..."
            />
          </div>
        </div>
        <div v-else class="w-full text-xs text-gray-500">
          Send to:
          <div class="relative px-2.5 py-2 ring-1 ring-gray-300 rounded bg-gray-50">
            <span v-html="box_stats.fulfilment_customer?.address?.value?.formatted_address" />
            <div
              @click="() => (isDeliveryAddressManagementModal = true)"
              class="whitespace-nowrap select-none text-gray-500 hover:text-[--app-accent] underline cursor-pointer">
              <span>{{ctrans('Edit')}}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Courier & shipping label -->
      <div v-if="!data_pallet.is_collection && box_stats.customer_shipping" class="flex flex-col w-full gap-y-1 mb-1">
        <dl class="flex items-center gap-x-2">
          <dt v-tooltip="ctrans('Courier')" class="flex-none">
            <span class="sr-only">{{ ctrans("Courier") }}</span>
            <FontAwesomeIcon icon="fal fa-shipping-fast" size="xs" class="text-gray-400" fixed-width aria-hidden="true" />
          </dt>
          <dd class="w-full">
            <Select
              v-model="selectedCourier"
              :options="box_stats.customer_shipping.courier_options"
              optionLabel="name"
              optionValue="code"
              :placeholder="ctrans('Courier of your label')"
              :disabled="['dispatched', 'cancel'].includes(data_pallet.state) || isLoadingCourier"
              :loading="isLoadingCourier"
              :showClear="data_pallet.state === 'in_process'"
              size="small"
              class="w-full"
              @change="updateCourier"
            />
          </dd>
        </dl>
        <div class="text-xs text-gray-500">
          <template v-if="box_stats.customer_shipping.shipping_label">
            {{ ctrans("Your shipping label") }}:
            <a
              :href="route(box_stats.customer_shipping.shipping_label.download_route.name, box_stats.customer_shipping.shipping_label.download_route.parameters)"
              target="_blank"
              class="underline hover:text-[--app-accent]">
              {{ box_stats.customer_shipping.shipping_label.name }}
            </a>
          </template>
          <template v-else>
            {{ ctrans("No shipping label attached, we will supply one. To use your own, attach it as Shipping label in the Attachments tab.") }}
          </template>
        </div>
      </div>
    </BoxStatPallet>

    <!-- Box: Notes -->
    <BoxStatPallet :color="{ bgColor: layout.app.theme[0], textColor: layout.app.theme[1] }" class="pb-2 pt-2 px-3"
                   :tooltip="ctrans('Notes')" :percentage="0">
      <!-- Customer reference -->
      <div class="mb-1">
        <PalletEditCustomerReference
          :dataPalletDelivery="data_pallet"
          :updateRoute="address_management.updateRoute"
          :disabled="data_pallet?.state !== 'in_process' && data_pallet?.state !== 'submitted'"
        />
      </div>
      <div class="grid gap-y-3 mb-3">
        <RetinaBoxNote
          :noteData="notes_data.return"
          :updateRoute="address_management.updateRoute"
        />
      </div>
      <div class="border-t border-gray-300 pt-1">
        <dl class="flex items-center w-full flex-none gap-x-2" :class="box_stats.delivery_state.class">
          <dt class="flex-none">
            <span class="sr-only">{{ box_stats.delivery_state.tooltip }}</span>
            <FontAwesomeIcon
              :icon="box_stats.delivery_state.icon"
              size="xs"
              fixed-width aria-hidden="true" />
          </dt>
          <dd class="">{{ box_stats?.delivery_state?.tooltip }}</dd>
        </dl>
      </div>
    </BoxStatPallet>

    <!-- Box: Order summary -->
    <BoxStatPallet class="sm:col-span-2 border-t sm:border-t-0 border-gray-300">
      <section aria-labelledby="summary-heading" class="rounded-lg px-4 py-4 sm:px-6 lg:mt-0">
        <h2 id="summary-heading" class="text-lg font-medium">Order summary</h2>
        <OrderSummary :order_summary="box_stats.order_summary" :currency_code="box_stats?.order_summary?.currency?.data?.code" />
      </section>
    </BoxStatPallet>
  </div>

  <Modal :isOpen="isModalAddress" @onClose="() => (isModalAddress = false)">
    <CustomerAddressManagementModal
      :addresses="box_stats.fulfilment_customer.address"
      :updateRoute="address_management.updateRoute"
    />
  </Modal>

  <Modal :isOpen="isDeliveryAddressManagementModal" @onClose="() => (isDeliveryAddressManagementModal = false)">
    <DeliveryAddressManagementModal
      :address_modal_title="address_management.address_modal_title"
      :addresses="address_management.addresses"
      :updateRoute="address_management.address_update_route"
      @onDone="() => (isDeliveryAddressManagementModal = false)"
    />
  </Modal>
  </div>
</template>

<style scoped>
:deep(.return-toggle) {
  --p-toggleswitch-checked-background: var(--app-accent);
  --p-toggleswitch-checked-hover-background: var(--app-accent-strong);
  --p-toggleswitch-focus-ring-color: var(--app-accent);
}

:deep(.return-radio) {
  --p-radiobutton-checked-background: var(--app-accent);
  --p-radiobutton-checked-border-color: var(--app-accent);
  --p-radiobutton-checked-hover-background: var(--app-accent-strong);
  --p-radiobutton-checked-hover-border-color: var(--app-accent-strong);
  --p-radiobutton-icon-checked-color: var(--app-accent-text);
  --p-radiobutton-focus-ring-color: var(--app-accent);
}
</style>
