import React, {useContext, useEffect, useState} from 'react';
import { View, ScrollView } from 'react-native';
import {AuthContext} from '@/src/components/Context/context';
import request from '@/src/utils/Request';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';
import {Card} from '@/src/components/ui/card';
import {Heading} from '@/src/components/ui/heading';
import {Text} from '@/src/components/ui/text';
import Description from '@/src/components/Description';
import {Divider} from '@/src/components/ui/divider';
import { Spinner } from '@/src/components/ui/spinner';

const ShowArea = ({navigation, route}) => {
  const {organisation, warehouse} = useContext(AuthContext);
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const id = route.params.id;

  const getDataFromServer = async () => {
    setLoading(true);
    request({
      urlKey: 'get-area',
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
        <Spinner size="large" />
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

  const schema = [
    {
      label : 'Code',
      value : data.code
    },
    {
      label : 'Number of locations',
      value : data.number_of_locations || "0"
    },
    {
      label : 'Stock value',
      value : data.stock_value  || "0"
    },
    {
      label : 'Number of empty locations',
      value : data.number_empty_locations || "0"
    },
  ]

  return (
    <ScrollView className="flex-1 bg-gray-50 p-4">
      <Card className="p-4 mb-4">
        <Heading className="text-xl font-bold mb-2 text-indigo-500">
          {data.name}
        </Heading>
        <Divider className="my-2" />
        <Description schema={schema} />
        
      </Card>
    </ScrollView>
  );
};

export default ShowArea;
