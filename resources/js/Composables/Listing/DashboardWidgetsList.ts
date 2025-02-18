/**
 * Author: Vika Aqordi
 * Created on: 09-01-2025-14h-05m
 * Github: https://github.com/aqordeon
 * Copyright: 2025
*/

import FlatTreeMap from '@/Components/DataDisplay/Dashboard/Widget/FlatTreeMap.vue'
import type { Component } from 'vue'
import { defineAsyncComponent } from 'vue'

const BasicDisplay = defineAsyncComponent(() => import('@/Components/DataDisplay/Dashboard/Widget/BasicDisplay.vue'))
const OverviewDisplay = defineAsyncComponent(() => import('@/Components/DataDisplay/Dashboard/Widget/OverviewDisplay.vue'))
const OperationDisplay = defineAsyncComponent(() => import('@/Components/DataDisplay/Dashboard/Widget/OperationDisplay.vue'))
const ChartDisplay = defineAsyncComponent(() => import('@/Components/DataDisplay/Dashboard/Widget/ChartDisplay.vue'))
const CircleDisplay = defineAsyncComponent(() => import('@/Components/DataDisplay/Dashboard/Widget/CircleDisplay.vue'))

export const widgetList: {[key: string]: Component} = {
    'basic': BasicDisplay,
    'flat_tree_map': FlatTreeMap,
    'overview_display': OverviewDisplay,
    'operation_display': OperationDisplay,
    'chart_display' : ChartDisplay,
    'circle_display': CircleDisplay
}

export const getComponentWidget = (componentName: string) => {
    return widgetList[componentName]
}