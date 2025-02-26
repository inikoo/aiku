
import React, {} from 'react';
import { SafeAreaView } from 'react-native';
import BottomTabs from '@/src/components/BottomTabs'

import StockDeliveries from '@/src/screens/Fulfilment/Stock/StockDeliveries';
import FulfilmentDeliveries from '@/src/screens/Fulfilment/Delivery/FulfilmentDeliveries'

import { faTruck , faTruckCouch } from '@/private/fa/pro-regular-svg-icons';

const TabArr = [
  {route: 'stock-deliveries', label: 'Stock', icon: faTruck, component: StockDeliveries},
  {route: 'fulfilment-deliveries', label: 'Deliveries', icon: faTruckCouch, component: FulfilmentDeliveries},
];

export default function GoodsInStackScreen() {
  return (
    <SafeAreaView style={{flex: 1}}>
      <BottomTabs tabArr={TabArr} />
    </SafeAreaView>
  );
}