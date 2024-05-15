<!--suppress JSUnresolvedReference, JSIncompatibleTypesComparison -->
<script setup lang="ts">
import Pagination from '@/Components/Table/Pagination.vue';
import HeaderCell from '@/Components/Table/HeaderCell.vue';
import TableFilterSearch from '@/Components/Table/TableFilterSearch.vue';
import TableElements from '@/Components/Table/TableElements.vue';
import TableWrapper from '@/Components/Table/TableWrapper.vue';
// import TableFilterColumn from '@/Components/Table/TableFilterColumn.vue';
// import TableColumns from '@/Components/Table/TableColumns.vue';
// import TableAdvancedFilter from '@/Components/Table/TableAdvancedFilter.vue';
// import TableSearchRows from '@/Components/Table/TableSearchRows.vue';
// import SearchReset from '@/Components/Table/SearchReset.vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import EmptyState from '@/Components/Utils/EmptyState.vue'
import {Link, useForm} from "@inertiajs/vue3"
import {trans} from 'laravel-vue-i18n'

import {computed, getCurrentInstance, onMounted, onUnmounted, ref, toRefs, Transition, watch, onBeforeMount, reactive } from 'vue';
import qs from 'qs';
import clone from 'lodash-es/clone';
import filter from 'lodash-es/filter';
import findKey from 'lodash-es/findKey';
import forEach from 'lodash-es/forEach';
import isEqual from 'lodash-es/isEqual';
import map from 'lodash-es/map';
import { kebabCase  } from 'lodash'
// import { library } from "@fortawesome/fontawesome-svg-core";
import {useLocaleStore} from '@/Stores/locale';
import CountUp from 'vue-countup-v3';
// import { cloneDeep } from 'lodash';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faCheckSquare, faCheck } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
library.add(faCheckSquare, faCheck)

const locale = useLocaleStore();

const props = defineProps(
    {
        changeElements: {
            type: Object,
        },
        inertia: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },

        name: {
            type: String,
            default: 'default',
            required: false,
        },

        striped: {
            type: Boolean,
            default: false,
            required: false,
        },

        preventOverlappingRequests: {
            type: Boolean,
            default: true,
            required: false,
        },

        inputDebounceMs: {
            type: Number,
            default: 350,
            required: false,
        },

        preserveScroll: {
            type: [Boolean, String],
            default: false,
            required: false,
        },

        // The main source of data
        resource: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },
        meta: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },

        data: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },

        columnsType: {
            type: Object,
            default: () => {
                return {};
            },
            required: false,
        },
        modelOperations: {
            type: Array,
            default: () => {
                return [];
            },
            required: false,
        },
        emptyState: {
            type: Array,
            default: () => {
                return [];
            },
            required: false,
        },
        selectedRow: {
            type: Object
        },
        exportLinks: {
            type: Object
        },
        isCheckBox: {
            type: Boolean
        },
        useForm : {
            type: Boolean,
            default: false,
            required: false,
        }
    });

const emits = defineEmits<{
    (e: 'onSelectRow', value: {[key: string]: boolean}): void
}>()

const app = getCurrentInstance();
const $inertia = app ? app.appContext.config.globalProperties.$inertia : props.inertia;
const updates = ref(0);

const queryBuilderProps = computed(() => {
    let data = $inertia.page.props.queryBuilderProps
        ? $inertia.page.props.queryBuilderProps[props.name] || {}
        : {};

    data._updates = updates.value;
    return data;
});



const queryBuilderData = ref(queryBuilderProps.value);
queryBuilderData.value.elementFilter = {
    // 'state': ['left'],
    // 'type': ['volunteer', 'employee']
}


const pageName = computed(() => {
    return queryBuilderProps.value.pageName;
});

const forcedVisibleSearchInputs = ref([]);

const tableFieldset = ref(null);

const hasOnlyData = computed(() => {
    if (queryBuilderProps.value.hasToggleableColumns) {
        return false;
    }

    if (queryBuilderProps.value.hasFilters) {
        return false;
    }

    if (queryBuilderProps.value.hasSearchInputs) {
        return false;
    }

    if (queryBuilderProps.value.elements) {
        return false;
    }

    return !queryBuilderProps.value.globalSearch;
});

// Data of list users
const compResourceData = computed(() => {
    if (Object.keys(props.resource).length === 0) {
        return props.data;
    }

    if ('data' in props.resource) {
        return props.resource.data;
    }
    return props.resource;
});

// Meta Page (Previous/next link, current page, data per page)
const compResourceMeta = computed(() => {
    if (Object.keys(props.resource).length === 0) {
        return props.meta;
    }

    if ('links' in props.resource && 'meta' in props.resource) {
        if (
            Object.keys(props.resource.links).length === 4 &&
            'next' in props.resource.links &&
            'prev' in props.resource.links
        ) {
            return {
                ...props.resource.meta,
                next_page_url: props.resource.links.next,
                prev_page_url: props.resource.links.prev,
            };
        }
    }

    if ('meta' in props.resource) {
        return props.resource.meta;
    }

    return props.resource;
});

const hasData = computed(() => {
    if (compResourceData.value.length > 0) {
        return true;
    }

    return compResourceMeta.value.total > 0;
});

//

// function disableSearchInput(key) {
//     forcedVisibleSearchInputs.value = forcedVisibleSearchInputs.value.filter(
//         (search) => search !== key,
//     );

//     changeSearchInputValue(key, null);
// }

// function showSearchInput(key) {
//     forcedVisibleSearchInputs.value.push(key);
// }

// const canBeReset = computed(() => {
//     if (forcedVisibleSearchInputs.value.length > 0) {
//         return true;
//     }

//     const queryStringData = qs.parse(location.search.substring(1));

//     const page = queryStringData[pageName.value];

//     if (page > 1) {
//         return true;
//     }

//     const prefix = props.name === 'default' ? '' : props.name + '_';
//     let dirty = false;

//     forEach(['filter', 'columns', 'cursor', 'sort', 'elementGroups'], (key) => {
//         const value = queryStringData[prefix + key];

//         if (key === 'sort' && value === queryBuilderProps.value.defaultSort) {
//             return;
//         }

//         if (value !== undefined) {
//             dirty = true;
//         }
//     });

//     return dirty;
// });

function resetQuery() {
    forcedVisibleSearchInputs.value = [];

    forEach(queryBuilderData.value.filters, (filter, key) => {
        queryBuilderData.value.filters[key].value = null;
    });

    forEach(queryBuilderData.value.searchInputs, (filter, key) => {
        queryBuilderData.value.searchInputs[key].value = null;
    });

    forEach(queryBuilderData.value.elements, (filter, key) => {
        queryBuilderData.value.elements[key].value = null;
    });

    // forEach(queryBuilderData.value.columns, (column, key) => {
    //     queryBuilderData.value.columns[key].hidden = column.can_be_hidden
    //         ? !queryBuilderProps.value.defaultVisibleToggleableColumns.includes(column.key)
    //         : false;
    // });

    queryBuilderData.value.sort = null;
    queryBuilderData.value.cursor = null;
    queryBuilderData.value.page = 1;
}

const debounceTimeouts = {};

function changeSearchInputValue(key, value) {
    clearTimeout(debounceTimeouts[key]);

    debounceTimeouts[key] = setTimeout(() => {
        if (visitCancelToken.value && props.preventOverlappingRequests) {
            visitCancelToken.value.cancel();
        }

        const intKey = findDataKey('searchInputs', key);
        queryBuilderData.value.searchInputs[intKey].value = value;
        queryBuilderData.value.cursor = null;
        queryBuilderData.value.page = 1;
    }, props.inputDebounceMs);
}

function changeGlobalSearchValue(value) {
    changeSearchInputValue('global', value);
}

// function changeFilterValue(key, value) {
//     const intKey = findDataKey('filters', key);

//     queryBuilderData.value.filters[intKey].value = value;
//     queryBuilderData.value.cursor = null;
//     queryBuilderData.value.page = 1;
// }

function onPerPageChange(value) {
    queryBuilderData.value.cursor = null
    queryBuilderData.value.perPage = value
    queryBuilderData.value.page = 1
}

function findDataKey(dataKey, key) {
    return findKey(queryBuilderData.value[dataKey], (value) => {
        return value.key === key;
    });
}

// function changeColumnStatus(key, visible) {
//     const intKey = findDataKey('columns', key);

//     queryBuilderData.value.columns[intKey].hidden = !visible;
// }

function getFilterForQuery() {
    let filtersWithValue = {};

    forEach(queryBuilderData.value.searchInputs, (searchInput) => {
        if (searchInput.value !== null) {
            filtersWithValue[searchInput.key] = searchInput.value;
        }
    });

    forEach(queryBuilderData.value.filters, (filters) => {
        if (filters.value !== null) {
            filtersWithValue[filters.key] = filters.value;
        }
    });

    return filtersWithValue;
}

function getColumnsForQuery() {
    const columns = queryBuilderData.value.columns;

    let visibleColumns = filter(columns, (column) => {
        return !column.hidden;
    });

    let visibleColumnKeys = map(visibleColumns, (column) => {
        return column.key;
    }).sort();

    if (isEqual(visibleColumnKeys, queryBuilderProps.value.defaultVisibleToggleableColumns)) {
        return {};
    }

    return visibleColumnKeys;
}

function dataForNewQueryString() {
    const filterForQuery = getFilterForQuery();
    const columnsForQuery = getColumnsForQuery();
    const queryData = {};

    if (Object.keys(filterForQuery).length > 0) {
        queryData.filter = filterForQuery;
    }

    if (Object.keys(columnsForQuery).length > 0) {
        queryData.columns = columnsForQuery;
    }

    const cursor = queryBuilderData.value.cursor;
    const page = queryBuilderData.value.page;
    const sort = queryBuilderData.value.sort;
    const perPage = queryBuilderData.value.perPage;
    const elementFilter = queryBuilderData.value.elementFilter

    if (cursor) {
        queryData.cursor = cursor;
    }

    if (page > 1) {
        queryData.page = page;
    }

    if (perPage > 1) {
        queryData.perPage = perPage;
    }

    if (sort) {
        queryData.sort = sort;
    }
    if (elementFilter) {
        queryData.elements = elementFilter // the beginning of add new filter
    }
    return queryData;
}

function generateNewQueryString() {
    // Get data from URL
    const queryStringData = qs.parse(location.search.substring(1))
    const prefix = props.name === 'default' ? '' : props.name + '_'

    // To exclude filter, columns, cursor, and sort that received from the URL
    forEach(['filter', 'columns', 'cursor', 'sort'], (key) => {
        delete queryStringData[prefix + key];
    });

    // To exclude page number from pagination
    delete queryStringData[pageName.value];

    forEach(dataForNewQueryString(), (value, key) => {
        if (key === 'page') {
            queryStringData[pageName.value] = value;
            // } else if (key === 'perPage') {
            // This line make pagination error
            //     queryStringData.perPage[prefix + key] = value;
        } else {
            queryStringData[prefix + key] = value;
        }
    });
    let query = qs.stringify(queryStringData, {
        // filter(prefix, value) {
        //         if (typeof value === 'object' && value !== null) {
        //         return pickBy(value);
        //     }

        //     return value;
        // },
        encodeValuesOnly: true,
        skipNulls: true,
        strictNullHandling: true,
        arrayFormat: 'comma', // for elementGroups
    });

    if (!query || query === pageName.value + '=1') {
        query = '';
    }

    return query;
}

const isVisiting = ref(false);
const visitCancelToken = ref(null);

const visit = (url) => {
    // Visit new generate URL, run on watch queryBuilderData

    if (!url) {
        return;
    }

    $inertia.get(
        url,
        {},
        {
            replace: true,
            preserveState: true,
            preserveScroll: props.preserveScroll !== false,
            onBefore() {
                isVisiting.value = true;
            },
            onCancelToken(cancelToken) {
                visitCancelToken.value = cancelToken;
            },
            onFinish() {
                isVisiting.value = false;
            },
            onSuccess() {
                if ('queryBuilderProps' in $inertia.page.props) {
                    queryBuilderData.value.cursor = queryBuilderProps.value.cursor;
                    queryBuilderData.value.page = queryBuilderProps.value.page;
                }

                if (props.preserveScroll === 'table-top') {
                    const offset = -8;
                    const top =
                        tableFieldset.value.getBoundingClientRect().top +
                        window.pageYOffset +
                        offset;

                    window.scrollTo({top});
                }

                updates.value++;
            },
        },
    );
}

watch(queryBuilderData, async () => {
        try {
            visit(location.pathname + '?' + generateNewQueryString())
        } catch {
            console.error("Can't visit expected path")
        }
    },
    {deep: true},
);

const inertiaListener = () => {
    updates.value++;
};

onMounted(() => {
    document.addEventListener('inertia:success', inertiaListener);
});

onUnmounted(() => {
    document.removeEventListener('inertia:success', inertiaListener);
});

/* onBeforeMount(() => {
    if ('data' in props.resource) {
        if (props.useForm) props.resource.data.forEach((item) => item.form = useForm({...item}));
    }
}); */


function sortBy(column) {
    if (queryBuilderData.value.sort === column) {
        queryBuilderData.value.sort = `-${column}`;
    } else {
        queryBuilderData.value.sort = column;
    }

    queryBuilderData.value.cursor = null;
    queryBuilderData.value.page = 1;
}

function show(key) {
    const intKey = findDataKey('columns', key);

    return !queryBuilderData.value.columns[intKey].hidden;
}

function header(key) {
    const intKey = findDataKey('columns', key);
    const columnData = clone(queryBuilderProps.value.columns[intKey]);

    columnData.onSort = sortBy;

    return columnData;
}

const handleElementsChange = (data) => {
    queryBuilderData.value.elementFilter = data
}

// const {name} = toRefs(props)

watch(() => props.name, () => {
    // To reset the 'sort' on change Tabs
    queryBuilderData.value.sort = null
    resetQuery()
})

const selectRow: {[key: string]: boolean} = reactive({})

// To preserve the object selectRow
if (props.isCheckBox) {
    for(const row in props.resource.data){
        selectRow[props.resource.data[row].id] = false
    }
}

// On select select all
const onClickSelectAll = (state: boolean) => {
    for(const row in props.resource.data){
        selectRow[props.resource.data[row].id] = !state
    }
}

watch(selectRow, () => {
    emits('onSelectRow', selectRow)
}, {deep: true})

</script>

<template>
    <Transition>
        <!--suppress JSValidateTypes -->
        <slot name='emptyState' :emptyState="queryBuilderProps.emptyState"
            v-if="queryBuilderProps.emptyState?.count === 0 && compResourceMeta.total === 0">
            <EmptyState :data="queryBuilderProps.emptyState">
                <template #button-empty-state>
                    <div>
                       <!--  <pre>{{ queryBuilderProps.emptyState }}</pre> -->
                        <Link v-if="queryBuilderProps.emptyState?.action" as="div"
                            :href="route(queryBuilderProps.emptyState?.action.route.name, queryBuilderProps.emptyState?.action.route.parameters)"
                            :method="queryBuilderProps.emptyState?.action?.route?.method" class="mt-4 block">
                        <Button :style="queryBuilderProps.emptyState?.action.style"
                            :icon="queryBuilderProps.emptyState?.action.icon"
                            :label="queryBuilderProps.emptyState?.action.tooltip" />
                        </Link>
                    </div>
                </template>
            </EmptyState>

        </slot>

        <!--suppress HtmlUnknownAttribute -->
        <fieldset v-else ref="tableFieldset" :key="`table-${name}`" :dusk="`table-${name}`" class="min-w-0"
            :class="{ 'opacity-75': isVisiting }">
            <div class="my-0">
                <!-- Wrapper -->
                <div class="grid grid-flow-col justify-between flex-nowrap pr-4">

                    <!-- Left Section: Records, -->
                    <div class="flex space-x-2">
                        <!-- Result Number -->
                        <div class="flex gap-0 items-center">
                            <div class="grid border-r rounded-md border-gray-300 justify-end items-center text-base font-normal text-gray-700"
                                title="Results">
                                <div v-if="compResourceMeta.total"
                                    class="px-2 py-1.5 whitespace-nowrap flex gap-x-1 flex-nowrap">
                                    <span class="font-semibold tabular-nums">
                                        <CountUp :endVal="compResourceMeta.total" :duration="1.2" :scrollSpyOnce="true"
                                            :options="{
                                            formattingFn: (number) => locale.number(number)
                                        }" />
                                    </span>
                                    <!-- {{ locale.number(compResourceMeta.total) }} -->
                                    <span class="font-light">{{
                                        compResourceMeta.total > 1 ? trans('records') : trans('record')
                                        }}</span>
                                </div>
                                <div v-else class="px-2 py-1.5">{{ locale.number(0) }} {{ trans('record') }}</div>
                            </div>

                            <!-- Button: Model Operations -->
                            <div v-if="queryBuilderProps.modelOperations?.createLink" class="flex">
                                <slot v-for="(linkButton, btnIndex) in queryBuilderProps.modelOperations?.createLink"
                                    :name="`button-${kebabCase(linkButton.label)}`"
                                    :linkButton="{...linkButton, btnIndex: btnIndex }">
                                    <component v-if="linkButton?.route?.name" :is="linkButton.target ? 'a' : Link"
                                        as="div"
                                        :target="linkButton.target || undefined"
                                        :href="route(linkButton?.route?.name, linkButton?.route?.parameters)"
                                        :method="linkButton.route?.method || 'get'"
                                        v-tooltip="linkButton.tooltip"
                                        :class="[queryBuilderProps.modelOperations?.createLink.length > 1 ? 'first:rounded-l last:rounded-r' : '']">
                                        <Button :style="linkButton.style" :icon="linkButton.icon" :label="linkButton.label"
                                            size="l" class="h-full border-none rounded-none"
                                            :class="{'rounded-l-md': btnIndex === 0, 'rounded-r-md ': btnIndex === queryBuilderProps.modelOperations?.createLink.length - 1}" />
                                    </component>
                                </slot>
                            </div>
                            <div v-if="queryBuilderProps.modelOperations?.bulk" class="flex">

                                <slot v-for="(linkButton, btnIndex) in queryBuilderProps.modelOperations?.bulk"
                                    :name="`button${linkButton.label}`" :linkButton="linkButton">
                                    <Link v-if="linkButton?.route?.name" as="div"
                                        :href="route(linkButton?.route?.name, linkButton?.route?.parameters)"
                                        :method="linkButton.route?.method || 'get'" v-tooltip="linkButton.tooltip"
                                        :data="selectRow"
                                        :class="[queryBuilderProps.modelOperations?.bulk.length > 1 ? 'first:rounded-l last:rounded-r' : '']">
                                    <Button
                                        :style="Object.values(selectRow).some(value => value) ? linkButton.style : 'disabled'"
                                        :icon="linkButton.icon" :label="linkButton.label" size="l"
                                        class="h-full border-none rounded-none"
                                        :class="{'rounded-l-md': btnIndex === 0, 'rounded-r-md ': btnIndex === queryBuilderProps.modelOperations?.bulk.length - 1}" />
                                    </Link>
                                </slot>
                            </div>

                            <slot v-if="queryBuilderProps.modelOperations?.uploadFile" name="uploadFile" id="uploadFile"
                                :item="queryBuilderProps.modelOperations?.uploadFile" />

                            <!-- Search Input Button -->
                            <div v-if="queryBuilderProps.globalSearch" class="flex flex-row">
                                <slot name="tableFilterSearch" :has-global-search="queryBuilderProps.globalSearch"
                                    :label="queryBuilderProps.globalSearch ? queryBuilderProps.globalSearch.label : null"
                                    :value="queryBuilderProps.globalSearch ? queryBuilderProps.globalSearch.value : null"
                                    :on-change="changeGlobalSearchValue">
                                    <TableFilterSearch v-if="queryBuilderProps.globalSearch" class=""
                                        @resetSearch="() => resetQuery()" :label="queryBuilderProps.globalSearch.label"
                                        :value="queryBuilderProps.globalSearch.value"
                                        :on-change="changeGlobalSearchValue" />
                                </slot>
                            </div>
                        </div>
                    </div>

                    <!-- Search Group -->
                    <div class="flex flex-row justify-end items-center flex-nowrap space-x-2">
                        <!-- <div class="order-2 sm:order-1 mr-2 sm:mr-4" v-if="queryBuilderProps.hasFilters">
                            <slot name="tableAdvancedFilter" :has-filters="queryBuilderProps.hasFilters"
                                :has-enabled-filters="queryBuilderProps.hasEnabledFilters" :filters="queryBuilderProps.filters"
                                :on-filter-change="changeFilterValue">
                                <TableAdvancedFilter :has-enabled-filters="queryBuilderProps.hasEnabledFilters"
                                    :filters="queryBuilderProps.filters" :on-filter-change="changeFilterValue" />
                            </slot>
                        </div> -->

                        <!-- Element Filter -->
                        <div class="w-fit">
                            <TableElements v-if="queryBuilderProps?.elementGroups?.length"
                                :elements="queryBuilderProps.elementGroups" @checkboxChanged="handleElementsChange"
                                :title="queryBuilderData.title" :name="props.name" />
                        </div>

                        <!-- Button: Reset -->
                        <!--suppress HtmlUnknownAttribute -->
                        <!-- <slot name="searchReset" can-be-reset="canBeReset" @resetSearch="() => resetQuery()">
                            <div v-if="canBeReset" class="order-3">
                                <SearchReset @resetSearch="() => resetQuery()" />
                            </div>
                        </slot> -->

                        <!-- Button: Filter table -->
                        <!-- <slot name="tableFilterColumn" :has-search-inputs="queryBuilderProps.hasSearchInputs"
                            :has-search-inputs-without-value="queryBuilderProps.hasSearchInputsWithoutValue"
                            :search-inputs="queryBuilderProps.searchInputsWithoutGlobal" :on-add="showSearchInput">
                            <TableFilterColumn v-if="queryBuilderProps.hasSearchInputs" class="order-4"
                                :search-inputs="queryBuilderProps.searchInputsWithoutGlobal" :has-search-inputs-without-value="queryBuilderProps.hasSearchInputsWithoutValue
                                    " :on-add="showSearchInput" />
                        </slot> -->


                        <!-- Button: Switch toggle search the column of table -->
                        <!-- <slot name="tableColumns" :has-columns="queryBuilderProps.hasToggleableColumns"
                            :columns="queryBuilderProps.columns" :has-hidden-columns="queryBuilderProps.hasHiddenColumns"
                            :on-change="changeColumnStatus">
                            <TableColumns v-if="queryBuilderProps.hasToggleableColumns" class="order-4 mr-4 sm:mr-0 sm:order-5"
                                :columns="queryBuilderProps.columns" :has-hidden-columns="queryBuilderProps.hasHiddenColumns"
                                :on-change="changeColumnStatus" />
                        </slot> -->
                    </div>
                </div>

                <!-- Field: search by column of table-->
                <!-- <slot name="tableSearchRows" :has-search-rows-with-value="queryBuilderProps.hasSearchInputsWithValue"
                    :search-inputs="queryBuilderProps.searchInputsWithoutGlobal"
                    :forced-visible-search-inputs="forcedVisibleSearchInputs" :on-change="changeSearchInputValue">
                    <TableSearchRows v-if="queryBuilderProps.hasSearchInputsWithValue ||
                        forcedVisibleSearchInputs.length > 0
                        " :search-inputs="queryBuilderProps.searchInputsWithoutGlobal"
                        :forced-visible-search-inputs="forcedVisibleSearchInputs" :on-change="changeSearchInputValue"
                        :on-remove="disableSearchInput" />
                </slot> -->

            </div>

            <!-- The Main Table -->
            <slot name="tableWrapper" :meta="compResourceMeta">
                <TableWrapper :result="compResourceMeta.total === 0" :class="{ 'mt-0': !hasOnlyData }">
                    <slot name="table">
                        <table class="divide-y divide-gray-200 bg-white w-full">
                            <thead class="bg-gray-50">
                                <tr class="border-t border-gray-200 divide-x divide-gray-200">
                                    <div v-if="isCheckBox"
                                        @click="() => onClickSelectAll(Object.values(selectRow).every((value) => value === true))"
                                        class="py-1.5 cursor-pointer">
                                        <FontAwesomeIcon
                                            v-if="Object.values(selectRow).every((value) => value === true)"
                                            icon='fal fa-check-square' class='mx-auto block h-5 my-auto' fixed-width
                                            aria-hidden='true' />
                                        <FontAwesomeIcon v-else icon='fal fa-square' class='mx-auto block h-5 my-auto'
                                            fixed-width aria-hidden='true' />
                                    </div>

                                    <HeaderCell v-for="column in queryBuilderProps.columns"
                                        :key="`table-${name}-header-${column.key}`" :cell="header(column.key)"
                                        :type="columnsType[column.key]" :column="column" :resource="compResourceData">
                                    </HeaderCell>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                <slot name="body" :show="show">
                                    <tr v-for="(item, key) in compResourceData" :key="`table-${name}-row-${key}`"
                                        class="" :class="[{
                                                'bg-gray-50': striped && key % 2,
                                            },
                                                striped ? 'hover:bg-gray-100' : 'hover:bg-gray-50'
                                            ]">
                                        <!-- Column: Check box -->
                                        <td v-if="isCheckBox" key="checkbox" class="h-full flex justify-center">
                                            <div v-if="selectRow[item.id]"
                                                class="absolute inset-0 bg-lime-500/10 -z-10" />
                                            <FontAwesomeIcon v-if="selectRow[item.id] === true"
                                                @click="selectRow[item.id] = !selectRow[item.id]"
                                                icon='fal fa-check-square' class='p-2 cursor-pointer' fixed-width
                                                aria-hidden='true' />
                                            <FontAwesomeIcon v-else @click="selectRow[item.id] = !selectRow[item.id]"
                                                icon='fal fa-square' class='p-2 cursor-pointer' fixed-width
                                                aria-hidden='true' />
                                        </td>

                                        <td v-for="(column,index) in queryBuilderProps.columns"
                                            v-show="show(column.key)"
                                            :key="`table-${name}-row-${key}-column-${column.key}`"
                                            class="text-sm py-2 text-gray-500 whitespace-normal h-full" :class="[
                                                    column.type === 'avatar' || column.type === 'icon'
                                                        ? 'text-center min-w-fit'  // if type = icon
                                                        : typeof item[column.key] == 'number'
                                                            ? 'text-right pr-11 tabular-nums'  // if the value is number
                                                            : 'px-6',
                                                    { 'first:border-l-4 first:border-gray-700 bg-gray-200/75': selectedRow?.[name]?.includes(item.id) },
                                                    column.className
                                            ]">
                                            <slot :name="`cell(${column.key})`"
                                                :item="{ ...item, index : index, editingIndicator: { loading : false , isSucces : false, isFailed : false } }"
                                                :tabName="name" class="">
                                                {{ item[column.key] }}
                                            </slot>
                                        </td>
                                    </tr>
                                </slot>
                            </tbody>
                        </table>
                    </slot>

                    <!-- Pagination -->
                    <slot name="pagination" :on-click="visit" :has-data="hasData" :meta="compResourceMeta"
                        :per-page-options="queryBuilderProps.perPageOptions" :on-per-page-change="onPerPageChange">
                        <Pagination :on-click="visit" :has-data="hasData" :meta="compResourceMeta"
                            :exportLinks="queryBuilderProps.exportLinks"
                            :per-page-options="queryBuilderProps.perPageOptions"
                            :on-per-page-change="onPerPageChange" />
                    </slot>
                </TableWrapper>
            </slot>
        </fieldset>
    </Transition>
</template>

<!--suppress HtmlUnknownAttribute -->
<style scope>
fieldset {
    margin-top: 0 !important;
}
</style>
