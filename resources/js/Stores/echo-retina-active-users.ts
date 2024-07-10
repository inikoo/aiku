/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Feb 2024 11:12:41 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

import { Image } from '@/types/Image'
import { usePage } from '@inertiajs/vue3'
import { defineStore } from 'pinia'
// import { useLayoutStore } from './layout'

// const layout = useLayoutStore()

interface LiveUser {
    id: number
    username: string  // aiku
    avatar_thumbnail: Image
    name: string  // Mr. Aiku
    action: string  // navigate, leave, logout
    last_active: Date
    current_page?: {
        label: string,
        url: string
        icon_left: {
            icon: string | string[]
            class: string
        }
        icon_right: {
            icon: string | string[]
            class: string
        }
    }
}
interface LiveUsers {
    [key: number]: LiveUser
}


export const useLiveUsers = defineStore('useLiveUsers', {
    state: () => ({
        liveUsers: { } as LiveUsers,
    }),
    getters: {
        count: (state) => Object.keys(state.liveUsers).length,
    },
    actions: {

        unsubscribe() {
            window.Echo.leave(`retina.active.users`)
        },
        subscribe() {

            window.Echo.join(`retina.active.users`)
                // .here((rawUsers) => {
                //     console.log('Who is here: ', rawUsers);

                // })

                .joining((user: LiveUser) => {
                    console.log('Someone join')
                    // if UserA join, then others send their data to UserA
                    window.Echo.join(`retina.active.users`).whisper(`sendTo${user.id}`, this.liveUsers[usePage().props.auth.user.id])
                })

                .leaving((user: {id: number, alias: string, name: string}) => {
                    console.log('Someone leaved: ', user)

                    // If user 'logout', no need to set the action to 'leave'
                    if (this.liveUsers[user.id].action != 'logout') {
                        this.liveUsers[user.id].action = 'leave'
                        this.liveUsers[user.id].last_active = new Date()
                    }
                })

                .error((error: {}) => {
                    console.log('error', error)
                })

                .listenForWhisper('otherIsNavigating', (e: LiveUser) => {
                    // On the first load and on navigating page
                    // console.log('qwer', e)
                    this.liveUsers[e.id] = e
                    // console.log('qwer', this.liveUsers)
                })

                .listenForWhisper(`sendTo${usePage().props.auth.user.id}`, (otherUser: LiveUser) => {
                    // console.log('receive the emit')
                    // On the first load and on navigating page
                    this.liveUsers[otherUser.id] = otherUser
                    // console.log('qwer', this.liveUsers)
                })
        },
    },
})
