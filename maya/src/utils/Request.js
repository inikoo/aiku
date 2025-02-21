import axios from 'axios';
/* import { API_URL } from "@env" */
import Config from 'react-native-config';
import urlConfig from '@/config/url';
import { logout } from '@/src/user';
import { getData } from "@/src/utils/AsyncStorage";


const ERROR_BY_STATUSES = {
  400: 'Data sent is invalid', // Bad Request
  401: 'Session expired', // Unauthorized
  403: 'You do not have permission to perform this action.', // Forbidden
  404: 'Request Data Not Found', // Not Found
  405: 'Method Not Allowed', // Method Not Allowed
  408: 'Request Timeout',
  422: 'Unprocessable Content',   
  500: 'Internal Server Error, please try again later.', // Internal Server Error
  offline: 'Fail connecting to server, please try again',
  default: 'Failed to fetch, please contact your admin!',
};

let isRefreshingToken = false;
let refreshSubscribers: ((token) => void)[] = [];

const api = axios.create({
  baseURL: Config.API_URL,
  headers: { 
    'Content-Type': 'application/json', 
    'Maya-Version': '1',
   },
});

// **Token Refresh Handler**
const onTokenRefreshed = (newToken) => {
  refreshSubscribers.forEach((callback) => callback(newToken));
  refreshSubscribers = [];
};

const addRefreshSubscriber = (callback: (token) => void) => {
  refreshSubscribers.push(callback);
};


// **Axios Request Interceptor**
api.interceptors.request.use(
  async (config) => {
    const state = await getData('persist:user');
    const accessToken = state?.token;

    if (accessToken) {
      config.headers.Authorization = `Bearer ${accessToken}`;
    }

    return config;
  },
  (error) => Promise.reject(error),
);

// **Axios Response Interceptor**
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    if (error.response?.status == 401) {
      logout();
      /* if (!isRefreshingToken) {
        isRefreshingToken = true;
        try {
          console.log('sdsd')
          const newToken = await refreshToken();
          isRefreshingToken = false;
          onTokenRefreshed(newToken);
        } catch (err) {
          isRefreshingToken = false;
          logout();
          return Promise.reject(err);
        }
      }

      return new Promise((resolve) => {
        addRefreshSubscriber((token) => {
          originalRequest.headers.Authorization = `Bearer ${token}`;
          resolve(api(originalRequest));
        });
      }); */
    }

    return Promise.reject(error);
  },
);

// **Main Request Function**
const request = async ({
  method = 'GET',
  urlKey,
  url,
  args = [],
  params,
  data,
  headers = {},
  responseType = 'json',
  autoRefreshExpiredToken = true,
  useCustomErrorMessage = false,
  default: defaultValue = null,
  onSuccess = () => {},
  onFailed = () => {},
  onBoth = () => {},
  extra = {},
}) => {
  try {
    let finalUrl = url || urlConfig[urlKey]?.url;
    if (!finalUrl) throw new Error(`Invalid URL key: ${urlKey}`);
    args.forEach((arg) => (finalUrl = finalUrl.replace('{}', arg)));
    const response = await api.request({
      method,
      url: finalUrl,
      params,
      data,
      responseType,
      headers
    });
    onSuccess(response.data, extra);
    onBoth(true, response.data, extra)
    return response.data;
  } catch (error) {
    console.log(error)
    const status = error.response?.status || 500;
    let errorMessage = ERROR_BY_STATUSES[status] || ERROR_BY_STATUSES.default;

    if (!useCustomErrorMessage && error.response?.data.message) {
      errorMessage = error.response.data;
    }
    const errorObj = { status, data: errorMessage };
    onFailed(errorObj, extra);
    onBoth(false, errorObj, extra);
    return defaultValue;
  }
};

// **Bulk Requests**
const bulk = async (requests, options) => {
  const { returnResultObject = false, resultType = 'object', onSuccess, onFailed, onBoth } = options;

  const results = await Promise.all(requests.map((req) => request({ ...req, returnResultObject: true })));

  const success = !results.some((res) => res.success === false);
  const finalResults = resultType === 'object' ? {} : [];

  results.forEach((result) => {
    const resData = returnResultObject ? result : result.success ? result.result : result.default;
    if (resultType === 'object') finalResults[result.key] = resData;
    else finalResults.push(resData);
  });

  success ? onSuccess?.(finalResults) : onFailed?.(finalResults);
  onBoth?.(success, finalResults);

  return finalResults;
};

request.bulk = bulk;

export default request;
