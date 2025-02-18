
import React, {} from 'react';
import { SafeAreaView } from 'react-native';
import BottomTabs from '@/src/components/BottomTabs'

import DeliveryNotes from '@/src/screens/DeliveryNote/DeliveryNotes';
import FulfilmentReturns from '@/src/screens/Return/FulfilmentReturns'

import { faTruck , faSignOut  } from '@/private/fa/pro-regular-svg-icons';

const TabArr = [
  {route: 'delivery-notes', label: 'Notes', icon: faTruck, component: DeliveryNotes},
  {route: 'fulfilment-returns', label: 'Returns', icon: faSignOut, component: FulfilmentReturns},
];

export default function GoodsOutStackScreen() {
  return (
    <SafeAreaView style={{flex: 1}}>
      <BottomTabs tabArr={TabArr} />
    </SafeAreaView>
  );
}