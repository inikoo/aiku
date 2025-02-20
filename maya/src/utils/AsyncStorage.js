import AsyncStorage from '@react-native-async-storage/async-storage';

export const storeData = async (name, value) => {
  try {
    const jsonValue = JSON.stringify(value);
    await AsyncStorage.setItem(name, jsonValue);
  } catch (e) {
    console.error('failed to save');
    // saving error
  }
};

export const getData = async name => {
  try {
    const jsonValue = await AsyncStorage.getItem(name);
    return jsonValue != null ? JSON.parse(jsonValue) : null;
  } catch (e) {
    return null;
  }
};
