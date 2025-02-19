import React from 'react';
import { View, FlatList, TouchableOpacity, Text } from 'react-native';
import globalStyles from '@/globalStyles';
import Empty from '@/src/components/Empty'

const ItemsInPallets = ({navigation, route, data }) => {
  return (
    <View style={globalStyles.container}>
      <FlatList
        data={data.items}
        keyExtractor={(item) => item.id.toString()}
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={(
          <Empty />
        )}
        renderItem={({ item }) => (
          <GroupItem item={item} navigation={navigation}  />
        )}
      />
    </View>
  );
};


const GroupItem = ({ item, navigation }) => {
  return (
    <TouchableOpacity
      style={[
        globalStyles.list.card,
        isActive && globalStyles.list.activeCard,
      ]}
      activeOpacity={0.7}
    >
      <View style={globalStyles.list.container}>
        <View style={globalStyles.list.textContainer}>
          <Text style={globalStyles.list.title}>{item.name}</Text>
          <Text style={globalStyles.list.description}>{item.code || 'No code available'}</Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};

export default ItemsInPallets;
