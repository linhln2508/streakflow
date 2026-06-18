import { validationMessages } from '@/lang/validation';

const defaultLocale = import.meta.env.VITE_APP_LOCALE || 'vi';

const getErrorMessage = (key, param = null) => {
    const messages = validationMessages[defaultLocale] || validationMessages.en;
    let message = messages[key] || validationMessages.en[key];

    if (param !== null && message) {
        message = message.replace('{param}', param);
    }

    return message;
};

const rules = {
    required: (value) => (value !== null && value !== '' && value !== undefined ? true : getErrorMessage('required')),
    string: (value) => (value === null || value === undefined || typeof value === 'string' ? true : getErrorMessage('string')),
    max: (value, _field, param) => {
        const maxLength = parseInt(param);
        return !value || String(value).length <= maxLength ? true : getErrorMessage('max', maxLength);
    },
    min: (value, _field, param) => {
        const minLength = parseInt(param);
        return value && String(value).length >= minLength ? true : getErrorMessage('min', minLength);
    },
    email: (value) => {
        if (!value) return true;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? true : getErrorMessage('email');
    },
    numeric: (value) => (!value || (!isNaN(parseFloat(value)) && isFinite(value)) ? true : getErrorMessage('numeric')),
    integer: (value) => (!value || Number.isInteger(Number(value)) ? true : getErrorMessage('integer')),
    url: (value) => {
        if (!value) return true;
        try {
            new URL(value);
            return true;
        } catch {
            return getErrorMessage('url');
        }
    },
    date: (value) => (!value || !isNaN(Date.parse(value)) ? true : getErrorMessage('date')),
    regex: (value, _field, param) => {
        if (!value) return true;
        return new RegExp(param).test(value) ? true : getErrorMessage('regex');
    },
};

export const validate = (value, field) => {
    if (!field?.validate) {
        return { isValid: true, errors: {} };
    }

    const ruleList = field.validate.split('|');
    let isValid = true;
    const validationErrors = {};
    const isNullable = ruleList.includes('nullable') || !ruleList.includes('required');

    ruleList.forEach((rule) => {
        const [ruleName, ruleParam] = rule.split(':');

        if (!rules[ruleName]) {
            return;
        }

        if (
            ruleName !== 'required'
            && (value === null || value === '' || value === undefined)
            && isNullable
        ) {
            return;
        }

        const result = rules[ruleName](value, field, ruleParam);
        if (result !== true) {
            validationErrors[ruleName] = result;
            isValid = false;
        }
    });

    return { isValid, errors: validationErrors };
};

export const validateFields = (fields, values, fieldRefs = []) => {
    const refResults = fieldRefs.map((ref) => ref?.validate?.() ?? true);
    const allValid = refResults.every(Boolean);

    return {
        isValid: allValid,
        message: allValid ? null : getErrorMessage('formInvalid'),
    };
};
