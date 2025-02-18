import {merge} from 'lodash';
import request from '@/src/utils/Request';
import { navigationRef } from '@/src/utils/NavigationService';

export function retrieveProfile(options) {
  options = merge(
    {
      onSuccess: window.noop,
      onFailed: window.noop,
    },
    options,
  );

  return request({
    urlKey: 'get-profile',
    headers: {
      Authorization: `Bearer ${options.accessToken}`,
    },
    onFailed: options.onFailed,
    onSuccess: userProfileRes => {
        options.onSuccess({...userProfileRes})
      }
  });
}

/* export function revokeToken(
    token = getDvaApp()._store.getState().user?.access_token,
    removeSession = true,
  ) {
    request({
      method: 'post',
      urlKey: 'logout',
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      data: {
        token,
        client_id: appConfig.oauth.id,
        client_secret: appConfig.oauth.secret,
      },
      onBefore(options) {
        delete options.headers['Authorization'];
      },
    }).then();
  
    if (removeSession) getDvaApp()._store.dispatch({ type: 'user/remove' });
  
    return null;
  } */


  export function logout(signOut) {
    console.log("Logging out...");
  
    if (signOut) {
      signOut();
    } else {
      if (navigationRef.isReady()) {
        navigationRef.navigate('session-expired');
      }
    }
  }
  