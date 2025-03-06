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
} from '@/private/fa/pro-light-svg-icons';
library.add(faSeedling, faShare, faSpellCheck, faCheck, faCross, faCheckDouble);

const AgentSkus = ({navigation}) => {
  const {organisation, warehouse} = useContext(AuthContext);

  return (
    <View style={globalStyles.container}>
      <BaseList
        navigation={navigation}
        urlKey='get-agent-skus'
        height="0"
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
      style={[globalStyles.list.card, { paddingBottom : 5 }]}
      activeOpacity={0.7}
    //   onPress={() => navigation.navigate("show-fulfilment-delivery",{ id : item.id })}
      >
      <View style={globalStyles.list.container}>
       {/*  <View style={globalStyles.list.avatarContainer}>
          <FontAwesomeIcon
            className={item.state_icon.class}
            color={item?.state_icon?.color}
            icon={item.state_icon.icon}
          />
        </View> */}
        <View style={globalStyles.list.textContainer}>
          <Text style={globalStyles.list.title}>{item.code}</Text>
          <Text style={globalStyles.list.description}>
            {item.name || 'No customer name available'}
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};

export default AgentSkus;
