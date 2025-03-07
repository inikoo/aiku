import React, { useContext, useState } from 'react';
import { View, FlatList, TouchableOpacity, Image, Text } from 'react-native';
import { AuthContext } from '@/src/components/Context/context';
import globalStyles from '@/globalStyles';
import { FontAwesomeIcon } from '@fortawesome/react-native-fontawesome';
import { faCheckCircle } from '@/private/fa/pro-solid-svg-icons';

const GroupItem = ({ item, navigation, selectedWarehouse }) => {
  const { setFulfilmentWarehouse, userData, organisation } = useContext(AuthContext);
  const isActive = selectedWarehouse?.id === item.id;
  const onPickWarehouse = () => {
    setFulfilmentWarehouse({ ...userData, warehouse : item , organisation : organisation})
    navigation.navigate('home-drawer')
  }

  return (
    <TouchableOpacity
    style={[
      globalStyles.list.card,
      isActive && globalStyles.list.activeCard,
    ]}
    activeOpacity={0.7}
    onPress={onPickWarehouse}
  >
    <View style={globalStyles.list.container}>
      <View style={globalStyles.list.textContainer}>
        <Text style={globalStyles.list.title}>{item.label}</Text>
        <Text style={globalStyles.list.description}>{item.code || 'No code available'}</Text>
      </View>
      {isActive && (
        <View style={globalStyles.list.activeIndicator}>
          <FontAwesomeIcon icon={faCheckCircle} color='green'/>
        </View>
      )}
    </View>
  </TouchableOpacity>
  );
};

const Warehouse = ({ navigation }) => {
  const { userData, organisation, fulfilment, warehouse } = useContext(AuthContext);
  const selectedWarehouse = userData.warehouse;
  console.log(organisation)

  return (
    <View style={globalStyles.container}>
      <FlatList
        data={organisation?.authorised_warehouses ? organisation?.authorised_warehouses : []}
        keyExtractor={(item) => item.id.toString()}
        showsVerticalScrollIndicator={false}
        renderItem={({ item }) => <GroupItem item={item} navigation={navigation} selectedWarehouse={selectedWarehouse}/>}
      />
    </View>
  );
};


export default Warehouse;
