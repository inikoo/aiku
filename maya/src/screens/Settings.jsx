import React, {useContext, useState} from 'react';
import {HStack} from '@/src/components/ui/hstack';
import {
  ChevronRightIcon,
  EditIcon,
  Icon,
  SettingsIcon,
} from '@/src/components/ui/icon';
import {
  Image,
  ScrollView,
  SafeAreaView,
  View,
  TouchableOpacity,
} from 'react-native';
import {Text} from '@/src/components/ui/text';
import {VStack} from '@/src/components/ui/vstack';
import {Button, ButtonIcon, ButtonText} from '@/src/components/ui/button';
import {Heading} from '@/src/components/ui/heading';
import {Center} from '@/src/components/ui/center';
import {Divider} from '@/src/components/ui/divider';

import {AuthContext} from '@/src/components/Context/context';

import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {
  faUsers,
  faHandHoldingBox,
  faChevronRight,
  faWarehouseAlt
} from '@/private/fa/pro-regular-svg-icons';

const MainContent = ({navigation}) => {
  const {userData, organisation} = useContext(AuthContext);
  const [imageError, setImageError] = useState(false);

  const handleImageError = () => setImageError(true);
  const getInitials = name => {
    if (!name) return '?';
    return name
      .split(' ')
      .map(word => word[0])
      .join('')
      .toUpperCase();
  };
  
  const accountData = [
    {icon: faUsers, subText: 'Organisation', route: 'organisation'},
  ];

  if (organisation) {
    accountData.push({
      icon: faHandHoldingBox,
      subText: 'Fulfilment',
      route: 'fulfilment',
    },{
      icon: faWarehouseAlt,
      subText: 'warehouse',
      route: 'warehouse',
    });
  }

  return (
    <VStack className="h-full w-full mb-16 md:mb-0">
      <ScrollView showsVerticalScrollIndicator={false}>
        <VStack className="h-full w-full pb-8 space-y-8">
          <Center className="border-b p-5 bg-indigo-500">
            <View
              className="w-full h-60 flex justify-center items-center bg-indigo-500"
              style={{
                borderBottomLeftRadius: 30,
                borderBottomRightRadius: 30,
              }}>
              <VStack className="items-center space-y-4 p-6">
                {!imageError && userData?.image?.original ? (
                  <Image
                    source={{uri: userData.image.original}}
                    className="w-20 h-20 rounded-full border-4 border-white shadow-lg"
                    onError={handleImageError}
                  />
                ) : (
                  <View className="w-20 h-20 rounded-full bg-gray-300 flex items-center justify-center mb-2 bg-white">
                    <Text className="text-xl font-bold text-gray-700">
                      {getInitials(userData?.username)}
                    </Text>
                  </View>
                )}
                {/*  <Image
                  source={require('@/asssets/Image/user-profile.jpg')}
                  className="w-20 h-20 rounded-full border-4 border-white shadow-lg"
                  alt="Profile Image"
                /> */}
                <VStack className="items-center space-y-1">
                  <Text
                    size="2xl"
                    className="font-bold text-white drop-shadow-lg">
                    {userData.username}
                  </Text>
                  <Text className="text-sm text-gray-200 drop-shadow-md">
                    {userData.email}
                  </Text>
                </VStack>
              </VStack>
              <Center>
                <Button onPress={()=>navigation.navigate("edit-profile")} className="gap-3 border-gray-400 px-6 py-2 rounded-full shadow-sm bg-white border">
                  <ButtonText className="text-dark font-semibold">
                    Edit Profile
                  </ButtonText>
                  <ButtonIcon as={EditIcon} className="text-indigo-500" />
                </Button>
              </Center>
            </View>
          </Center>
          <VStack className="mx-6 space-y-6 p-5">
            <Heading className="font-roboto text-xl mb-3">Setting</Heading>
            <VStack className="py-5 px-6 border rounded-2xl border-gray-300 shadow-lg space-y-4 bg-white">
              {accountData.map((item, index) => (
                <React.Fragment key={index}>
                  <TouchableOpacity
                    onPress={() => navigation.navigate(item.route)}
                    className="justify-start w-full py-2">
                    <HStack className="items-center gap-3">
                      <FontAwesomeIcon
                        icon={item.icon}
                        className="text-gray-600"
                      />
                      <Text size="lg" className="text-gray-800 font-medium">
                        {item.subText}
                      </Text>
                    </HStack>
                  </TouchableOpacity>
                  {index < accountData.length - 1 && (
                    <Divider className="my-1 bg-gray-300" />
                  )}
                </React.Fragment>
              ))}
            </VStack>
          </VStack>
        </VStack>
      </ScrollView>
    </VStack>
  );
};

export const Settings = props => (
  <SafeAreaView>
    <MainContent navigation={props.navigation} />
  </SafeAreaView>
);

export default Settings;
