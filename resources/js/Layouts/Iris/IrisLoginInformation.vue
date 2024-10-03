<script setup lang='ts'>
import { trans } from 'laravel-vue-i18n'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { faHeart, faShoppingCart, faSignOut, faUser, faSignIn, faUserPlus } from '@fal'
import { library } from '@fortawesome/fontawesome-svg-core'
import { ref } from 'vue'
import { inject } from 'vue'
import { aikuLocaleStructure } from '@/Composables/useLocaleStructure'
library.add(faHeart, faShoppingCart, faSignOut, faUser, faSignIn, faUserPlus)

const isLoggedIn = ref(false)
const isDropshipping = ref(false)

const locale = inject('locale', aikuLocaleStructure)

</script>

<template>
    <div id="top_bar" class="bg-[rgb(75,80,88)] text-white py-1 px-4 flex justify-between">
        <div class="flex">
            <div>Hello,</div>

            <div class="font-semibold max-w-[180px] text-ellipsis inline-block whitespace-nowrap ml-[5px] overflow-hidden">Katka Buchy</div>
            <div id="top_bar_is_gold_reward_member" class="hide" style="margin-left: 20px;">
                <i class="fal fa-sparkles" style="color: #ffebb1;"></i>
                <div id="top_bar_is_gold_reward_member_label" style="padding: 1px 2px  1px 3px;color: #ffbf00;font-weight: 600;"></div>
                <i class="fal fa-sparkles" style="color: #ffebb1;"></i>
                <div id="top_bar_is_gold_reward_member_until" style="white-space: nowrap;display: inline-block;font-size: 0.7rem;margin-left: 2px;"></div>
            </div>

            <div id="top_bar_is_first_order_bonus" class="hide" style="margin-left: 20px;">
                <i class="fal fa-sparkles" style="color: #ffebb1;"></i>
                <div id="top_bar_is_first_order_bonus_label" style="padding: 1px 2px  1px 3px;color: #ffbf00;font-weight: 600;"></div>
                <i class="fal fa-sparkles" style="color: #ffebb1;"></i>
            </div>
        </div>

        <div class="action_buttons" style="display: flex; justify-content: flex-end; column-gap: 45px; grid-column: span 5 / span 5">
            <template v-if="isLoggedIn">
                <a href="#" class="space-x-1.5" style="margin-left: 0px;">
                    <!-- <i class="far fa-flip-horizontal fa-sign-out" title="Log out" aria-hidden="true"></i> -->
                    <FontAwesomeIcon icon='fal fa-sign-out' v-tooltip="trans('Log out')" class='' fixed-width aria-hidden='true' />
                    <span>Log out</span>
                </a>
                <a id="profile_button" href="profile.sys" class="space-x-1.5" >
                    <!-- <i class="far fa-user fa-flip-horizontal  " title="Profile" aria-hidden="true"></i> -->
                    <FontAwesomeIcon icon='fal fa-user' class='' v-tooltip="trans('Profile')" fixed-width aria-hidden='true' />
                    <span>Profile</span>
                </a>
                <a id="favorites_button" href="favourites.sys" class="mx-0 space-x-1.5">
                    <!-- <i class=" far fa-heart" title="My favourites" aria-hidden="true"></i> -->
                    <FontAwesomeIcon icon='fal fa-heart' class='' fixed-width aria-hidden='true' />
                    <span>My favourites</span>
                </a>
                <a id="header_order_totals" href="basket.sys" class="space-x-1.5" style="">
                    <span class="ordered_products_number">11</span>
                    <FontAwesomeIcon icon='fal fa-shopping-cart' class='text-base px-[5px]' v-tooltip="trans('Basket')" fixed-width aria-hidden='true' />
                    <span class="order_amount" title="" style="font-weight: 600; font-size: 1.1rem;">
                        {{  }}
                    </span>
                </a>
            </template>

            <template v-else>
                <template v-if="isDropshipping">
                    <a href="/login.sys" class="space-x-1.5" id="">
                        <span>Call us</span>
                    </a>
                </template>

                <template v-else>
                    <a href="/login.sys" class="space-x-1.5" id="">
                        <FontAwesomeIcon icon='fal fa-sign-in' class='' fixed-width aria-hidden='true' />
                        <span>Login</span>
                    </a>
                    <a href="/register.sys" class="space-x-1.5">
                        <FontAwesomeIcon icon='fal fa-user-plus' class='' fixed-width aria-hidden='true' />
                        <span>Register</span>
                    </a>
                </template>
            </template>

        </div>
    </div>
</template>