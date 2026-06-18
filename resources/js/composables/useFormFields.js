import { validationMessages } from '@/lang/validation';

const defaultLocale = import.meta.env.VITE_APP_LOCALE || 'vi';

export const useFormFields = () => {
    const validateAll = (fieldRefs = []) => {
        const results = fieldRefs.filter(Boolean).map((ref) => ref.validate());
        const isValid = results.every(Boolean);
        const messages = validationMessages[defaultLocale] || validationMessages.en;

        return {
            isValid,
            message: isValid ? null : messages.formInvalid,
        };
    };

    return { validateAll };
};
