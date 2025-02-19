import { View, Text } from 'react-native';
import { FontAwesomeIcon } from '@fortawesome/react-native-fontawesome';
import { faInbox } from '@/private/fa/pro-regular-svg-icons';

const Empty = ({ title = 'Empty', description = 'No Data Here', icon = faInbox }) => {
  return (
    <View className="flex-1 justify-center items-center">
      <View className="space-y-2 items-center">
        <FontAwesomeIcon icon={icon} size={48} color={"#D1D5DC"} />
        <Text className="text-lg font-bold text-gray-500">{title}</Text>
        <Text className="text-sm text-gray-600">{description}</Text>
      </View>
    </View>
  );
};

export default Empty;