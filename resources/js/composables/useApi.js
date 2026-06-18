import axios from 'axios';
import { toast as sonner } from 'vue-sonner';

const defaultLocale = import.meta.env.VITE_APP_LOCALE || 'vi';

const axiosInstance = axios.create({
    timeout: 60000,
    headers: {
        'Accept-Language': defaultLocale,
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
    withXSRFToken: true,
});

axiosInstance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            window.location.href = route('login');
        }
        return Promise.reject(error);
    },
);

export const unwrapApiData = (response) => response?.data?.data ?? response?.data ?? null;

export const useApi = (request) => {
    const api = {
        get: (params = {}, signal) => axiosInstance.get(request, { params, signal }),
        delete: (data = {}, signal) => axiosInstance.delete(request, { data, signal }),
        post: (data = {}, signal) => axiosInstance.post(request, data, { signal }),
        put: (data = {}, signal) => axiosInstance.put(request, data, { signal }),
        patch: (data = {}, signal) => axiosInstance.patch(request, data, { signal }),
    };

    const handleError = (err, isShowToast) => {
        if (axios.isCancel(err)) {
            return null;
        }

        if (isShowToast) {
            sonner.error('Thất bại', {
                description:
                    err.response?.data?.message
                    || Object.values(err.response?.data?.errors ?? {})?.[0]?.[0]
                    || 'Có lỗi xảy ra. Vui lòng thử lại!',
            });
        }

        throw err;
    };

    const handleSuccessToast = (response, isShowToast) => {
        if (response?.data?.success && isShowToast && response.data.message) {
            sonner.success('Thành công', {
                description: response.data.message,
            });
        }
    };

    return {
        async get(params = {}, isShowToast = true, signal = null) {
            try {
                return await api.get(params, signal);
            } catch (err) {
                return handleError(err, isShowToast);
            }
        },
        async post(data = {}, isShowToast = true, signal = null) {
            try {
                const response = await api.post(data, signal);
                handleSuccessToast(response, isShowToast);
                return response;
            } catch (err) {
                return handleError(err, isShowToast);
            }
        },
        async put(data = {}, isShowToast = true, signal = null) {
            try {
                const response = await api.put(data, signal);
                handleSuccessToast(response, isShowToast);
                return response;
            } catch (err) {
                return handleError(err, isShowToast);
            }
        },
        async patch(data = {}, isShowToast = true, signal = null) {
            try {
                const response = await api.patch(data, signal);
                handleSuccessToast(response, isShowToast);
                return response;
            } catch (err) {
                return handleError(err, isShowToast);
            }
        },
        async delete(data = {}, isShowToast = true, signal = null) {
            try {
                const response = await api.delete(data, signal);
                handleSuccessToast(response, isShowToast);
                return response;
            } catch (err) {
                return handleError(err, isShowToast);
            }
        },
    };
};
