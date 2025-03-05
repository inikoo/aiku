import {useEffect, useState, useContext} from 'react';
import {StyleSheet} from 'react-native';
import {SafeAreaView} from 'react-native-safe-area-context';
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
import request from '@/src/utils/Request';
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
    request({
      urlKey: 'get-scanner',
      args: [organisation.id, warehouse.id, codes],
      onSuccess: goToDetail,
      onFailed: error => {
        console.log(error)
        Toast.show({
          type: ALERT_TYPE.DANGER,
          title: 'Error',
          textBody: error?.data?.message || 'Failed to fetch data',
        });
      },
    });
  };

  const goToDetail = (response) => {       
    switch (response.data.model_type) {
      case "PalletReturn":
        navigation.navigate("show-fulfilment-return", { id: response.data.model.id });
        break;
      case "PalletDelivery":
        navigation.navigate("show-fulfilment-delivery", { id: response.data.model.id });
        break;
      case "Pallet":
        navigation.navigate("show-pallet", { id: response.data.model.id });
        break;
      case "Location":
        navigation.navigate("show-location", { id: response.data.model.id });
        break;
      default:
        console.warn("Unknown response type:", response.data.model_type);
    }
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
    <SafeAreaView style={{flex: 1}}>
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
    </SafeAreaView>
  );
}
