import React, { useContext, useState } from 'react';
import { View, FlatList, TouchableOpacity, Image, Text } from 'react-native';
import { AuthContext } from '@/src/components/Context/context';
import globalStyles from '@/globalStyles';
import { FontAwesomeIcon } from '@fortawesome/react-native-fontawesome';
import { faCheckCircle } from '@/private/fa/pro-solid-svg-icons';

const GroupItem = ({ item, navigation, selectedfulfilment }) => {
  const { setFulfilment, userData } = useContext(AuthContext);
  const isActive = selectedfulfilment?.id === item.id;
  const onPickOrganisation = () => {
    setFulfilment({ ...userData, fulfilment : item, warehouse : item.warehouse[0] })
    navigation.navigate('home-drawer')
  }

  return (
    <TouchableOpacity
    style={[
      globalStyles.list.card,
      isActive && globalStyles.list.activeCard,
    ]}
    activeOpacity={0.7}
    onPress={onPickOrganisation}
  >
    <View style={globalStyles.list.container}>
      <View style={globalStyles.list.textContainer}>
        <Text style={globalStyles.list.title}>{item.label}</Text>
        <Text style={globalStyles.list.description}>{item.type || 'No description available'}</Text>
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

const Fulfilment = ({ navigation }) => {
  const { userData, organisation, fulfilment } = useContext(AuthContext);
  const selectedfulfilment = userData.fulfilment;
  return (
    <View style={globalStyles.container}>
      <FlatList
        data={organisation?.authorised_fulfilments ? organisation?.authorised_fulfilments : []}
        keyExtractor={(item) => item.id.toString()}
        showsVerticalScrollIndicator={false}
        renderItem={({ item }) => <GroupItem item={item} navigation={navigation} selectedfulfilment={selectedfulfilment}/>}
      />
    </View>
  );
};


export default Fulfilment;
