import {Text, View} from 'react-native';
import globalStyles from '@/globalStyles';

export default function ScannerNotFound(props) {
  return (
    <View style={globalStyles.scanner.centered}>
      <Text>Camera Not Found</Text>
    </View>
  );
}
