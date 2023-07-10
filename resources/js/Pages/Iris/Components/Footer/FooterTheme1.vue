<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome"
import {
	faFacebook,
	faInstagram,
	faTwitter,
	faGithub,
	faYoutube,
} from "@fortawesome/free-brands-svg-icons"
import {
	faMapMarkerAlt,
	faEnvelope,
	faBalanceScale,
	faBuilding,
	faPhone,
	faMap,
} from "@fortawesome/free-solid-svg-icons"
import { library } from "@fortawesome/fontawesome-svg-core"
import draggable from "vuedraggable"
import { ref } from "vue"
import Input from "../Fields/Input.vue"
import TextArea from "../Fields/TextArea.vue"
import HyperLink from "../Fields/Hyperlink.vue"
import IconPicker from "../Fields/IconPicker/IconPicker.vue"
import SocialMediaPicker from "../Fields/SocialMediaTools.vue"
library.add(
	faFacebook,
	faInstagram,
	faTwitter,
	faGithub,
	faYoutube,
	faMapMarkerAlt,
	faEnvelope,
	faBalanceScale,
	faBuilding,
	faPhone,
	faMap
)

const props = defineProps<{
	social: Object
	navigation: Object
	selectedColums: Function
	columSelected: Object
	saveItemTitle: Function
	saveTextArea: Function
	tool: Object
	saveLink: Function
	saveInfo: Function
	copyRight: Object
	copyRightSave: Function
	saveSocialmedia: Function
}>()

const navigations = [
    {
        title: 'solutions',
        data: [
            { name: 'Marketing', href: '#' },
            { name: 'Analytics', href: '#' },
            { name: 'Commerce', href: '#' },
            { name: 'Insights', href: '#' },
        ],
    },
    {
        title: 'support',
        data: [
            { name: 'Pricing', href: '#' },
            { name: 'Documentation', href: '#' },
            { name: 'Guides', href: '#' },
            { name: 'API Status', href: '#' },
        ],
    },
    {
        title: 'company',
        data: [
            { name: 'About', href: '#' },
            { name: 'Blog', href: '#' },
            { name: 'Jobs', href: '#' },
            { name: 'Press', href: '#' },
            { name: 'Partners', href: '#' },
        ],
    },
    {
        title: 'legal',
        data: [
            { name: 'Claim', href: '#' },
            { name: 'Privacy', href: '#' },
            { name: 'Terms', href: '#' },
        ],
    },
]

const socials = [
    {
        title: "Facebook",
        icon: ['fab', 'fa-facebook'],
    },
    {
        title: "Instagram",
        icon: ['fab', 'fa-instagram'],
    },
    {
        title: "Twitter",
        icon: ['fab', 'fa-twitter'],
    },
    {
        title: "Github",
        icon: ['fab', 'fa-github'],
    },
    {
        title: "Youtube",
        icon: ['fab', 'fa-youtube'],
    },
]
</script>

<template>
    <div class="arya">
    <footer class="bg-gray-900" aria-labelledby="footer-heading">
        <h2 id="footer-heading" class="sr-only">Footer</h2>
        <div class="mx-auto max-w-7xl px-6 pb-8 pt-16 sm:pt-24 lg:px-8 lg:pt-32">
            <div class="xl:grid xl:grid-cols-3 xl:gap-20 items-center">

                <!-- Box -->
                <div class="grid justify-center space-y-5 rounded-xl bg-gray-950 border border-indigo-500 py-4 mb-8 xl:mb-0">
                    <div class=" flex justify-center">
                        <img class="h-24" src="https://tailwindui.com/img/logos/mark.svg?color=indigo&shade=500"
                            alt="Company name" />
                    </div>
                    <p class="px-3 text-center text-sm text-gray-300">
                        Making the world a better place through constructing elegant hierarchies.
                    </p>
                    <div class="flex justify-center">
                        <div class="text-gray-100 border-t border-gray-500 w-10/12" />
                    </div>

                    <div class="flex justify-center space-x-6">
                        <!-- <a v-for="(social, index) in socials" :key="index" href="#"
                            class="text-gray-500 py-0.5 px-1.5  flex items-center justify-center hover:text-gray-400 cursor-pointer">
                            <span class="sr-only">{{ social.title }}</span>
                            <FontAwesomeIcon :icon="social.icon" class="h-6 w-6" aria-hidden="true" />
                        </a> -->
                        <draggable :list="social" group="socialMedia" itemKey="id" :class="[
						tool.name === 'grab' ? 'cursor-grab' : 'cursor-pointer',
						'text-gray-400 hover:text-gray-500 flex space-x-6',
					]"  :disabled="tool.name !== 'grab'">
						<template #item="{ element: child, index: childIndex }">
							<div>
								<span class="sr-only">{{ child.label }}</span>
								<SocialMediaPicker :modelValue="child.icon" cssClass="h-6 w-6" :data="child"
									:save="saveSocialmedia" />
							</div>
						</template>
					</draggable>
                    </div>
                </div>




                <div class="col-span-2">
                    <div class="flex gap-x-10 justify-between">
                    <draggable :list="navigation" group="navigation" itemKey="id" :disabled="tool.name !== 'grab'" :class="[
					'flex',
					'gap-8',
					'xl:col-span-2',
					tool.name !== 'grab' ? 'cursor-pointer' : 'cursor-grab',
				]">
					<template #item="{ element, index }">
						<div :class="[
							'space-y-3',
							'w-1/4',
							columSelected.id !== element.id ? '' : 'border',
						]" @click="props.selectedColums(element)">
							<!-- <h3 class="text-sm font-bold leading-6 text-gray-700 capitalize">{{ element.title }}</h3> -->
							<Input :data="element" :save="props.saveItemTitle" keyValue="title"
								cssClass="font-bold text-white capitalize" />
							<div v-if="element.type == 'list'">
								<draggable :list="element.data" group="list" @change="childLog" itemKey="name"
									:disabled="tool.name !== 'grab'">
									<template #item="{ element: child, index: childIndex }">
										<ul role="list">
											<li :key="child.name">
												<HyperLink valueKeyLabel="name" valueKeyLink="href" :useDelete="true"
													:data="child"
													cssClass="text-sm text-gray-300 hover:text-white"
													:save="(value) =>
															props.saveLink({
																parentId: element.id,
																...value,
															})
														" />
											</li>
										</ul>
									</template>
								</draggable>
							</div>

							<div v-if="element.type == 'description'">
								<!-- <div class="space-y-3 text-sm leading-6 text-gray-600 hover:text-indigo-500">{{ element.data }}</div> -->
								<TextArea :data="element" :save="props.saveTextArea" cssClass="text-sm text-gray-300 hover:text-white"/>
							</div>

							<div v-if="element.type == 'info'">
								<div class="flex flex-col gap-y-5">
									<draggable :list="element.data" group="info" @change="childLog" itemKey="name"
										:disabled="tool.name !== 'grab'">
										<template #item="{ element: child, index: childIndex }">
											<div
												class="grid grid-cols-[auto,1fr] gap-4 items-center justify-start gap-y-3 mb-2.5">
												<div class="w-5 flex items-center justify-center text-gray-400">
													<!-- <FontAwesomeIcon :icon="child.icon" :title="child.title"
                                                aria-hidden="true" /> -->
													<IconPicker :modelValue="child.icon" :data="child" :save="(value) =>
															props.saveInfo({
																parentId: element.id,
																type: 'icon',
																...value,
															})
														" />
												</div>
												<Input :data="child" :save="(value) =>
														props.saveInfo({
															parentId: element.id,
															type: 'value',
															...value,
														})
													" keyValue="value" cssClass="text-sm text-gray-300 hover:text-white" />
											</div>
										</template>
									</draggable>
								</div>
							</div>
						</div>
					</template>
				</draggable>
                    </div>
                </div>
            </div>
            <div class="mt-16 border-t border-white/10 pt-8 sm:mt-20 lg:mt-24 text-center">
                <div class="text-xs  flex justify-center leading-5 text-gray-400">&copy; 2023 <HyperLink :useDelete="false" :data="copyRight" :save="copyRightSave" valueKeyLabel="label"
								valueKeyLink="href" />, Inc. All rights reserved.</div>
            </div>
        </div>
    </footer>
</div>
</template>

<style>
.arya{
    width: 100%;
}
</style>