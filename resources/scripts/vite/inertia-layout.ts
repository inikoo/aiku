/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 10 Aug 2022 23:49:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */
import { Plugin } from 'vite'

const PLUGIN_NAME = 'vite:inertia:layout'
const TEMPLATE_LAYOUT_REGEX = /<template +layout(?: *= *['"](?:(?:(\w+):)?(\w+))['"] *)?>/

/**
 * A basic Vite plugin that adds a <template layout="name"> syntax to Vite SFCs.
 * It must be used before the Vue plugin.
 */
export default (layouts: string = '@/Layouts/'): Plugin => ({
    name: PLUGIN_NAME,
    transform: (code: string) => {
        if (!TEMPLATE_LAYOUT_REGEX.test(code)) {
            return
        }

        const isTypeScript = /lang=['"]ts['"]/.test(code)

        return code.replace(TEMPLATE_LAYOUT_REGEX, (_, __, layoutName) => `
			<script${isTypeScript ? ' lang="ts"' : ''}>
			import layout from '${layouts}${layoutName ?? 'default'}.vue'
			export default { layout }
			</script>
			<template>
		`)
    },
})

