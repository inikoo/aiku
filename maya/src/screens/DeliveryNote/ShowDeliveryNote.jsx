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

const ShowDeliveryNote = ({navigation, route}) => {
  const {organisation, warehouse} = useContext(AuthContext);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const id = route.params.id;

  const getDataFromServer = async () => {
    setLoading(true);
    request({
      urlKey: 'get-delivery-note',
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
      label: 'Date',
      value: data?.date ? dayjs(data.date).format('MMMM D, YYYY') : "N/A",
    },
    {label: 'Type', value: data?.type},
    {label: 'Weight', value: `${data?.weight} kg`},
    {
      label: 'Created',
      value: data?.created_at ? dayjs(data.created_at).format('MMMM D, YYYY') : "N/A",
    },
    {
      label: 'Updated',
      value: data?.updated_at ? dayjs(data.updated_at).format('MMMM D, YYYY') : "N/A",
    },
  ];

  useEffect(() => {
    getDataFromServer();
  }, []);

  if (loading) {
    return (
      <View className="flex-1 items-center justify-center bg-gray-100">
        <ActivityIndicator size="large" color='#4F46E5'/>
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
          Delivery Note: {data.reference || 'N/A'}
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

export default ShowDeliveryNote;
