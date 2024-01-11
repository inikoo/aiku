/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Dec 2023 12:46:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

import axios from 'axios'
import { defineStore } from 'pinia'


export const useLiveUsers = defineStore('useLiveUsers', {
    state: () => ({
        liveUsers: {
            1: {
                name: '',
                current_page: {
                    label: ''
                }
            }
        },
    }),
    getters: {
        count: (state) => Object.keys(state.liveUsers).length,
    },
    actions: {
        unsubscribe() {
            window.Echo.leave(`grp.live.users`)
        },
        subscribe() {
            window.Echo.join(`grp.live.users`)
                .here((rawUsers) => {
                    // console.log('Who is here: ', rawUsers);

                    axios.get(route('grp.models.live-group-users-current-page.index'))
                        .then((response) => {
                            // console.log('lll', response.data)
                            this.liveUsers = response.data

                            Object.keys(this.liveUsers).forEach((userKey) => {
                                // Retrieve alias from Echo.here data
                                this.liveUsers[userKey].name = (rawUsers.find((rawUser) => rawUser.id === userKey))?.name
                            })
                        })

                }).joining((user) => {
                    // console.log('Someone is join: ', user);
                    this.liveUsers[user.id] = user

                }).leaving((user) => {
                    // console.log('Someone leaved: ', user);
                    delete this.liveUsers[user.id]

                }).error((error) => {
                    console.log('error', error)

                }).listen('.changePage', (data) => {
                    // Listen from another user who change the page
                    // console.log('Another user ' + data.user_alias + '  is change the page ' + data.active_page);
                    this.liveUsers[data.user_id].active_page = data.active_page

                })

        },
    },
})
