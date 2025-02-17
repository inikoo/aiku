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
import {retrieveProfile} from '@/src/user';

export default function ScannerLogin() {
  const {signIn} = useContext(AuthContext);
  const [active, setActive] = useState(true);
  const device = useCameraDevice('back');

  useEffect(() => {
    handleCameraPermission();
  }, []);

  const handleLogin = async codes => {
    return new Promise(resolve => {
      request({
        method: 'post',
        urlKey: "login-scanner",
        autoRefreshExpiredToken: false,
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        data: {
          code: codes,
          device_name: 'android',
        },
        onBoth: async (isSuccess, userRes) => {
          if (isSuccess) {
            await retrieveProfile({
              accessToken: userRes.token,
              onSuccess: async profileRes => {
                const user = {...userRes, ...profileRes.data};
                signIn(user);
                resolve(true);
              },
              onFailed: err => {
                Toast.show({
                  type: ALERT_TYPE.DANGER,
                  title: 'Error',
                  textBody: err?.detail?.message || 'Failed to fetch data',
                });
                console.error('Profile Retrieval Failed:', err);
              },
            });
          } else {
            console.error('Login Failed:', userRes);
            Toast.show({
              type: ALERT_TYPE.DANGER,
              title: 'Error',
              textBody: userRes?.detail?.message || 'Failed to fetch data',
            });
            resolve(false);
          }
        },
      });
    });
  };

  const codeScanner = useCodeScanner({
    ...ScannerConfig,
    onCodeScanned: codes => {
      if (codes.length > 0) {
        setActive(false);
        handleLogin(codes[0].value);
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
