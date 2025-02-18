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
} from '@/private/fa/pro-regular-svg-icons';

const EditProfile = () => {
  const {userData} = useContext(AuthContext);
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
               </View>
             </Center>
             <VStack className="mx-6 space-y-6 p-5">
             </VStack>
           </VStack>
         </ScrollView>
       </VStack>
  );
};

export default EditProfile;
