<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import PageHeading from '@/Components/Headings/PageHeading.vue'
import Button from '@/Components/Elements/Buttons/Button.vue'
import EmployeeAvatar from '@/Components/HumanResources/EmployeeAvatar.vue'
import EmailBody from '@/Components/Chat/EmailBody.vue'
import { capitalize } from '@/Composables/capitalize'
import { useFormatTime, useRangeFromNow } from '@/Composables/useFormatTime'
import { ctrans } from '@/Composables/useTrans'
import { PageHeadingTypes } from '@/types/PageHeading'
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faEnvelope, faArrowLeft, faSearch, faFileAlt, faDownload, faUsers } from '@fal'

library.add(faEnvelope, faArrowLeft, faSearch, faFileAlt, faDownload, faUsers)

interface BulkEmailAttachment {
    index: number
    name: string
    size: number | null
    available: boolean
}

interface BulkEmailRecipient {
    id: number
    name: string
    avatar: string | null
    job_title: string | null
    is_working: boolean
}

const props = defineProps<{
    title: string
    pageHead: PageHeadingTypes
    email: {
        id: number
        subject: string
        body: string
        number_recipients: number
        attachments: BulkEmailAttachment[]
        sender: { name: string; avatar: Record<string, string> | null } | null
        created_at: string
    }
    recipients: BulkEmailRecipient[]
}>()

const search = ref('')

const visibleRecipients = computed(() => {
    const term = search.value.trim().toLowerCase()

    if (!term) {
        return props.recipients
    }

    return props.recipients.filter((recipient) => `${recipient.name} ${recipient.job_title ?? ''}`.toLowerCase().includes(term))
})

const recipientsLabel = computed(() =>
    props.email.number_recipients === 1
        ? ctrans(':count employee', { count: props.email.number_recipients })
        : ctrans(':count employees', { count: props.email.number_recipients })
)

const formatFileSize = (bytes: number) =>
    bytes < 1024 * 1024 ? `${Math.ceil(bytes / 1024)} KB` : `${(bytes / 1024 / 1024).toFixed(1)} MB`

const downloadUrl = (attachment: BulkEmailAttachment) =>
    route('grp.org.hr.bulk_emails.attachments.download', {
        organisation: (route().params as Record<string, any>).organisation,
        employeeBulkEmail: props.email.id,
        attachment: attachment.index,
    })

const chipClass = 'inline-flex max-w-full items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-700'
</script>

<template layout="Grp">
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #button-back="{ action }">
            <Link :href="route(action.route.name, action.route.parameters)">
                <Button type="tertiary" :label="action.label">
                    <template #icon>
                        <FontAwesomeIcon icon="fal fa-arrow-left" class="text-[--app-accent]" fixed-width aria-hidden="true" />
                    </template>
                </Button>
            </Link>
        </template>
    </PageHeading>

    <div class="grid grid-cols-1 gap-6 px-4 py-6 lg:grid-cols-3">
        <div class="min-w-0 space-y-5 lg:col-span-2">
            <dl class="flex flex-wrap items-center gap-x-8 gap-y-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm">
                <div class="flex min-w-0 items-center gap-3">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ ctrans('From') }}</dt>
                    <dd class="flex min-w-0 items-center gap-2 text-gray-900">
                        <EmployeeAvatar :name="email.sender?.name ?? null" :image="email.sender?.avatar ?? null" size="xs" class="!h-7 !w-7" />
                        <span class="truncate font-medium">{{ email.sender?.name ?? ctrans('Sender no longer available') }}</span>
                    </dd>
                </div>
                <div class="flex items-center gap-3">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ ctrans('Sent') }}</dt>
                    <dd class="text-gray-900">
                        <span class="tabular-nums">{{ useFormatTime(email.created_at, { formatTime: 'hm' }) }}</span>
                        <span class="ml-1 text-gray-500">({{ ctrans(':time ago', { time: useRangeFromNow(email.created_at) }) }})</span>
                    </dd>
                </div>
            </dl>

            <div class="rounded-lg border border-gray-200 bg-white px-5 py-6 shadow-sm sm:px-8">
                <div class="mx-auto max-w-3xl">
                    <EmailBody :html="email.body" />
                </div>
            </div>

            <div v-if="email.attachments.length">
                <h2 class="text-sm font-semibold text-gray-900">
                    {{ ctrans('Attachments') }} <span class="font-normal tabular-nums text-gray-500">({{ email.attachments.length }})</span>
                </h2>
                <ul class="mt-2 flex flex-wrap gap-2">
                    <li v-for="attachment in email.attachments" :key="attachment.index" class="max-w-full">
                        <a
                            v-if="attachment.available"
                            :href="downloadUrl(attachment)"
                            :download="attachment.name"
                            :class="chipClass"
                            class="hover:border-[--app-accent-muted] hover:bg-[--app-accent-soft] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[--app-accent]"
                        >
                            <FontAwesomeIcon icon="fal fa-file-alt" class="shrink-0 text-gray-500" fixed-width aria-hidden="true" />
                            <span class="truncate font-medium text-gray-900">{{ attachment.name }}</span>
                            <span v-if="attachment.size" class="shrink-0 tabular-nums text-gray-500">{{ formatFileSize(attachment.size) }}</span>
                            <FontAwesomeIcon icon="fal fa-download" class="shrink-0 text-[--app-accent]" fixed-width aria-hidden="true" />
                            <span class="sr-only">{{ ctrans('Download') }}</span>
                        </a>
                        <span v-else :class="chipClass" class="bg-gray-50">
                            <FontAwesomeIcon icon="fal fa-file-alt" class="shrink-0 text-gray-400" fixed-width aria-hidden="true" />
                            <span class="truncate text-gray-500">{{ attachment.name }}</span>
                            <span v-if="attachment.size" class="shrink-0 tabular-nums text-gray-400">{{ formatFileSize(attachment.size) }}</span>
                            <span class="shrink-0 text-gray-400">· {{ ctrans('File no longer available') }}</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <aside class="self-start rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-semibold text-gray-900">{{ ctrans('Recipients') }}</h2>
                <span class="inline-flex items-center gap-1.5 text-xs tabular-nums text-gray-500">
                    <FontAwesomeIcon icon="fal fa-users" fixed-width aria-hidden="true" />
                    {{ recipientsLabel }}
                </span>
            </div>

            <template v-if="recipients.length">
                <IconField class="mt-3">
                    <InputIcon>
                        <FontAwesomeIcon icon="fal fa-search" class="text-gray-400" fixed-width aria-hidden="true" />
                    </InputIcon>
                    <InputText
                        v-model="search"
                        fluid
                        size="small"
                        :placeholder="ctrans('Search by name or job title')"
                        :aria-label="ctrans('Search recipients')"
                        class="focus:!border-[--app-accent]"
                    />
                </IconField>

                <ul class="mt-3 max-h-[32rem] divide-y divide-gray-100 overflow-y-auto rounded-lg border border-gray-200">
                    <li v-for="recipient in visibleRecipients" :key="recipient.id" class="flex items-center gap-3 px-3 py-2">
                        <EmployeeAvatar :name="recipient.name" :image="recipient.avatar" size="sm" class="!h-9 !w-9" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm text-gray-900">{{ recipient.name }}</span>
                            <span v-if="recipient.job_title" class="block truncate text-xs text-gray-500">{{ recipient.job_title }}</span>
                        </span>
                        <span v-if="!recipient.is_working" class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500">
                            {{ ctrans('no longer working') }}
                        </span>
                    </li>
                    <li v-if="!visibleRecipients.length" class="px-3 py-6 text-center text-xs text-gray-500">
                        {{ ctrans('No recipients match your search') }}
                    </li>
                </ul>
            </template>
            <p v-else class="mt-3 rounded-lg border border-dashed border-gray-300 px-3 py-4 text-center text-xs text-gray-500">
                {{ email.number_recipients ? ctrans('The recipient list was not recorded for this email.') : ctrans('This email had no recipients.') }}
            </p>
        </aside>
    </div>
</template>
