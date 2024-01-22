/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 12:46:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
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
    current_page?: {
        label: string,
        url: string
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
        // liveUsersWithoutMe: (state) => state.liveUsers.filter((liveUser, keyUser) => keyUser != layout.user.id )
    },
    actions: {
        unsubscribe() {
            window.Echo.leave(`grp.live.users`)
        },
        subscribe() {

            window.Echo.join(`grp.live.users`)
                // .here((rawUsers) => {
                //     console.log('Who is here: ', rawUsers);

                // })

                .joining((user) => {
                    console.log('Someone join')
                    window.Echo.join(`grp.live.users`).whisper(`sendTo${user.id}`, this.liveUsers[usePage().props.auth.user.id])
                })

                .leaving((user: {id: number, alias: string, name: string}) => {
                    console.log('Someone leaved: ', user);
                    delete this.liveUsers[user.id]
                })

                .error((error) => {
                    console.log('error', error)
                })
                
                .listenForWhisper('otherIsNavigating', (e: LiveUsers) => {
                    // On the first load and on navigating page 
                    // console.log('qwer', this.liveUsers)
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
