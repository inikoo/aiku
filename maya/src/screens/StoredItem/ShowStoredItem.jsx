import React, {useContext, useEffect, useState} from 'react';
import {View, ScrollView, ActivityIndicator} from 'react-native';
import {AuthContext} from '@/src/components/Context/context';
import request from '@/src/utils/Request';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';
import {Card} from '@/src/components/ui/card';
import {Heading} from '@/src/components/ui/heading';
import {Text} from '@/src/components/ui/text';
import dayjs from 'dayjs';
import Description from '@/src/components/Description';
import { FontAwesomeIcon } from '@fortawesome/react-native-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core'

import { faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faSortSizeUp, faCircle } from '@/private/fa/pro-light-svg-icons'
library.add(faSeedling, faShare, faSpellCheck, faCheck, faCheckDouble, faSortSizeUp)

const ShowStoredItem = ({navigation, route}) => {
  const {organisation, warehouse} = useContext(AuthContext);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const id = route.params.id;
  console.log(id)

  const getDataFromServer = async () => {
    setLoading(true);
    request({
      urlKey: 'get-stored-item',
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

  const schema = [
    {
        label : "Customer",
        value : data?.customer_name
    },
    {
        label: 'State',
        value: (
     <View className="flex-row items-center justify-center gap-2">
            <FontAwesomeIcon icon={data?.state_icon?.icon ||  faCircle } color={data?.state_icon?.color} />
            <Text className="text-center">{data?.state}</Text>
          </View>
        ),
      },
    {
        label : "Location",
        value : data?.location
    },
    {
        label : "Quantity",
        value : data?.quantity ? data?.quantity.toString() : "0"
    },
  ];

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
      {/* Header */}
      <Card className="bg-indigo-600 p-6 rounded-xl shadow-lg mb-5">
        <Heading className="text-white text-2xl font-bold">
          {data.reference || 'N/A'}
        </Heading>
        <Text className="text-white text-lg font-semibold">
          Status: {data.state.toUpperCase()}
        </Text>
      </Card>

      {/* Detail Section */}
      <Card className="bg-white p-6 rounded-xl shadow-md">
        <Heading>Details</Heading>
        <Description schema={schema} />
      </Card>
    </ScrollView>
  );
};

export default ShowStoredItem;
