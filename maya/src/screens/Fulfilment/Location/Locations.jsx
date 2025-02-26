import React, {useContext} from 'react';
import {View, TouchableOpacity, Text} from 'react-native';
import dayjs from 'dayjs';
import {AuthContext} from '@/src/components/Context/context';
import BaseList from '@/src/components/BaseList';
import globalStyles from '@/globalStyles';

const Locations = ({navigation}) => {
  const {organisation, warehouse} = useContext(AuthContext);

  return (
    <View style={globalStyles.container}>
      <BaseList
        navigation={navigation}
        urlKey="get-locations"
        args={[organisation.id, warehouse.id]}
        listItem={({item, navigation}) => (
          <GroupItem item={item} navigation={navigation} />
        )}
      />
    </View>
  );
};

const GroupItem = ({item, navigation}) => {
  return (
    <TouchableOpacity
      style={globalStyles.list.card}
      activeOpacity={0.7}
      onPress={() => navigation.navigate("show-location", { id : item.id })}>
      <View style={globalStyles.list.container}>
        <View style={globalStyles.list.textContainer}>
          <Text style={globalStyles.list.title}>{item.code}</Text>
          <Text style={globalStyles.list.description}>
            Stock : {item.stock_value}
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};



export default Locations;
