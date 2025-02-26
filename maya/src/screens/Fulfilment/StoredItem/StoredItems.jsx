import React, {useContext} from 'react';
import {View, TouchableOpacity, Text} from 'react-native';
import dayjs from 'dayjs';
import {AuthContext} from '@/src/components/Context/context';
import BaseList from '@/src/components/BaseList';
import globalStyles from '@/globalStyles';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
  faSeedling,
  faShare,
  faSpellCheck,
  faCheck,
  faCross,
  faCheckDouble,
  faPallet,
  faNarwhal,
  faSortSizeUp,
  faTruck,
  faInventory
} from '@/private/fa/pro-light-svg-icons';
library.add(
  faSeedling,
  faShare,
  faSpellCheck,
  faCheck,
  faCross,
  faCheckDouble,
  faPallet,
  faNarwhal,
  faSortSizeUp,
  faTruck,
  faInventory
);



const StoredItems = ({navigation}) => {
  const {organisation, warehouse} = useContext(AuthContext);

  return (
    <View style={globalStyles.container}>
      <BaseList
        navigation={navigation}
        urlKey="get-stored-items"
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
    onPress={() => navigation.navigate("show-stored-item", { id : item.id })}>
    <View style={globalStyles.list.container}>
      <View style={globalStyles.list.avatarContainer}>
        <FontAwesomeIcon
          className={item.state_icon.class}
          color={item.state_icon.color}
          icon={item.state_icon.icon}
        />
      </View>
      <View style={globalStyles.list.textContainer}>
        <Text style={globalStyles.list.title}>{item.reference}</Text>
        <Text style={globalStyles.list.description}>
          {item.customer_name|| 'No customer available'}
        </Text>
      </View>
    </View>
  </TouchableOpacity>
  );
};

export default StoredItems;
