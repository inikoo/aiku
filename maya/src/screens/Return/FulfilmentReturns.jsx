import React, {useContext} from 'react';
import {View, TouchableOpacity, Text} from 'react-native';
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
);

const FulfilmentReturns = ({navigation}) => {
  const {organisation, warehouse} = useContext(AuthContext);

  return (
    <View style={globalStyles.container}>
      <BaseList
        navigation={navigation}
        urlKey="get-returns"
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
      onPress={() => navigation?.navigate("show-fulfilment-return", {id : item.id })}>
      <View style={globalStyles.list.container}>
        {/* Avatar Section */}
        <View style={globalStyles.list.avatarContainer}>
          {item?.state_icon && (
            <FontAwesomeIcon
              icon={item.state_icon.icon}
              color={item.state_icon.color}
              size={item.state_icon.size || 24}
              style={{marginVertical: 3}}
            />
          )}
          {item?.type_icon && (
            <FontAwesomeIcon
              icon={item.type_icon.icon}
              size={item.type_icon.size || 20}
              style={{marginVertical: 3}}
            />
          )}
        </View>

        {/* Text Section */}
        <View style={globalStyles.list.textContainer}>
          <Text style={globalStyles.list.title}>
            {item?.reference || 'No reference available'}
          </Text>
          <Text style={globalStyles.list.description}>
            {item?.customer_reference || 'No customer reference available'}
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};

export default FulfilmentReturns;
