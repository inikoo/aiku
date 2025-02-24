import React, {} from 'react';

import Home from '@/src/screens/Home';
import Organisation from '@/src/screens/Organisation';
import Warehouse from '@/src/screens/Warehouse'
import Fulfilment from '@/src/screens/Fulfilment';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {
    faHome,
    faUsers,
    faWarehouseAlt,
} from '@/private/fa/pro-regular-svg-icons';
import {faHandHoldingBox} from '@/private/fa/pro-regular-svg-icons';

export default FirstMenu = [
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
            name: 'Organisation/Agents',
            component: Organisation,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon icon={faUsers} size={22} color={color} />
                ),
            },
        },
        {
            name: 'Fulfilment',
            component: Fulfilment,
            options: {
                drawerIcon: ({color}) => (
                    <FontAwesomeIcon
                        icon={faHandHoldingBox}
                        size={22}
                        color={color}
                    />
                ),
            },
        },
        {
          name: 'Warehouse',
          component: Warehouse,
          options: {
              drawerIcon: ({color}) => (
                  <FontAwesomeIcon
                      icon={faWarehouseAlt}
                      size={22}
                      color={color}
                  />
              ),
          },
      },
    ];