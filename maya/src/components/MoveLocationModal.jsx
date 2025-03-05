import React, {useState} from 'react';
import {
    View,
    Text,
    TouchableOpacity,
    StyleSheet,
    Modal as ModalNative,
} from 'react-native';
import {Button, ButtonText, ButtonSpinner} from '@/src/components/ui/button';
import Modal from '@/src/components/Modal';
import {Input, InputField} from '@/src/components/ui/input';
import {useForm, Controller} from 'react-hook-form';
import ScannerModal from '@/src/components/ScannerModal';

import {
    holesConfig,
    handleCameraPermission,
    ScannerConfig,
} from '@/src/utils/Scanner';
import {
    Camera,
    useCameraDevice,
    useCodeScanner,
} from 'react-native-vision-camera';
import globalStyles from '@/globalStyles';
import {RNHoleView} from 'react-native-hole-view';
import {FontAwesomeIcon} from '@fortawesome/react-native-fontawesome';
import {library} from '@fortawesome/fontawesome-svg-core';
import {
    faSeedling,
    faShare,
    faSpellCheck,
    faCheck,
    faCross,
    faCheckDouble,
    faWarehouseAlt,
    faPallet,
    faTimes,
} from '@/private/fa/pro-light-svg-icons';
import {
    faFileInvoiceDollar,
    faInventory,
    faTimes as faTimesRegular,
    faSave,
    faBarcodeRead,
} from '@/private/fa/pro-regular-svg-icons';

library.add(
    faSeedling,
    faShare,
    faSpellCheck,
    faCheck,
    faCross,
    faCheckDouble,
    faWarehouseAlt,
    faPallet,
    faTimes,
    faFileInvoiceDollar,
);

const ModalMoveLocation = ({
    isVisible = false,
    title = 'Move Pallet',
    onClose = () => null,
    onSave = e => console.log(e),
    location = '',
}) => {
    const [loadingSave, setLoadingSave] = useState(false);
    const [showScanner, setShowScanner] = useState(false);
    const {control, handleSubmit, reset, setValue} = useForm({
        defaultValues: {
            location: location,
        },
    });

    const showingScanner = () => {
        setShowScanner(true);
        onClose();
    };

    const device = useCameraDevice('back');
    const codeScanner = useCodeScanner({
        ...ScannerConfig,
        onCodeScanned: codes => {
            if (codes[0].value) {
                setValue('location', codes[0].value);
                setShowScanner(false);
                
                // ✅ Ensure form data is submitted properly
                setTimeout(() => {
                    handleSubmit(onSave)();
                }, 200); 
            }
        },
    });
    

    return (
        <View style={styles.container}>
            <Modal isVisible={isVisible} title={title} onClose={onClose}>
                <View className="w-full">
                    <Text className="text-sm font-semibold mb-1">Location</Text>
                    <Controller
                        name="location"
                        control={control}
                        render={({field}) => (
                            <View className="flex-row items-center space-x-2">
                                {/* Input Field */}
                                <Input
                                    variant="outline"
                                    size="md"
                                    className="flex-1 rounded-l-lg">
                                    <InputField
                                        placeholder="location code"
                                        value={field.value}
                                        onChangeText={field.onChange}
                                    />
                                </Input>

                                {/* Barcode Button */}
                                <TouchableOpacity
                                    className="p-2 bg-gray-300 rounded-r-lg"
                                    onPress={showingScanner}>
                                    <FontAwesomeIcon
                                        icon={faBarcodeRead}
                                        size={20}
                                    />
                                </TouchableOpacity>
                            </View>
                        )}
                    />

                    <Button
                        size="lg"
                        className="my-3"
                        onPress={handleSubmit(onSave)}>
                        {loadingSave ? (
                            <ButtonSpinner />
                        ) : (
                            <View
                                style={{
                                    flexDirection: 'row',
                                    alignItems: 'center',
                                    gap: 8,
                                }}>
                                <FontAwesomeIcon
                                    icon={faSave}
                                    size={20}
                                    color="#fff"
                                />
                                <ButtonText>Move Pallet</ButtonText>
                            </View>
                        )}
                    </Button>
                </View>
            </Modal>

            <View style={styles.container}>
                <ModalNative
                    visible={showScanner}
                    animationType="slide"
                    transparent={false}
                    onRequestClose={() => setShowScanner(false)}
                    presentationStyle="fullScreen">
                    <View style={styles.modalContent}>
                        <Camera
                            codeScanner={codeScanner}
                            style={styles.camera}
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
                    </View>
                </ModalNative>
            </View>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {flex: 1, justifyContent: 'center', alignItems: 'center'},
    modalContent: {flex: 1, backgroundColor: 'black'},
    camera: {flex: 1},
});
export default ModalMoveLocation;
