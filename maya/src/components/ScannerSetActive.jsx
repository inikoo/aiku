import {Text, View, TouchableOpacity} from 'react-native';
import globalStyles from '@/globalStyles';

export default function ScannerSetActive(props) {
  return (
    <View style={globalStyles.scanner.centered}>
      <TouchableOpacity onPress={props.onPress}>
        <Text>Touch to scan</Text>
      </TouchableOpacity>
    </View>
  );
}
