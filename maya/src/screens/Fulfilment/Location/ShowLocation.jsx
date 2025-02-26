import React, {useContext, useEffect, useState} from 'react';
import {View, ScrollView, ActivityIndicator} from 'react-native';
import {AuthContext} from '@/src/components/Context/context';
import request from '@/src/utils/Request';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';
import {Card} from '@/src/components/ui/card';
import {Heading} from '@/src/components/ui/heading';
import {Text} from '@/src/components/ui/text';
import Description from '@/src/components/Description';
import Barcode from 'react-native-barcode-svg';
import {Center} from '@/src/components/ui/center';

const ShowLocation = ({navigation, route}) => {
  const {organisation, warehouse} = useContext(AuthContext);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const id = route.params.id;

  const getDataFromServer = async () => {
    setLoading(true);
    request({
      urlKey: 'get-location',
      args: [organisation.id, warehouse.id, id],
      onSuccess: response => {
        setData(response);
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
      label: 'code',
      value: data?.code,
    },
    {label: 'Status', value: data?.status},
    {label: 'Stock Value', value: data?.stock_value},
    {
      label: 'Empty',
      value: data?.is_empty,
    },
    {
      label: 'Max weight',
      value: data?.max_weight ? data.max_weight.toString() : '0',
    },
    {
      label: 'Max volume',
      value: data?.max_volume ? data.max_volume.toString() : '0',
    },
  ];

  useEffect(() => {
    getDataFromServer();
  }, []);

  if (loading) {
    return (
      <View className="flex-1 items-center justify-center bg-gray-100">
        <ActivityIndicator size="large" color='#4F46E5' />
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
      {/* <Card className="bg-indigo-600 p-6 rounded-xl shadow-lg mb-5">
      <Heading className="text-white text-2xl font-bold">
        Location : {data.code}
      </Heading>
    </Card> */}

      <Card>
        <Center>
          <Barcode
            value={data?.slug}
            format="CODE128"
            maxWidth={250}
            height={60}
          />
        </Center>
        <Center>
          <Heading>{data?.slug}</Heading>
        </Center>
      </Card>

      <Card className="bg-white p-6 rounded-xl shadow-md mt-4">
        <Heading>Details</Heading>
        <Description schema={schema} />
      </Card>
    </ScrollView>
  );
};

export default ShowLocation;
