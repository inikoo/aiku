import {useEffect, useState, useContext} from 'react';
import {StyleSheet} from 'react-native';
import {
  Camera,
  useCameraDevice,
  useCodeScanner,
} from 'react-native-vision-camera';
import {RNHoleView} from 'react-native-hole-view';
import {
  holesConfig,
  handleCameraPermission,
  ScannerConfig,
} from '@/src/utils/Scanner';
import globalStyles from '@/globalStyles';
import ScannerNotFound from '@/src/components/ScannerNotFound';
import ScannerSetActive from '@/src/components/ScannerSetActive';
import {AuthContext} from '@/src/components/Context/context';
import {ALERT_TYPE, Toast} from 'react-native-alert-notification';

export default function Scanner({navigation}) {
  const {organisation, warehouse} = useContext(AuthContext);
  const [scannedCode, setScannedCode] = useState(null);
  const [active, setActive] = useState(true);
  const device = useCameraDevice('back');

  useEffect(() => {
    handleCameraPermission();
  }, []);


  const onGetData = codes => {
  };

  const goToDetail = (response) => {       
  };
  

  const codeScanner = useCodeScanner({
    ...ScannerConfig,
    onCodeScanned: codes => {
      if (codes.length > 0) {
        setActive(false);
        onGetData(codes[0].value);
      }
    },
  });

  if (!device) {
    return <ScannerNotFound />;
  }

  return (
    <View style={{flex: 1}}>
      {active ? (
        <>
          <Camera
            codeScanner={codeScanner}
            style={StyleSheet.absoluteFill}
            device={device}
            isActive={true}
          />
          <RNHoleView
            holes={holesConfig()}
            style={[
              globalStyles.scanner.rnholeView,
              globalStyles.scanner.fullScreenCamera,
            ]}
          />
        </>
      ) : (
        <ScannerSetActive onPress={() => setActive(true)} />
      )}
    </View>
  );
}
