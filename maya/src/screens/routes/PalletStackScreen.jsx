import React from 'react';
import {SafeAreaView} from 'react-native';
import BottomTabs from '@/src/components/BottomTabs';

import PalletShowcase from '@/src/screens/Pallet/ShowPallet'
import ItemsInPallet from '@/src/screens/Pallet/ItemsInPallet';


import {faMapSigns, faInventory} from '@/private/fa/pro-regular-svg-icons';


const PalletStackScreen = ({navigation, route}) => {
    const TabArr = [
        {
            route: 'show-pallet',
            label: 'Pallet',
            icon: faMapSigns,
            component: props => (
                <PalletShowcase
                    {...props}
                    navigation={navigation}
                    route={route}
                />
            ),
        },
        {
            route: 'items-in-pallet',
            label: 'Items In Pallet',
            icon: faInventory,
            component: props => (
                <ItemsInPallet
                    {...props}
                    navigation={navigation}
                    route={route}
                />
            ),
        },
    ];

    return (
        <SafeAreaView style={{flex: 1}}>
            <BottomTabs tabArr={TabArr} />
        </SafeAreaView>
    );
};

export default PalletStackScreen;
