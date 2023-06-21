<script setup>
import Pagination from '@/Components/Table/Pagination.vue';
import HeaderCell from '@/Components/Table/HeaderCell.vue';
import TableGlobalSearch from '@/Components/Table/TableGlobalSearch.vue';
import TableElements from '@/Components/Table/TableElements.vue';
import TableWrapper from '@/Components/Table/TableWrapper.vue';
import TableAddSearchRow from '@/Components/Table/TableAddSearchRow.vue';
import TableColumns from '@/Components/Table/TableColumns.vue';
import TableFilter from '@/Components/Table/TableFilter.vue';
import TableSearchRows from '@/Components/Table/TableSearchRows.vue';
import SearchReset from '@/Components/Table/SearchReset.vue';
import Button from '@/Components/Elements/Buttons/Button.vue';
import EmptyState from '@/Components/Common/EmptyState.vue'
import TableDownload from '@/Components/Table/TableDownload.vue'
import { Link } from "@inertiajs/vue3"

import { computed, onMounted, ref, watch, onUnmounted, getCurrentInstance, Transition } from 'vue';
import qs from 'qs';
import clone from 'lodash-es/clone';
import filter from 'lodash-es/filter';
import findKey from 'lodash-es/findKey';
import forEach from 'lodash-es/forEach';
import isEqual from 'lodash-es/isEqual';
import map from 'lodash-es/map';

import {useLocaleStore} from '@/Stores/locale.js';
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
        // elements: {
        //     type: Array,
        //     default: () => {
        //         return [];
        //     },
        //     required: false,
        // },
        modelOperations: {
            type: Array,
            default: () => {
                return [];
            },
            required: false,
        },
        prefix:{
            type: String,
            default: () => {
                return '';
            },
            required: false,
        }
    });

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
const resourceData = computed(() => {
    if (Object.keys(props.resource).length === 0) {
        return props.data;
    }

    if ('data' in props.resource) {
        return props.resource.data;
    }

    return props.resource;
});

// Meta Page (Previous/next link, current page, data per page)
const resourceMeta = computed(() => {
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
    if (resourceData.value.length > 0) {
        return true;
    }

    return resourceMeta.value.total > 0;
});

//

function disableSearchInput(key) {
    forcedVisibleSearchInputs.value = forcedVisibleSearchInputs.value.filter(
        (search) => search !== key,
    );

    changeSearchInputValue(key, null);
}

function showSearchInput(key) {
    forcedVisibleSearchInputs.value.push(key);
}

const canBeReset = computed(() => {
    if (forcedVisibleSearchInputs.value.length > 0) {
        return true;
    }

    const queryStringData = qs.parse(location.search.substring(1));

    const page = queryStringData[pageName.value];

    if (page > 1) {
        return true;
    }

    const prefix = props.name === 'default' ? '' : props.name + '_';
    let dirty = false;

    forEach(['filter', 'columns', 'cursor', 'sort', 'elementGroups'], (key) => {
        const value = queryStringData[prefix + key];

        if (key === 'sort' && value === queryBuilderProps.value.defaultSort) {
            return;
        }

        if (value !== undefined) {
            dirty = true;
        }
    });

    return dirty;
});

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

    forEach(queryBuilderData.value.columns, (column, key) => {
        queryBuilderData.value.columns[key].hidden = column.can_be_hidden
            ? !queryBuilderProps.value.defaultVisibleToggleableColumns.includes(column.key)
            : false;
    });

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

function changeFilterValue(key, value) {
    const intKey = findDataKey('filters', key);

    queryBuilderData.value.filters[intKey].value = value;
    queryBuilderData.value.cursor = null;
    queryBuilderData.value.page = 1;
}

function onPerPageChange(value) {
    queryBuilderData.value.cursor = null;
    queryBuilderData.value.perPage = value;
    queryBuilderData.value.page = 1;
}

function findDataKey(dataKey, key) {
    return findKey(queryBuilderData.value[dataKey], (value) => {
        return value.key === key;
    });
}

function changeColumnStatus(key, visible) {
    const intKey = findDataKey('columns', key);

    queryBuilderData.value.columns[intKey].hidden = !visible;
}

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
        queryData.elements = elementFilter // the begining of add new filter
    }
    return queryData;
}

function generateNewQueryString() {
    const queryStringData = qs.parse(location.search.substring(1));

    const prefix = props.name === 'default' ? '' : props.name + '_';

    forEach(['filter', 'columns', 'cursor', 'sort'], (key) => {
        delete queryStringData[prefix + key];
    });

    delete queryStringData[pageName.value];

    forEach(dataForNewQueryString(), (value, key) => {
        if (key === 'page') {
            queryStringData[pageName.value] = value;
        } else if (key === 'perPage') {
            queryStringData.perPage = value;
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
        encodeValuesOnly: true ,
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

function visit(url) {
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

                    window.scrollTo({ top });
                }

                updates.value++;
            },
        },
    );
}

watch(
    queryBuilderData,
    () => {
        visit(location.pathname + '?' + generateNewQueryString());
    },
    { deep: true },
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

//

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
    // visit(location.pathname + '?elements[state]=' + data)
    //queryBuilderData.value.elements[0].checked=true

}
</script>

<template>
    <Transition>
        <EmptyState v-if="resourceMeta.total == 0" />
        <fieldset v-else ref="tableFieldset" :key="`table-${name}`" :dusk="`table-${name}`" class="min-w-0" :class="{ 'opacity-75': isVisiting }">
            <div class="my-2">
            <!-- Wrapper -->

            <slot @changed="handleElementsChange">
                <TableElements class="mb-2" v-if="queryBuilderProps.elementGroups?.length" :elements="queryBuilderProps.elementGroups" @changed="handleElementsChange" />
            </slot>
            <div class="grid grid-flow-col justify-between flex-nowrap px-4">
                
                <!-- Left Section: Records, -->
                <div class="flex space-x-2">
                    <!-- Result Number -->
                    <div class="flex border border-indigo-100 rounded-md">
                        <div class="grid justify-end items-center text-base font-normal text-gray-700"
                            title="Results">
                            <div v-if="resourceMeta.total" class="px-2 ">{{ locale.number(resourceMeta.total) }} {{ $t(resourceMeta.total > 1 ? 'records' : 'record') }}</div>
                            <div v-else class="px-2 ">{{ locale.number(0) }} {{ $t('record') }}</div>
                        </div>
                        <!-- Button -->
                        <!-- <div v-if="queryBuilderProps.modelOperations.createLink">
                            <Link :href="route(queryBuilderProps.modelOperations.createLink.route.name, queryBuilderProps.modelOperations.createLink.route.parameters[0])">
                                <Button type='secondary' action="create" class="bg-indigo-100/60 hover:bg-indigo-100 capitalize focus:ring-offset-0 focus:ring-transparent rounded-l-none border-indigo-500">
                                    {{queryBuilderProps.modelOperations.createLink.label}}
                                </Button>
                            </Link>
                        </div> -->
                    </div>
                </div>

              <!-- <pre>{{queryBuilderProps.modelOperations}}</pre> -->

                <!-- Search Group -->
                <div class="flex flex-row justify-end items-start flex-nowrap space-x-2">
                    <div class="order-2 sm:order-1 mr-2 sm:mr-4" v-if="queryBuilderProps.hasFilters">
                        <slot name="tableFilter" :has-filters="queryBuilderProps.hasFilters"
                            :has-enabled-filters="queryBuilderProps.hasEnabledFilters" :filters="queryBuilderProps.filters"
                            :on-filter-change="changeFilterValue">
                            <TableFilter :has-enabled-filters="queryBuilderProps.hasEnabledFilters"
                                :filters="queryBuilderProps.filters" :on-filter-change="changeFilterValue" />
                        </slot>
                    </div>

                    <!-- Search Input Button -->
                    <div v-if="queryBuilderProps.globalSearch"
                        class="flex flex-row w-64 order-1 md:order-2 transition-all ease-in-out duration-100">
                        <slot name="tableGlobalSearch" :has-global-search="queryBuilderProps.globalSearch"
                            :label="queryBuilderProps.globalSearch ? queryBuilderProps.globalSearch.label : null"
                            :value="queryBuilderProps.globalSearch ? queryBuilderProps.globalSearch.value : null"
                            :on-change="changeGlobalSearchValue">
                            <TableGlobalSearch v-if="queryBuilderProps.globalSearch" class="flex-grow"
                                :label="queryBuilderProps.globalSearch.label" :value="queryBuilderProps.globalSearch.value"
                                :on-change="changeGlobalSearchValue" />
                        </slot>
                    </div>
                    
                    <!-- Button: Reset -->
                    <slot name="searchReset" can-be-reset="canBeReset" @resetSearch="() => resetQuery()">
                        <div v-if="canBeReset" class="order-3">
                            <SearchReset @resetSearch="() => resetQuery()" />
                        </div>
                    </slot>
                    
                    <!-- Button: Filter -->
                    <slot name="tableAddSearchRow" :has-search-inputs="queryBuilderProps.hasSearchInputs"
                        :has-search-inputs-without-value="queryBuilderProps.hasSearchInputsWithoutValue"
                        :search-inputs="queryBuilderProps.searchInputsWithoutGlobal" :on-add="showSearchInput">
                        <TableAddSearchRow v-if="queryBuilderProps.hasSearchInputs" class="order-4"
                            :search-inputs="queryBuilderProps.searchInputsWithoutGlobal" :has-search-inputs-without-value="queryBuilderProps.hasSearchInputsWithoutValue
                                " :on-add="showSearchInput" />
                    </slot>

                    

                    <!-- Button: Switch toggle filter the column of table -->
                    <slot name="tableColumns" :has-columns="queryBuilderProps.hasToggleableColumns"
                        :columns="queryBuilderProps.columns" :has-hidden-columns="queryBuilderProps.hasHiddenColumns"
                        :on-change="changeColumnStatus">
                        <TableColumns v-if="queryBuilderProps.hasToggleableColumns" class="order-4 mr-4 sm:mr-0 sm:order-5"
                            :columns="queryBuilderProps.columns" :has-hidden-columns="queryBuilderProps.hasHiddenColumns"
                            :on-change="changeColumnStatus" />
                    </slot>
                </div>
            </div>

            <slot name="tableSearchRows" :has-search-rows-with-value="queryBuilderProps.hasSearchInputsWithValue"
                :search-inputs="queryBuilderProps.searchInputsWithoutGlobal"
                :forced-visible-search-inputs="forcedVisibleSearchInputs" :on-change="changeSearchInputValue">
                <TableSearchRows v-if="queryBuilderProps.hasSearchInputsWithValue ||
                    forcedVisibleSearchInputs.length > 0
                    " :search-inputs="queryBuilderProps.searchInputsWithoutGlobal"
                    :forced-visible-search-inputs="forcedVisibleSearchInputs" :on-change="changeSearchInputValue"
                    :on-remove="disableSearchInput" />
            </slot>

            </div>

            <!-- The Main Table -->
            <slot name="tableWrapper" :meta="resourceMeta">
                <TableWrapper :class="{ 'mt-0': !hasOnlyData }">
                    <slot name="table">
                        <table class="divide-y divide-gray-200 bg-white w-full">
                            <thead class="bg-gray-50">
                                <tr class="border-t border-gray-200">
                                    <HeaderCell v-for="column in queryBuilderProps.columns"
                                        :key="`table-${name}-header-${column.key}`" :cell="header(column.key)"
                                        :type="columnsType[column.key]" />
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">
                                <slot name="body" :show="show">
                                    <tr v-for="(item, key) in resourceData" :key="`table-${name}-row-${key}`" class=""
                                        :class="{
                                            'bg-gray-50': striped && key % 2,
                                            'hover:bg-gray-100': striped,
                                            'hover:bg-gray-50': !striped,
                                        }">
                                        <td v-for="column in queryBuilderProps.columns" v-show="show(column.key)"
                                            :key="`table-${name}-row-${key}-column-${column.key}`" :class="[
                                                typeof item[column.key] == 'number' ? 'text-right' : '',
                                                column.key == 'avatar' ? 'flex justify-center items-center' : '',
                                                'text-sm py-4 px-6 text-gray-500 whitespace-normal min-w-fit max-w-[450px]',

                                            ]">
                                            <slot :name="`cell(${column.key})`" :item="item">
                                                <img v-if="column.key == 'avatar'" :src="`/media/group/${item[column.key]}`" class="w-5"/>
                                                <div v-else>{{ item[column.key] }}</div>
                                            </slot>
                                        </td>
                                    </tr>
                                </slot>
                            </tbody>
                        </table>
                    </slot>

                    <!-- Pagination -->
                    <slot name="pagination" :on-click="visit" :has-data="hasData" :meta="resourceMeta"
                        v-if="resourceMeta.total > 15" :per-page-options="queryBuilderProps.perPageOptions"
                        :on-per-page-change="onPerPageChange">
                        <Pagination :on-click="visit" :has-data="hasData" :meta="resourceMeta"
                            :per-page-options="queryBuilderProps.perPageOptions" :on-per-page-change="onPerPageChange" />
                    </slot>
                </TableWrapper>
            </slot>
        </fieldset>
    </Transition>
</template>

<style scope>
fieldset {
    margin-top: 0px !important;
}
</style>
