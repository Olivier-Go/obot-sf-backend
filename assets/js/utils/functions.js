export const isEmptyObj = (obj) => (
    Object.keys(obj).length === 0
);

/**
 * Convert Form Data to JSON String
 * @param form
 * @returns {string}
 */
export const serializeForm = (form) => {
    const formData = new FormData(form).entries();
    const json = Object.assign(...Array.from(formData, ([key,value]) => {
        const parsedKey = key.split('[').pop().split(']')[0];
        return {[parsedKey]: value};
    }));
    return JSON.stringify(json);
}

/**
 * Serialize and Submit Form Data in JSON
 * @param form
 * @returns {Promise<null|any>}
 */
export const jsonSubmitFormData = async (form) => {
    const data = serializeForm(form);
    const response = await fetch(form.action, {
        method: form.method,
        body: data
    })
    if (response.ok) {
        return await response.json();
    }
    return null;
}

/**
 * Validate email field
 * @param {String} email
 * @return Bool true/false if valid email
 */
export const validateEmail = (email) => (
    /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)
);

/**
 * Validate Password fields
 * @param {String} password
 * @return Bool true/false if valid password with conditions :
 * - Min eight characters
 * - at least one lowercase letter
 * - at least one number
 */
export const validatePassword = (password) => (
    /^(?=.*\d)(?=.*[a-z])[0-9a-z]{8,}$/.test(password)
);
