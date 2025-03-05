import React, {useContext, useRef, useState, useEffect} from 'react';
import {View, Text, TouchableOpacity, StyleSheet} from 'react-native';
import {Button, ButtonText, ButtonSpinner} from '@/src/components/ui/button';
import Modal from '@/src/components/Modal';
import TextWithScanner from '@/src/components/TextWithScanner';
import {Input, InputField, InputSlot} from '@/src/components/ui/input';
import {useForm, Controller} from 'react-hook-form';
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
    faHistory,
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
    const device = useCameraDevice('back');

    useEffect(() => {
        handleCameraPermission();
    }, []);
    const {control, handleSubmit, reset, setValue} = useForm({
        defaultValues: {
            location: location,
        },
    });

    const showingScanner = () => {
        setShowScanner(true);
        onClose();
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

    return (
        <View>
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

            <Modal
                isVisible={showScanner}
                title={'Scan Location'}
                onClose={() => setShowScanner(false)}>
                <View className="w-full">
                    <>
                        <Camera
                            codeScanner={codeScanner}
                            style={StyleSheet.absoluteFill}
                            device={device}
                            isActive={true}
                        />
                       {/*  <RNHoleView
                            holes={holesConfig()}
                            style={[
                                globalStyles.scanner.rnholeView,
                                globalStyles.scanner.fullScreenCamera,
                            ]}
                        /> */}
                    </>
                </View>
            </Modal>
        </View>
    );
};

export default ModalMoveLocation;
