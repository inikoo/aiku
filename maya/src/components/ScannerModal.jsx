import {useState} from 'react';
import {Modal, View, Button, StyleSheet} from 'react-native';
import {
    Camera,
    useCameraDevice,
    useCodeScanner,
} from 'react-native-vision-camera';

import {
    holesConfig,
    handleCameraPermission,
    ScannerConfig,
} from '@/src/utils/Scanner';

const ScannerModal = visible => {
    const device = useCameraDevice('back');
    const codeScanner = useCodeScanner({
        ...ScannerConfig,
        onCodeScanned: codes => {
            console.log(codes);
        },
    });

    return (
        <View style={styles.container}>
            <Modal visible={visible} animationType="slide" transparent={false}>
                <View style={styles.modalContent}>
                    <Camera
                        codeScanner={codeScanner}
                        style={styles.camera}
                        device={device}
                        isActive={true}
                    />
                    <Button title="Close"  />
                </View>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {flex: 1, justifyContent: 'center', alignItems: 'center'},
    modalContent: {flex: 1, backgroundColor: 'black'},
    camera: {flex: 1},
});

export default ScannerModal;
