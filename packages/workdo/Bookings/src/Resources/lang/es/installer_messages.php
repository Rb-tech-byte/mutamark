<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'Instalador de Laravel',
    'next' => 'Siguiente Paso',
    'back' => 'Anterior',
    'finish' => 'Instalar',
    'forms' => [
        'errorTitle' => 'Se produjeron los siguientes errores:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'templateTitle' => 'Bienvenido',
        'title'   => 'Instalador de Laravel',
        'message' => 'Asistente de instalación y configuración fácil.',
        'next'    => 'Verificar Requisitos',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'templateTitle' => 'Paso 1 | Requisitos del Servidor',
        'title' => 'Requisitos del Servidor',
        'next'    => 'Verificar Permisos',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'templateTitle' => 'Paso 2 | Permisos',
        'title' => 'Permisos',
        'next' => 'Configurar Entorno',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'menu' => [
            'templateTitle' => 'Paso 3 | Configuración del Entorno',
            'title' => 'Configuración del Entorno',
            'desc' => 'Por favor, selecciona cómo deseas configurar el archivo <code>.env</code> de la aplicación.',
            'wizard-button' => 'Configuración con Asistente',
            'classic-button' => 'Editor de Texto Clásico',
        ],
        'wizard' => [
            'templateTitle' => 'Paso 3 | Configuración del Entorno | Asistente Guiado',
            'title' => 'Asistente Guiado para <code>.env</code>',
            'tabs' => [
                'environment' => 'Entorno',
                'database' => 'Base de Datos',
                'application' => 'Aplicación',
            ],
            'form' => [
                'name_required' => 'Se requiere un nombre para el entorno.',
                'app_name_label' => 'Nombre de la Aplicación',
                'app_name_placeholder' => 'Nombre de la Aplicación',
                'app_environment_label' => 'Entorno de la Aplicación',
                'app_environment_label_local' => 'Local',
                'app_environment_label_developement' => 'Desarrollo',
                'app_environment_label_qa' => 'QA',
                'app_environment_label_production' => 'Producción',
                'app_environment_label_other' => 'Otro',
                'app_environment_placeholder_other' => 'Introduce tu entorno...',
                'app_debug_label' => 'Depuración de la Aplicación',
                'app_debug_label_true' => 'Verdadero',
                'app_debug_label_false' => 'Falso',
                'app_log_level_label' => 'Nivel de Registro de la Aplicación',
                'app_log_level_label_debug' => 'depuración',
                'app_log_level_label_info' => 'información',
                'app_log_level_label_notice' => 'aviso',
                'app_log_level_label_warning' => 'advertencia',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'crítico',
                'app_log_level_label_alert' => 'alerta',
                'app_log_level_label_emergency' => 'emergencia',
                'app_url_label' => 'URL de la Aplicación',
                'app_url_placeholder' => 'URL de la Aplicación',
                'db_connection_failed' => 'No se pudo conectar a la base de datos.',
                'db_connection_label' => 'Conexión a la Base de Datos',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Host de la Base de Datos',
                'db_host_placeholder' => 'Host de la Base de Datos',
                'db_port_label' => 'Puerto de la Base de Datos',
                'db_port_placeholder' => 'Puerto de la Base de Datos',
                'db_name_label' => 'Nombre de la Base de Datos',
                'db_name_placeholder' => 'Nombre de la Base de Datos',
                'db_username_label' => 'Nombre de Usuario de la Base de Datos',
                'db_username_placeholder' => 'Nombre de Usuario de la Base de Datos',
                'db_password_label' => 'Contraseña de la Base de Datos',
                'db_password_placeholder' => 'Contraseña de la Base de Datos',

                'app_tabs' => [
                    'more_info' => 'Más Información',
                    'broadcasting_title' => 'Transmisión, Caché, Sesión y Cola',
                    'broadcasting_label' => 'Controlador de Transmisión',
                    'broadcasting_placeholder' => 'Controlador de Transmisión',
                    'cache_label' => 'Controlador de Caché',
                    'cache_placeholder' => 'Controlador de Caché',
                    'session_label' => 'Controlador de Sesión',
                    'session_placeholder' => 'Controlador de Sesión',
                    'queue_label' => 'Controlador de Cola',
                    'queue_placeholder' => 'Controlador de Cola',
                    'redis_label' => 'Controlador de Redis',
                    'redis_host' => 'Host de Redis',
                    'redis_password' => 'Contraseña de Redis',
                    'redis_port' => 'Puerto de Redis',

                    'mail_label' => 'Correo',
                    'mail_driver_label' => 'Controlador de Correo',
                    'mail_driver_placeholder' => 'Controlador de Correo',
                    'mail_host_label' => 'Host de Correo',
                    'mail_host_placeholder' => 'Host de Correo',
                    'mail_port_label' => 'Puerto de Correo',
                    'mail_port_placeholder' => 'Puerto de Correo',
                    'mail_username_label' => 'Usuario de Correo',
                    'mail_username_placeholder' => 'Usuario de Correo',
                    'mail_password_label' => 'Contraseña de Correo',
                    'mail_password_placeholder' => 'Contraseña de Correo',
                    'mail_encryption_label' => 'Cifrado de Correo',
                    'mail_encryption_placeholder' => 'Cifrado de Correo',

                    'pusher_label' => 'Pusher',
                    'pusher_app_id_label' => 'ID de la Aplicación Pusher',
                    'pusher_app_id_palceholder' => 'ID de la Aplicación Pusher',
                    'pusher_app_key_label' => 'Clave de la Aplicación Pusher',
                    'pusher_app_key_palceholder' => 'Clave de la Aplicación Pusher',
                    'pusher_app_secret_label' => 'Secreto de la Aplicación Pusher',
                    'pusher_app_secret_palceholder' => 'Secreto de la Aplicación Pusher',
                ],
                'buttons' => [
                    'setup_database' => 'Configurar Base de Datos',
                    'setup_application' => 'Configurar Aplicación',
                    'install' => 'Instalar',
                ],
            ],
        ],
        'classic' => [
            'templateTitle' => 'Paso 3 | Configuración del Entorno | Editor Clásico',
            'title' => 'Editor Clásico de Entorno',
            'save' => 'Guardar .env',
            'back' => 'Usar Asistente',
            'install' => 'Guardar e Instalar',
        ],
        'success' => 'La configuración del archivo .env se ha guardado correctamente.',
        'errors' => 'No se pudo guardar el archivo .env, por favor créalo manualmente.',
    ],

    'install' => 'Instalar',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Instalador de Laravel instalado correctamente el ',
    ],

    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Instalación Finalizada',
        'templateTitle' => 'Instalación Finalizada',
        'finished' => 'La aplicación se ha instalado correctamente.',
        'migration' => 'Salida de la Consola de Migración y Seed:',
        'console' => 'Salida de la Consola de la Aplicación:',
        'log' => 'Entrada del Registro de Instalación:',
        'env' => 'Archivo .env Final:',
        'exit' => 'Haz clic aquí para salir',
    ],

    /*
     *
     * Update specific translations
     *
     */
    'updater' => [
        /*
         *
         * Shared translations.
         *
         */
        'title' => 'Actualizador de Laravel',

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'welcome' => [
            'title'   => 'Bienvenido al Actualizador',
            'message' => 'Bienvenido al asistente de actualización.',
        ],

        /*
         *
         * Welcome page translations for update feature.
         *
         */
        'overview' => [
            'title'   => 'Resumen',
            'message' => 'Hay 1 actualización.|Hay :number actualizaciones.',
            'install_updates' => 'Instalar Actualizaciones',
        ],

        /*
         *
         * Final page translations.
         *
         */
        'final' => [
            'title' => 'Finalizado',
            'finished' => 'La base de datos de la aplicación se ha actualizado correctamente.',
            'exit' => 'Haz clic aquí para salir',
        ],

        'log' => [
            'success_message' => 'Instalador de Laravel actualizado correctamente el ',
        ],
    ],
];
