function validarPassword(password) {
    // Mínimo 6 caracteres
    if (password.length < 6) {
        return {
            valido: false,
            mensaje: 'La contraseña debe tener al menos 6 caracteres'
        };
    }

    // Al menos una letra mayúscula
    if (!/[A-Z]/.test(password)) {
        return {
            valido: false,
            mensaje: 'La contraseña debe contener al menos una letra mayúscula'
        };
    }

    // Al menos una letra minúscula
    if (!/[a-z]/.test(password)) {
        return {
            valido: false,
            mensaje: 'La contraseña debe contener al menos una letra minúscula'
        };
    }

    // Al menos un número
    if (!/[0-9]/.test(password)) {
        return {
            valido: false,
            mensaje: 'La contraseña debe contener al menos un número'
        };
    }

    // No debe contener símbolos
    if (/[^a-zA-Z0-9]/.test(password)) {
        return {
            valido: false,
            mensaje: 'La contraseña no debe contener símbolos especiales'
        };
    }

    return {
        valido: true,
        mensaje: 'Contraseña válida'
    };
} 