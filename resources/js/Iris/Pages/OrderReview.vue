<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref } from "vue"
import { Head } from "@inertiajs/vue3"
import axios from "axios"
import Rating from "primevue/rating"
import Textarea from "primevue/textarea"
import Button from "@/Components/Elements/Buttons/Button.vue"
import Image from "@/Common/Components/Image.vue"
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import { library } from "@fortawesome/fontawesome-svg-core"
import { faCheck, faStar } from "@fal"
import { ctrans } from "@/Composables/useTrans"

library.add(faCheck, faStar)

type ReviewItem = {
    scope: "order" | "product"
    reviewable_id: number
    name: string
    code?: string
    image?: object | null
    logo?: object | null
    rating: number | null
    message: string | null
}

type VisitorOrderReview = {
    id: number
    token: string
    reference: string
    date: string | null
    company: ReviewItem
    products: ReviewItem[]
}

const props = defineProps<{
    title: string
    order: VisitorOrderReview
    preselect: { product: number | null; rating: number | null }
}>()

const itemKey = (item: ReviewItem) => `${item.scope}-${item.reviewable_id}`

const toForm = (order: VisitorOrderReview) => [order.company, ...order.products].map((item) => ({
    ...item,
    rating: item.rating,
    message: item.message ?? "",
}))

const items = reactive(toForm(props.order))
const preselectedKey = props.preselect.product ? `product-${props.preselect.product}` : "order-" + props.order.id

if (props.preselect.rating) {
    const preselected = items.find((item) => itemKey(item) === preselectedKey)
    if (preselected) {
        preselected.rating = props.preselect.rating
    }
}

const isSending = ref(false)
const isSent = ref(false)
const errorMessage = ref("")

const ratedItems = computed(() => items.filter((item) => item.rating))

onMounted(async () => {
    if (!props.preselect.rating) {
        return
    }
    await nextTick()
    document.getElementById(preselectedKey)?.scrollIntoView({ block: "center" })
})

const send = async () => {
    if (!ratedItems.value.length) {
        errorMessage.value = ctrans("Choose a star rating first.")
        return
    }
    isSending.value = true
    errorMessage.value = ""
    try {
        const { data } = await axios.post(route("iris.models.order_review.store", { order: props.order.id, token: props.order.token }), {
            reviews: ratedItems.value.map((item) => ({
                scope: item.scope,
                reviewable_id: item.reviewable_id,
                rating: item.rating,
                message: item.message || null,
            })),
        })
        items.splice(0, items.length, ...toForm(data))
        isSent.value = true
        window.scrollTo({ top: 0, behavior: "smooth" })
    } catch (error: any) {
        errorMessage.value = error?.response?.status === 429
            ? ctrans("Too many attempts. Please wait a minute and try again.")
            : ctrans("Something went wrong. Please try again.")
    }
    isSending.value = false
}
</script>

<template>
    <div>
        <Head :title="title" />
        <div class="w-full px-4 py-10 text-slate-700">
            <div class="mx-auto max-w-[640px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_8px_rgba(0,0,0,0.08)]">
                <header class="px-6 py-7">
                    <div class="text-sm font-semibold text-slate-500">{{ order.company.name }}</div>
                    <h1 class="mt-1 text-[1.5rem] font-bold leading-tight text-slate-800">
                        {{ ctrans("How was your order :reference?", { reference: order.reference }) }}
                    </h1>
                    <div v-if="order.date" class="mt-1 text-sm text-slate-500">{{ order.date }}</div>

                    <div v-if="isSent" class="mt-4 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800" role="status">
                        <span class="font-semibold">{{ ctrans("Thank you!") }}</span>
                        {{ ctrans("Your review has been sent. You can change it here at any time.") }}
                    </div>
                    <div v-else-if="preselect.rating" class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800" role="status">
                        {{ ctrans("Your :rating star rating is ready. Add a few words if you like, then press Send.", { rating: preselect.rating }) }}
                    </div>
                </header>

                <section
                    v-for="item in items"
                    :id="itemKey(item)"
                    :key="itemKey(item)"
                    class="border-t border-slate-200 px-6 py-5">
                    <div class="flex items-center gap-4">
                        <div class="review-thumb">
                            <Image v-if="item.image || item.logo" :src="item.image ?? item.logo" :alt="item.name" :imageCover="true" class="h-full w-full" />
                            <FontAwesomeIcon v-else icon="fal fa-star" class="text-xl text-slate-400" fixed-width aria-hidden="true" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-slate-800">
                                {{ item.scope === "order" ? ctrans("How would you rate :shop?", { shop: item.name }) : item.name }}
                            </div>
                            <div v-if="item.code" class="whitespace-nowrap text-xs text-slate-500">{{ item.code }}</div>
                            <Rating v-model="item.rating" :cancel="false" class="review-stars mt-2" />
                        </div>
                    </div>
                    <Textarea
                        v-if="item.rating"
                        v-model="item.message"
                        rows="2"
                        autoResize
                        maxlength="5000"
                        class="mt-3 w-full"
                        :placeholder="ctrans('Tell us more (optional)')" />
                </section>

                <footer class="flex flex-wrap items-center gap-3 border-t border-slate-200 px-6 py-5">
                    <Button type="tertiary" :label="ctrans('Send')" :loading="isSending" @click="send">
                        <template #icon>
                            <FontAwesomeIcon icon="fal fa-check" class="text-green-600" fixed-width aria-hidden="true" />
                        </template>
                    </Button>
                    <span class="text-sm text-slate-500">
                        {{ ctrans(":count of :total rated", { count: ratedItems.length, total: items.length }) }}
                    </span>
                    <p v-if="errorMessage" class="w-full text-sm text-red-600">{{ errorMessage }}</p>
                </footer>
            </div>
        </div>
    </div>
</template>

<style scoped>
.review-thumb {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    overflow: hidden;
    border-radius: 6px;
    background: #f8fafc;
}

.review-stars :deep(.p-rating-icon) {
    width: 1.5rem;
    height: 1.5rem;
}

.review-stars :deep(.p-rating-option-active .p-rating-icon) {
    color: #f5b301;
}
</style>
