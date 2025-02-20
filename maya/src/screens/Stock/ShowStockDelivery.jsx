import React, {useContext, useEffect, useState} from 'react';
import {View, ScrollView, ActivityIndicator} from 'react-native';
import {AuthContext} from '@/src/components/Context/context';
import request from '@/src/utils/Request';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';
import {Card} from '@/src/components/ui/card';
import {Heading} from '@/src/components/ui/heading';
import {Text} from '@/src/components/ui/text';
import dayjs from 'dayjs';

// Import FontAwesome icons
import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {
  faCalendarWeek,
  faBoxOpen,
  faWeightHanging,
  faClock,
  faSyncAlt,
} from '@/private/fa/pro-regular-svg-icons';

const ShowStockDelivery = ({navigation, route}) => {
  const {organisation, warehouse} = useContext(AuthContext);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const id = route.params.id;

  const getDataFromServer = async () => {
    setLoading(true);
    request({
      urlKey: "get-stock-delivery" ,
      args: [organisation.id, warehouse.id, id],
      onSuccess: response => {
        setData(response.data);
        setLoading(false);
      },
      onFailed: error => {
        setLoading(false);
        Toast.show({
          type: ALERT_TYPE.DANGER,
          title: 'Error',
          textBody: error.detail?.message || 'Failed to fetch data',
        });
      },
    });
  };

  useEffect(() => {
    getDataFromServer();
  }, []);

  if (loading) {
    return (
      <View className="flex-1 items-center justify-center bg-gray-100">
        <ActivityIndicator size="large" color="#3b82f6" />
      </View>
    );
  }

  if (!data) {
    return (
      <View className="flex-1 items-center justify-center bg-gray-100">
        <Text className="text-lg text-gray-600">No Data Available</Text>
      </View>
    );
  }

  return (
    <ScrollView className="flex-1 bg-gray-50 p-4">
     <Text>Show Locations</Text>
    </ScrollView>
  );
};

export default ShowStockDelivery;
