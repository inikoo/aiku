<!--
  - Author: stewicca <stewicalf@gmail.com>
  - Copyright (c) 2026, Steven Wicca Alfredo
  -->

<script setup lang="ts">
import { Head } from "@inertiajs/vue3"
import PageHeading from "@/Components/Headings/PageHeading.vue"
import SeoExportButton from "@/Components/Seo/SeoExportButton.vue"
import TableSiteAuditIssuePages from "@/Components/Tables/Grp/Org/Web/TableSiteAuditIssuePages.vue"
import { capitalize } from "@/Composables/capitalize"
import { PageHeadingTypes } from "@/types/PageHeading"

defineProps<{
    title: string
    pageHead: PageHeadingTypes
    issueType: string
    websiteSlug: string
    severity: string
    description: string
    data: object
}>()
</script>

<template>
    <Head :title="capitalize(title)" />
    <PageHeading :data="pageHead">
        <template #other>
            <SeoExportButton table="site_audit_issue_pages" :extra="{ issue_type: issueType }" />
        </template>
    </PageHeading>

    <p class="mx-4 mb-4 mt-4 max-w-3xl text-sm text-gray-600">{{ description }}</p>

    <TableSiteAuditIssuePages :data="data" :issueType="issueType" :websiteSlug="websiteSlug" />
</template>
