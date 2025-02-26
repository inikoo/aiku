import React, {} from 'react';

import Home from '@/src/screens/Home';
import Organisation from '@/src/screens/Organisation';
import Warehouse from '@/src/screens/Warehouse'
import Fulfilment from '@/src/screens/Fulfilment';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {
    faHome,
    faArrowToBottom,
    faArrowFromLeft,
    faBoxUsd
} from '@/private/fa/pro-regular-svg-icons';
import {faHandHoldingBox} from '@/private/fa/pro-regular-svg-icons';

const agentMenu = (fulfilment,warehouse) => {
    return [
        {
            name: 'Home',
            component: Home,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon icon={faHome} size={22} color={color} />
                ),
            },
        },
        {
            name: 'Stock Control',
            component: Fulfilment,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faBoxUsd}
                        size={22}
                        color={color}
                    />
                ),
            },
        },
        {
            name: 'Goods In',
            component: Fulfilment,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faArrowToBottom}
                        size={22}
                        color={color}
                    />
                ),
            },
        },
        {
            name: 'Goods Out',
            component: Fulfilment,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faArrowFromLeft}
                        size={22}
                        color={color}
                    />
                ),
            },
        },
    ]
}

export default agentMenu
      