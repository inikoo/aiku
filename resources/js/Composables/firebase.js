import {initializeApp} from 'firebase/app';
import {getDatabase, set, ref as dbRef} from 'firebase/database';
import {useDatabaseList} from 'vuefire';
// import {
//     initializeAppCheck,
//     ReCaptchaEnterpriseProvider,
// } from 'firebase/app-check';

const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    databaseURL: import.meta.env.VITE_FIREBASE_DATABASE_URL,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MSG_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID,
    measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID
};

// Init Firebase with config
const firebaseApp = initializeApp(firebaseConfig);

//todo activate this
//initializeAppCheck(firebaseApp, {provider: new ReCaptchaEnterpriseProvider(import.meta.env.VITE_RECAPTCHA_APP_KEY),});

let db = getDatabase(firebaseApp);

export const getDbRef = (column) => {
    return dbRef(db, column);
};

// Get the data
export const getDataFirebase = (column) => {

    try {
        return useDatabaseList(getDbRef(column));
    } catch (error) {
        console.error('An error occurred while fetching data from Firebase:', error);
        return [];
    }

};

export const setDataFirebase = async (column, data) => {
    const dbReference = getDbRef(column);
    console.log('vuefire',dbReference)
    try {
      await set(dbReference, data);
      console.log('Data set successfully in Firebase:', data);
    } catch (error) {
      console.error('Error setting data in Firebase:', error);
      throw error; // You can handle the error further as needed
    }
  };
