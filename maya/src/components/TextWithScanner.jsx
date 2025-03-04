import React, { useState } from 'react';
import { View, TouchableOpacity } from 'react-native';
import { Input, InputField, InputSlot } from '@/src/components/ui/input';
import Modal from '@/src/components/Modal';

import { FontAwesomeIcon } from '@fortawesome/react-native-fontawesome';
import { faBarcodeRead } from '@/private/fa/pro-regular-svg-icons';

const TextWithScanner = ({
    value = '',
    onChangeText = () => null,
    placeholder = '',
}) => {
    const [scannerOpen, setScannerOpen] = useState(false);

    return (
        <View>
            <View className="flex-row items-center space-x-2">
                {/* Input Field */}
                <Input variant="outline" size="md" className="flex-1 rounded-t-lg">
                    <InputField
                        placeholder={value ? '' : placeholder} 
                        value={value}
                        onChangeText={onChangeText}
                    />
                </Input>

                {/* Barcode Button */}
                <TouchableOpacity
                    className="p-2 bg-gray-300 rounded-r-lg"
                    onPress={() => setScannerOpen(true)}
                >
                    <FontAwesomeIcon icon={faBarcodeRead} size={20} />
                </TouchableOpacity>
            </View>

            {/* Modal */}
            <Modal
                isVisible={scannerOpen}
                title="Move Pallet"
                onClose={() => setScannerOpen(false)}
            >
                <View className="w-full"></View>
            </Modal>
        </View>
    );
};

export default TextWithScanner;
