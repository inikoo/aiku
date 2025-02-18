import React, { useContext, useState } from 'react';
import { View, FlatList, TouchableOpacity, Image, Text } from 'react-native';
import { AuthContext } from '@/src/components/Context/context';
import globalStyles from '@/globalStyles';
import { FontAwesomeIcon } from '@fortawesome/react-native-fontawesome';
import { faCheckCircle } from '@/private/fa/pro-solid-svg-icons';

const GroupItem = ({ item, navigation, selectedOrganisation }) => {
  const [imageError, setImageError] = useState(false);
  const handleImageError = () => setImageError(true);
  const { setOrganisation, userData } = useContext(AuthContext);

  const onPickOrganisation = () => {
    setOrganisation({ ...userData, organisation: item });
    navigation.navigate('fulfilment');
  };

  const isActive = selectedOrganisation?.id === item.id;

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
        <View style={globalStyles.list.avatarContainer}>
          {!imageError && item.logo?.original ? (
            <Image
              source={{ uri: item.logo.original }}
              style={globalStyles.list.avatar}
              onError={handleImageError}
            />
          ) : (
            <View style={globalStyles.list.fallbackAvatar}>
              <Text style={globalStyles.list.fallbackText}>{item.label?.charAt(0) || '?'}</Text>
            </View>
          )}
        </View>
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

const Organisation = ({ navigation }) => {
  const { userData } = useContext(AuthContext);
  const selectedOrganisation = userData.organisation;

  return (
    <View style={globalStyles.container}>
      <FlatList
        data={userData.organisations}
        keyExtractor={(item) => item.id.toString()}
        showsVerticalScrollIndicator={false}
        renderItem={({ item }) => (
          <GroupItem item={item} navigation={navigation} selectedOrganisation={selectedOrganisation} />
        )}
      />
    </View>
  );
};

export default Organisation;
