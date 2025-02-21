import React, {useContext, useEffect, useState, useRef} from 'react';
import {
  SafeAreaView,
  View,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import BottomTabs from '@/src/components/BottomTabs';
import {DeliveryProvider, useDelivery} from '@/src/components/Context/delivery';
import Menu from '@/src/components/Menu';
import {AuthContext} from '@/src/components/Context/context';
import PalletInDeliveries from '@/src/screens/Delivery/PalletInDeliveries';
import ShowFulfilmentDelivery from '@/src/screens/Delivery/ShowFulfilmentDelivery';
import request from '@/src/utils/Request';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {faBars} from '@/private/fa/pro-regular-svg-icons';
import {faPallet, faTachometerAlt} from '@/private/fa/pro-regular-svg-icons';

const DeliveryStackScreen = ({navigation, route}) => {
  const {organisation, warehouse} = useContext(AuthContext);
  const {data, setData} = useDelivery();
  const menuRef = useRef(null);
  const {id} = route.params;

  const TabArr = [
    {
      route: 'delivery-showcase',
      label: 'Showcase',
      icon: faTachometerAlt,
      component: props => (
        <ShowFulfilmentDelivery
          {...props}
          navigation={navigation}
          route={route}
          handleRefresh={fetchData}
          onChangeState={onPressMenu}
        />
      ),
    },
    {
      route: 'pallets-in-delivery',
      label: 'Pallets',
      icon: faPallet,
      component: props => (
        <PalletInDeliveries 
        {...props} 
        navigation={navigation} 
        route={route} 
        handleRefresh={fetchData}
        onChangeState={onPressMenu}
        />
      ),
    },
  ];

 /*  const getFilteredActions = () => {
    if (data?.state === 'confirmed') {
      return [
        {id: 'received', title: 'Received'},
        {id: 'cancel', title: 'Cancel', attributes: {destructive: true}},
      ];
    }

    if (data?.state === 'received') {
      return [{id: 'booking-in', title: 'Booking in'}];
    }

    if (data?.state === 'booking_in') {
      return [{id: 'booked-in', title: 'Booked in'}];
    }

    return [];
  }; */

  const fetchData = async () => {
    try {
      const response = await request({
        urlKey: 'get-delivery',
        args: [organisation.id, warehouse.id, id],
      });
      console.log(response.data)
      setData(response.data);
    } catch (error) {
      Toast.show({
        type: ALERT_TYPE.DANGER,
        title: 'Error',
        textBody: error.detail?.message || 'Failed to fetch data',
      });
    } finally {
      setLoading(false);
    }
  };

  const onPressMenu = event => {
    switch (event) {
      case 'received':
        setStatusDelivery(event);
        break;
      case 'booking-in':
        setStatusDelivery(event);
        break;
      case 'booked-in':
        setStatusDelivery(event);
        break;
      case 'cancel':
        console.log('Option 3 selected');
        break;
      default:
        console.log('Unknown option selected');
    }
  };

  const setStatusDelivery = state => {
    request({
      urlKey: 'set-delivery-' + state,
      method: 'patch',
      args: [data.id],
      data: {state: state},
      onSuccess: response => {
        fetchData();
        Toast.show({
          type: ALERT_TYPE.SUCCESS,
          title: 'Success',
          textBody: 'success update pallet to ' + state,
        });
      },
      onFailed: error => {
        Toast.show({
          type: ALERT_TYPE.DANGER,
          title: 'Error',
          textBody: error.detail?.message || 'Failed to update data',
        });
      },
    });
  };

  useEffect(() => {
    fetchData();
  }, []);

  useEffect(() => {
    navigation.setOptions({
      title: data ? `Delivery ${data.reference}` : 'Delivery Details',
     /*  headerRight: () => (
        <View className="px-4">
          <Menu
            ref={menuRef}
            onPressAction={({nativeEvent}) => onPressMenu(nativeEvent)}
            button={
              <TouchableOpacity onPress={() => menuRef?.current?.menu.show()}>
                <FontAwesomeIcon icon={faBars} />
              </TouchableOpacity>
            }
            actions={getFilteredActions()}
          />
        </View>
      ), */
    });
  }, [navigation, data]);

  return (
    <SafeAreaView style={{flex: 1}}>
      {data ? (
        <BottomTabs tabArr={TabArr} />
      ) : (
        <View style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
          <ActivityIndicator size="large" color='#4F46E5' />
        </View>
      )}
    </SafeAreaView>
  );
};

const renderStackScreen = ({navigation, route}) => {
  return (
    <DeliveryProvider>
      <DeliveryStackScreen navigation={navigation} route={route} />
    </DeliveryProvider>
  );
};

export default renderStackScreen;
