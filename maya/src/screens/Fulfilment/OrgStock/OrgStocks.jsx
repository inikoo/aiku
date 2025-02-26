import React, {useContext} from 'react';
import {View, TouchableOpacity, Text} from 'react-native';
import dayjs from 'dayjs';
import {AuthContext} from '@/src/components/Context/context';
import BaseList from '@/src/components/BaseList';
import globalStyles from '@/globalStyles';

const OrgStocks = ({navigation}) => {
  const {organisation, warehouse} = useContext(AuthContext);

  return (
    <View style={globalStyles.container}>
      <BaseList
        navigation={navigation}
        urlKey='get-org-stocks'
        args={[organisation.id, warehouse.id]}
        listItem={({item, navigation}) => (
          <GroupItem item={item} navigation={navigation} />
        )}
      />
    </View>
  );
};

const GroupItem = ({item, navigation}) => {
  const formattedDate = item.date
    ? dayjs(item.date).format('MMMM D[,] YYYY') // Example: "August 27th, 2019"
    : 'No Date Available';

  return (
    <TouchableOpacity
      style={[
        globalStyles.list.card,
        {
          flexDirection: 'row',
          justifyContent: 'space-between',
          alignItems: 'center',
          padding: 10,
        },
      ]}
      activeOpacity={0.7}
      onPress={() => navigation.navigate("show-org-stock",{id:item.id})}>
      {/* Left section (Text Content) */}
      <View style={{flex: 1}}>
        <Text style={globalStyles.list.title}>{item.name}</Text>
        <Text style={globalStyles.list.description}>
          {item.code || '[No code available]'} - {item.code}
        </Text>
      </View>
    </TouchableOpacity>
  );
};

export default OrgStocks;
