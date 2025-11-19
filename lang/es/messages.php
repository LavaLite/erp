<?php

return [
    // Authentication
    'auth' => [
        'login_success' => 'Inicio de sesión exitoso',
        'logout_success' => 'Sesión cerrada exitosamente',
        'register_success' => 'Registro exitoso. Por favor verifique su correo electrónico antes de iniciar sesión.',
        'invalid_credentials' => 'Las credenciales proporcionadas son incorrectas.',
        'unauthorized' => 'No autorizado',
        'token_expired' => 'El token ha expirado',
        'token_invalid' => 'Token inválido',
        'email_not_verified' => 'Se requiere verificación de correo electrónico.',
        'email_verification_message' => 'Por favor verifique su correo electrónico antes de iniciar sesión. Revise su bandeja de entrada para el enlace de verificación.',
        'organization_required' => 'Se requiere organización',
        'organization_not_found' => 'Organización no encontrada',
        'switched_organization' => 'Cambiado exitosamente a :name',
        'social_login_error' => 'No se pudo iniciar sesión con :provider. Por favor intente de nuevo.',
        'social_login_success' => '¡Inicio de sesión exitoso con :provider!',
        'no_org_access' => 'No tiene acceso a esta organización.',
        'no_org_context' => 'No se ha establecido el contexto de la organización',
        'org_context_required' => 'Se requiere contexto de organización (encabezado X-Organization-ID)',
    ],

    // Email Verification
    'verification' => [
        'sent' => 'Correo de verificación enviado exitosamente.',
        'resent' => 'Correo de verificación reenviado exitosamente.',
        'already_verified' => 'Correo ya verificado.',
        'verified' => 'Correo verificado exitosamente.',
        'invalid_token' => 'Token de verificación inválido o expirado.',
        'expired' => 'El enlace de verificación es inválido o ha expirado. Por favor solicite un nuevo correo de verificación.',
        'user_not_found' => 'Usuario no encontrado.',
        'rate_limit' => 'Por favor espere :seconds segundos antes de solicitar otro correo de verificación.',
        'invalid_link' => 'Enlace de verificación inválido. Falta el token o el parámetro de correo.',
        'failed_title' => 'Verificación Fallida',
        'already_verified_title' => 'Ya Verificado',
        'verified_title' => 'Correo Verificado',
        'already_verified_message' => 'Su dirección de correo electrónico ya ha sido verificada.',
        'verified_message' => '¡Su dirección de correo electrónico ha sido verificada exitosamente! Ahora puede iniciar sesión en su cuenta.',
    ],

    // Password Reset
    'password' => [
        'reset_sent' => 'Si su correo está registrado, recibirá un enlace para restablecer su contraseña.',
        'reset_success' => 'Contraseña restablecida exitosamente.',
        'invalid_token' => 'Token de restablecimiento inválido o expirado.',
        'same_password' => 'La nueva contraseña debe ser diferente de la actual',
        'current_incorrect' => 'La contraseña actual es incorrecta',
    ],

    // Two-Factor Authentication
    '2fa' => [
        'already_enabled' => 'La autenticación de dos factores ya está habilitada.',
        'enabled' => 'Autenticación de dos factores habilitada exitosamente.',
        'disabled' => 'Autenticación de dos factores deshabilitada exitosamente.',
        'confirmed' => 'Configuración 2FA confirmada',
        'invalid_code' => 'Código de autenticación inválido.',
        'recovery_codes_generated' => 'Códigos de recuperación regenerados exitosamente.',
        'required' => 'Se requiere código de autenticación de dos factores.',
        'scan_qr' => 'Escanee el código QR con su aplicación de autenticación y confirme con un código válido.',
        'not_enabled' => 'La autenticación de dos factores no está habilitada.',
        'not_enabled_user' => 'La autenticación de dos factores no está habilitada para este usuario.',
        'recovery_accepted' => 'Código de recuperación aceptado.',
        'invalid_recovery' => 'Código de recuperación inválido.',
        'code_verified' => 'Código de autenticación verificado.',
        'enable_first' => 'Por favor llame a /2fa/enable primero para generar un secreto.',
    ],

    // User Management
    'user' => [
        'profile_updated' => 'Perfil actualizado exitosamente',
        'avatar_uploaded' => 'Avatar subido exitosamente',
        'avatar_deleted' => 'Avatar eliminado exitosamente',
        'password_changed' => 'Contraseña cambiada exitosamente',
        'preferences_updated' => 'Preferencias actualizadas exitosamente',
        'not_found' => 'Usuario no encontrado',
        'not_member' => 'El usuario no es miembro de esta organización',
    ],

    // Organizations
    'organization' => [
        'created' => 'Organización creada exitosamente',
        'updated' => 'Organización actualizada exitosamente',
        'deleted' => 'Organización eliminada exitosamente',
        'not_found' => 'Organización no encontrada',
        'access_denied' => 'Acceso denegado a esta organización',
        'user_added' => 'Usuario agregado a la organización exitosamente',
        'user_removed' => 'Usuario eliminado de la organización exitosamente',
        'user_limit_reached' => 'Límite de usuarios alcanzado para esta organización',
        'suspended' => 'Organización suspendida exitosamente',
        'activated' => 'Organización activada exitosamente',
        'cancelled' => 'Suscripción de la organización cancelada exitosamente',
        'limits_updated' => 'Límites de la organización actualizados exitosamente',
        'invalid_id' => 'El ID de organización seleccionado es inválido.',
        'invalid_uuid' => 'El ID de organización debe ser un UUID válido o "global".',
    ],

    // Teams
    'team' => [
        'created' => 'Equipo creado exitosamente',
        'updated' => 'Equipo actualizado exitosamente',
        'deleted' => 'Equipo eliminado exitosamente',
        'not_found' => 'Equipo no encontrado',
        'member_added' => 'Miembro agregado exitosamente',
        'member_updated' => 'Rol del miembro actualizado exitosamente',
        'member_removed' => 'Miembro eliminado exitosamente',
        'access_denied' => 'No tiene acceso a este equipo',
        'leader_required' => 'Solo los líderes del equipo pueden realizar esta acción',
        'modules_assigned' => 'Módulos asignados exitosamente',
        'slug_exists' => 'Ya existe un equipo con este slug en su organización',
        'has_subteams' => 'No se puede eliminar un equipo con sub-equipos. Elimine o reasigne los sub-equipos primero.',
        'already_member' => 'El usuario ya es miembro de este equipo',
        'not_member' => 'El usuario no es miembro de este equipo',
        'last_leader' => 'No se puede eliminar al último líder del equipo',
        'demote_last_leader' => 'No se puede degradar al último líder del equipo',
        'only_admin_delete' => 'Solo los administradores de la organización pueden eliminar equipos',
        'only_admin_assign' => 'Solo los administradores de la organización pueden asignar módulos',
    ],

    // Roles & Permissions
    'role' => [
        'created' => 'Rol creado exitosamente',
        'updated' => 'Rol actualizado exitosamente',
        'deleted' => 'Rol eliminado exitosamente',
        'not_found' => 'Rol no encontrado',
        'not_found_in_org' => 'Rol no encontrado en esta organización',
        'assigned' => 'Rol asignado exitosamente',
        'removed' => 'Rol eliminado exitosamente',
        'permission_denied' => 'Permiso denegado',
        'slug_exists' => 'Ya existe un rol con este slug para este inquilino',
        'global_only' => 'Solo los administradores globales pueden crear o actualizar roles globales',
        'admin_only' => 'Solo los administradores de la organización pueden crear roles en su organización',
        'superadmin_only' => 'Solo los super administradores pueden asignar el rol de superadmin',
        'global_assign_only' => 'Solo los administradores globales pueden asignar roles globales',
        'not_belong' => 'El rol no pertenece a este inquilino',
        'permission_assigned' => 'Permiso asignado al rol exitosamente',
        'permission_removed' => 'Permiso eliminado del rol exitosamente',
        'modules_assigned' => 'Módulos asignados al rol exitosamente',
        'module_added' => "Módulo ':module' agregado al rol ':role'",
        'module_removed' => "Módulo ':module' eliminado del rol ':role'",
        'manage_modules_denied' => 'No autorizado. Solo los propietarios y administradores pueden gestionar módulos de roles.',
        'view_modules_denied' => 'No autorizado. Solo los propietarios y administradores pueden ver módulos de roles.',
    ],

    'permission' => [
        'created' => 'Permiso creado exitosamente',
        'deleted' => 'Permiso eliminado exitosamente',
        'not_found' => 'Permiso no encontrado',
        'slug_exists' => 'Ya existe un permiso con este slug para este inquilino',
        'global_only' => 'Solo los administradores globales pueden crear o actualizar permisos globales',
        'admin_only' => 'Solo los administradores de la organización pueden crear permisos en su organización',
        'assigned' => 'Permiso asignado al usuario exitosamente',
        'removed' => 'Permiso eliminado del usuario exitosamente',
    ],

    // Modules
    'module' => [
        'enabled' => 'Módulo habilitado para la organización exitosamente',
        'enabled_plural' => 'Módulos habilitados para la organización exitosamente',
        'disabled' => 'Módulo deshabilitado para la organización exitosamente',
        'updated' => 'Configuración del módulo actualizada',
        'deleted' => 'Módulo eliminado exitosamente',
        'not_found' => 'Módulo no encontrado',
        'access_denied' => 'No tiene acceso a este módulo',
        'expired' => 'El acceso al módulo ha expirado',
        'not_enabled' => 'El módulo no está habilitado para esta organización',
        'some_not_enabled' => 'Algunos módulos no están habilitados para esta organización',
    ],

    // Billing
    'billing' => [
        'subscription_updated' => 'Suscripción actualizada exitosamente',
        'limit_updated' => 'Límite de usuarios actualizado exitosamente',
        'organization_suspended' => 'Organización suspendida',
        'organization_reactivated' => 'Organización reactivada',
        'subscription_id_required' => 'Se requiere ID de suscripción para la activación',
    ],

    // Validation
    'validation' => [
        'required' => 'El campo :attribute es requerido',
        'email' => 'Por favor proporcione una dirección de correo válida',
        'min' => 'El :attribute debe tener al menos :min caracteres',
        'max' => 'El :attribute no debe exceder :max caracteres',
        'unique' => 'Este :attribute ya está en uso',
        'confirmed' => 'La confirmación del :attribute no coincide',
    ],

    // Errors
    'error' => [
        'server_error' => 'Error interno del servidor',
        'not_found' => 'Recurso no encontrado',
        'validation_failed' => 'Falló la validación',
        'rate_limit_exceeded' => 'Demasiadas solicitudes. Por favor intente más tarde',
    ],

    // Success
    'success' => [
        'operation_completed' => 'Operación completada exitosamente',
    ],
];
