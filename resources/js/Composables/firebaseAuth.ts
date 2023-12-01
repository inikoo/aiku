import { getAuth, signInWithCustomToken, signOut } from "firebase/auth"

const auth = getAuth()

// Sign in
export const useAuthFirebase = (tokenBackend: string) => {
     signInWithCustomToken(auth, tokenBackend)
         .then((userCredential) => {
             console.log("Successfully login.")
         })
         .catch((error) => {
             const errorCode = error.code;
             const errorMessage = error.message;
             console.log("Error login.")
             console.error(error)

             // ...
     });
}

// Sign out
export const useSignOutFirebase = () => {
    signOut(auth).then(() => {
        console.log("Logged out.")
    }).catch((error) => {
        console.error(error.message)
    })
}
