const i18n = {
    es: {
        // Nav
        'nav.inicio': 'Inicio',
        'nav.tickets': 'Tickets',
        'nav.ayuda': 'Ayuda',
        'nav.inventario': 'Inventario',
        'nav.mi_cuenta': 'Mi cuenta',
        'nav.configuracion': 'Configuración',
        'nav.cerrar_sesion': 'Cerrar sesión',
        'nav.administracion': 'Administración',
        'nav.informes': 'Informes',
        'nav.iniciar_sesion': 'Iniciar sesión',
        'nav.crear_cuenta': 'Crear cuenta',
        'nav.idioma': 'Cambiar idioma',
        'nav.tema': 'Cambiar tema',

        // Footer
        'footer.copyright': '© 2026 Kinetix. Todos los derechos reservados.',
        'footer.software': 'Software para avanzar.',

        // Inicio
        'inicio.title': 'Kinetix - Panel de Cliente',
        'inicio.eyebrow': 'KINETIX · TECNOLOGÍA QUE ACOMPAÑA',
        'inicio.h1_cliente': 'Hola, {nombre}.',
        'inicio.lead_cliente': 'Todo lo que necesitás para seguir tu proyecto, resolver consultas y avanzar con claridad.',
        'inicio.h1_admin': 'Ideas en marcha. Gestión en control.',
        'inicio.lead_admin': 'Administrá Kinetix y encontrá en un mismo lugar las herramientas para mantener todo en movimiento.',
        'inicio.h1_invitado': 'La tecnología también puede sentirse simple.',
        'inicio.lead_invitado': 'Conectamos tus proyectos con las personas, los recursos y el soporte que necesitás para avanzar.',
        'inicio.btn_tickets': 'Ir a mis tickets →',
        'inicio.btn_admin': 'Abrir administración →',
        'inicio.btn_registrarse': 'Empezar ahora →',
        'inicio.btn_ayuda': 'Conocer más →',
        'inicio.cta_registrarse': 'Crear mi cuenta',
        'inicio.overview.eyebrow': 'UNA PLATAFORMA PARA EL DÍA A DÍA',
        'inicio.overview.h1': 'Gestioná tus activos y el soporte <span>desde un mismo lugar.</span>',
        'inicio.overview.p': 'Kinetix reúne herramientas para consultar los recursos tecnológicos, pedir asistencia y mantener cada solicitud organizada. Así, usuarios y equipo técnico pueden dar seguimiento a las necesidades del trabajo diario.',
        'inicio.process.eyebrow': 'UN FLUJO CLARO',
        'inicio.process.h1': 'De la necesidad a la solución.',
        'inicio.process.p': 'Las herramientas de Kinetix ayudan a que cada pedido tenga contexto y un lugar donde continuar.',
        'inicio.process.step1.h3': 'Encontrá el equipo',
        'inicio.process.step1.p': 'Buscá el activo en el inventario y revisá su información disponible.',
        'inicio.process.step2.h3': 'Contanos qué necesitás',
        'inicio.process.step2.p': 'Creá un ticket o iniciá una solicitud de servicio con los detalles del caso.',
        'inicio.process.step3.h3': 'Seguí el avance',
        'inicio.process.step3.p': 'Consultá el estado y el historial para saber cómo continúa la atención.',
        'inicio.links.eyebrow': 'ACCESOS DIRECTOS',
        'inicio.links.h1': '¿A dónde vamos?',
        'inicio.links.note': 'Tu espacio de trabajo',
        'inicio.card.inventario.h3': 'Inventario',
        'inicio.card.inventario.p': 'Consultá los recursos vinculados a tu proyecto.',
        'inicio.card.tickets.h3': 'Soporte técnico',
        'inicio.card.tickets.p': 'Seguí tus consultas y conversá con el equipo.',
        'inicio.card.ayuda.h3': 'Centro de ayuda',
        'inicio.card.ayuda.p': 'Encontrá respuestas a preguntas frecuentes.',
        'inicio.card.cuenta.h3': 'Mi cuenta',
        'inicio.card.cuenta.p': 'Revisá los datos de tu perfil.',
        'inicio.card.admin.h3': 'Administración',
        'inicio.card.admin.p': 'Gestioná tablas y registros de la plataforma.',
        'inicio.card.informes.h3': 'Informes',
        'inicio.card.informes.p': 'Consultá las métricas y el resumen del sistema.',
        'inicio.note.eyebrow': 'KINETIX SOFTWARE',
        'inicio.note.h2': 'La tecnología funciona mejor cuando todo está conectado.',
        'inicio.note.p': 'Desde el seguimiento del proyecto hasta el soporte diario, encontrá cada herramienta donde la necesitás.',
        'inicio.cta.eyebrow': 'EL PRÓXIMO PASO EMPIEZA ACÁ',
        'inicio.cta.h2': '¿Lo ponemos en marcha?',

        // Login
        'login.title': 'Iniciar sesión | Kinetix',
        'login.h2': 'Acceso a clientes',
        'login.subtitle': 'Iniciá sesión para poder acceder a nuestro servicio.<br><small>(Por favor no uses un gmail real o contraseña personal)</small>',
        'login.label_email': 'Correo electrónico',
        'login.label_password': 'Contraseña',
        'login.btn': 'Iniciar sesión',
        'login.footer': '¿No tenés una cuenta? <a href="registrarse">Registrate aquí</a>',
        'login.err_credenciales': 'Lo que ingreso parece estar mal, intentelo de nuevo.',
        'login.err_inactivo': 'Este usuario fue desactivado por un administrador, intente registrarse de nuevo.',
        'login.err_ya_sesion': 'Usted ya inicio sesion',
        'login.err_error': 'Error al iniciar sesion. Intentalo de nuevo.',

        // Registrarse
        'registrarse.title': 'Registrarse | Kinetix',

        // Usuario
        'usuario.title': 'Mi cuenta | Kinetix',
        'usuario.eyebrow': 'Cuenta personal',
        'usuario.h1': 'Perfil del Usuario #{id}',
        'usuario.p': 'Consultá la información asociada a esta cuenta de Kinetix.',
        'usuario.btn_config': 'Configurar perfil',
        'usuario.datos.eyebrow': 'Información registrada',
        'usuario.datos.h2': 'Datos de la cuenta',
        'usuario.detail.nombre': 'Nombre completo',
        'usuario.detail.email': 'Correo electrónico',
        'usuario.detail.rol': 'Rol',
        'usuario.detail.ubicacion': 'Ubicación de trabajo',
        'usuario.detail.fecha': 'Fecha de registro',
        'usuario.detail.items': 'Cantidad de items en posesión',
        'usuario.profile_heading': 'Perfil de usuario',
        'usuario.user_label': 'Usuario #{id}',
        'usuario.detail.id': 'Número de cuenta',
        'usuario.msg_no_permiso': 'No tienes permiso para ver este perfil.',
        'usuario.msg_no_existe': 'El usuario no existe.',
        'usuario.msg_admin': 'Este usuario es un administrador, no tienes permiso para ver este perfil.',
        'inventory.filters': 'Filtrar resultados',
        'inventory.status': 'Estado',
        'inventory.view_item': 'Ver artículo',

        // Configuración
        'config.title': 'Configuración | Kinetix',
        'config.profile_photo': 'Foto de perfil',
        'config.profile_photo_hint': 'JPG, PNG o WebP; máximo 5 MB.',
        'config.p': 'Gestioná tu información personal y de acceso.',
        'config.tab_cuenta': 'Cuenta',
        'config.tab_ubicacion': 'Ubicación',
        'config.tab_contrasena': 'Contraseña',
        'config.location': 'Ubicación de trabajo',
        'config.location_hint': 'Necesitás una ubicación para solicitar activos.',
        'config.choose_location': 'Seleccioná una ubicación',
        'config.error_location': 'Seleccioná una ubicación válida.',
        'config.error_location_save': 'No se pudo actualizar la ubicación. Inténtalo nuevamente.',
        'config.success_location': 'La ubicación se actualizó correctamente.',
        'config.tab_desactivar': 'Desactivar cuenta',
        'config.p_nombre': 'Este nombre se muestra en tu perfil y en los tickets.',
        'config.first_name': 'Nombre',
        'config.last_name': 'Apellido',
        'config.p_last_name': 'Este apellido se muestra en tu perfil y en los tickets.',
        'config.label_email': 'Correo electrónico',
        'config.p_email': 'Usado para iniciar sesión y recibir notificaciones.',
        'config.label_rol': 'Rol de cuenta',
        'config.p_rol': 'Rol asignado a tu cuenta.',
        'config.current_password': 'Contraseña actual',
        'config.password_placeholder': 'Nueva contraseña',
        'config.password_confirm': 'Confirmar contraseña',
        'config.btn_guardar': 'Guardar cambios',
        'config.modal.titulo': '¿Estás seguro?',
        'config.modal.p': 'Al desactivar tu cuenta:',
        'config.modal.item1': 'No podrás iniciar sesión ni acceder a tus tickets.',
        'config.modal.item2': 'Tu información personal quedará inactiva.',
        'config.modal.item3': 'Esta acción no se puede deshacer.',
        'config.modal.cancelar': 'Cancelar',
        'config.modal.aceptar': 'Aceptar',
        'config.no_assets': 'Tus activos quedarán sin dueño.',
        'inventory.filter_category': 'Filtrar por categoría',
        'inventory.filter_state': 'Filtrar por estado',
        'config.error_data': 'Ingresa nombre, apellido y un correo electrónico válido.',
        'config.error_email_taken': 'Ese correo electrónico ya está asociado a otra cuenta.',
        'config.error_save': 'No se pudieron guardar los cambios. Inténtalo nuevamente.',
        'config.success_account': 'Los datos de tu cuenta se actualizaron correctamente.',
        'config.error_password_current': 'La contraseña actual no es correcta.',
        'config.error_password_length': 'La nueva contraseña debe tener al menos 6 caracteres.',
        'config.error_password_match': 'La confirmación no coincide con la nueva contraseña.',
        'config.success_password': 'La contraseña se actualizó correctamente.',
        'config.error_password_save': 'No se pudo actualizar la contraseña. Inténtalo nuevamente.',
        'config.error_deactivate': 'No se pudo desactivar la cuenta. Inténtalo nuevamente o contacta a un administrador.',
        'inventory.no_items': 'No se encontraron activos.',

        // Tickets
        'tickets.title': 'Tickets | Kinetix',
        'tickets.filter_category': 'Categoría',
        'tickets.filter_status': 'Estado',
        'tickets.all_statuses': 'Todos los estados',
        'tickets.filter_age': 'Antigüedad',
        'tickets.newest_first': 'Más recientes primero',
        'tickets.oldest_first': 'Más antiguos primero',
        'tickets.state.open': 'Abiertos',
        'tickets.state.reviewed': 'Revisados',
        'tickets.state.closed': 'Cerrados',
        'tickets.state.resolved': 'Solucionados',
        'tickets.state.cancelled': 'Cancelados',
        'tickets.admin.requester': 'Solicitante:',
        'tickets.admin.assigned_tech': 'Técnico asignado:',
        'tickets.admin.unassigned': 'Sin técnico asignado',
        'tickets.admin.unassign': 'Quitar técnico asignado',

        // Inventario
        'inventario.title': 'Inventario | Kinetix',

        // Preguntas frecuentes
        'faq.title': 'Preguntas frecuentes | Kinetix',

        // Alertas
        'toast.error': 'error',
        'toast.success': 'success',
    },
    en: {
        // Nav
        'nav.inicio': 'Home',
        'nav.tickets': 'Tickets',
        'nav.ayuda': 'Help',
        'nav.inventario': 'Inventory',
        'nav.mi_cuenta': 'My account',
        'nav.configuracion': 'Settings',
        'nav.cerrar_sesion': 'Log out',
        'nav.administracion': 'Administration',
        'nav.informes': 'Reports',
        'nav.iniciar_sesion': 'Log in',
        'nav.crear_cuenta': 'Create account',
        'nav.idioma': 'Change language',
        'nav.tema': 'Change theme',

        // Footer
        'footer.copyright': '© 2026 Kinetix. All rights reserved.',
        'footer.software': 'Software to move forward.',

        // Inicio
        'inicio.title': 'Kinetix - Client Panel',
        'inicio.eyebrow': 'KINETIX · TECHNOLOGY THAT ACCOMPANIES',
        'inicio.h1_cliente': 'Hello, {nombre}.',
        'inicio.lead_cliente': 'Everything you need to follow your project, resolve inquiries and move forward with clarity.',
        'inicio.h1_admin': 'Ideas in motion. Management under control.',
        'inicio.lead_admin': 'Manage Kinetix and find in one place the tools to keep everything running.',
        'inicio.h1_invitado': 'Technology can also feel simple.',
        'inicio.lead_invitado': 'We connect your projects with the people, resources and support you need to move forward.',
        'inicio.btn_tickets': 'Go to my tickets →',
        'inicio.btn_admin': 'Open administration →',
        'inicio.btn_registrarse': 'Get started now →',
        'inicio.btn_ayuda': 'Learn more →',
        'inicio.cta_registrarse': 'Create my account',
        'inicio.overview.eyebrow': 'A PLATFORM FOR DAY TO DAY',
        'inicio.overview.h1': 'Manage your assets and support <span>from the same place.</span>',
        'inicio.overview.p': 'Kinetix brings together tools to consult technological resources, request assistance and keep every request organized. Thus, users and technical staff can track the needs of daily work.',
        'inicio.process.eyebrow': 'A CLEAR FLOW',
        'inicio.process.h1': 'From need to solution.',
        'inicio.process.p': 'Kinetix tools help each request have context and a place to continue.',
        'inicio.process.step1.h3': 'Find the equipment',
        'inicio.process.step1.p': 'Search the asset in the inventory and review its available information.',
        'inicio.process.step2.h3': 'Tell us what you need',
        'inicio.process.step2.p': 'Create a ticket or start a service request with the case details.',
        'inicio.process.step3.h3': 'Follow the progress',
        'inicio.process.step3.p': 'Check the status and history to know how the service continues.',
        'inicio.links.eyebrow': 'DIRECT ACCESS',
        'inicio.links.h1': 'Where are we going?',
        'inicio.links.note': 'Your workspace',
        'inicio.card.inventario.h3': 'Inventory',
        'inicio.card.inventario.p': 'Consult the resources linked to your project.',
        'inicio.card.tickets.h3': 'Technical support',
        'inicio.card.tickets.p': 'Follow your inquiries and talk to the team.',
        'inicio.card.ayuda.h3': 'Help center',
        'inicio.card.ayuda.p': 'Find answers to frequently asked questions.',
        'inicio.card.cuenta.h3': 'My account',
        'inicio.card.cuenta.p': 'Review your profile data.',
        'inicio.card.admin.h3': 'Administration',
        'inicio.card.admin.p': 'Manage tables and records of the platform.',
        'inicio.card.informes.h3': 'Reports',
        'inicio.card.informes.p': 'Check the metrics and system summary.',
        'inicio.note.eyebrow': 'KINETIX SOFTWARE',
        'inicio.note.h2': 'Technology works better when everything is connected.',
        'inicio.note.p': 'From project tracking to daily support, find every tool where you need it.',
        'inicio.cta.eyebrow': 'THE NEXT STEP STARTS HERE',
        'inicio.cta.h2': 'Shall we get it started?',

        // Login
        'login.title': 'Log in | Kinetix',
        'login.h2': 'Client access',
        'login.subtitle': 'Log in to access our service.<br><small>(Please do not use a real gmail or personal password)</small>',
        'login.label_email': 'Email',
        'login.placeholder_email': 'name@example.com',
        'login.label_password': 'Password',
        'login.btn': 'Log in',
        'login.footer': "Don't have an account? <a href='registrarse'>Register here</a>",
        'login.err_credenciales': 'What you entered seems wrong, try again.',
        'login.err_inactivo': 'This user was deactivated by an administrator, try registering again.',
        'login.err_ya_sesion': 'You already started a session',
        'login.err_error': 'Error starting session. Try again.',

        // Usuario
        'usuario.title': 'My account | Kinetix',
        'usuario.eyebrow': 'Personal account',
        'usuario.h1': 'User Profile #{id}',
        'usuario.p': 'Check the information associated with this Kinetix account.',
        'usuario.btn_config': 'Configure profile',
        'usuario.datos.eyebrow': 'Registered information',
        'usuario.datos.h2': 'Account details',
        'usuario.detail.nombre': 'Full name',
        'usuario.detail.email': 'Email',
        'usuario.detail.rol': 'Role',
        'usuario.detail.ubicacion': 'Work location',
        'usuario.detail.fecha': 'Registration date',
        'usuario.detail.items': 'Items in possession',
        'usuario.profile_heading': 'User profile',
        'usuario.user_label': 'User #{id}',
        'usuario.detail.id': 'Account number',
        'usuario.msg_no_permiso': 'You do not have permission to view this profile.',
        'usuario.msg_no_existe': 'The user does not exist.',
        'usuario.msg_admin': 'This user is an administrator, you do not have permission to view this profile.',

        // Configuración
        'config.title': 'Settings | Kinetix',
        'config.profile_photo': 'Profile photo',
        'config.profile_photo_hint': 'JPG, PNG or WebP; 5 MB maximum.',
        'config.p': 'Manage your personal and access information.',
        'config.tab_cuenta': 'Account',
        'config.tab_ubicacion': 'Location',
        'config.tab_contrasena': 'Password',
        'config.location': 'Work location',
        'config.location_hint': 'You need a location to request assets.',
        'config.choose_location': 'Select a location',
        'config.error_location': 'Select a valid location.',
        'config.error_location_save': 'Could not update your location. Please try again.',
        'config.success_location': 'Your location was updated successfully.',
        'config.tab_desactivar': 'Deactivate account',
        'config.p_nombre': 'This name is shown in your profile and in tickets.',
        'config.first_name': 'First name',
        'config.last_name': 'Last name',
        'config.p_last_name': 'This last name is shown in your profile and tickets.',
        'config.label_email': 'Email',
        'config.p_email': 'Used to log in and receive notifications.',
        'config.label_rol': 'Account role',
        'config.p_rol': 'Role assigned to your account.',
        'config.current_password': 'Current password',
        'config.password_placeholder': 'New password',
        'config.password_confirm': 'Confirm password',
        'config.btn_guardar': 'Save changes',
        'config.modal.titulo': 'Are you sure?',
        'config.modal.p': 'When deactivating your account:',
        'config.modal.item1': 'You will not be able to log in or access your tickets.',
        'config.modal.item2': 'Your personal information will be inactive.',
        'config.modal.item3': 'This action cannot be undone.',
        'config.modal.cancelar': 'Cancel',
        'config.modal.aceptar': 'Accept',
        'config.error_data': 'Enter your first name, last name, and a valid email address.',
        'config.error_email_taken': 'That email address is already associated with another account.',
        'config.error_save': 'Could not save your changes. Please try again.',
        'config.success_account': 'Your account details were updated successfully.',
        'config.error_password_current': 'The current password is incorrect.',
        'config.error_password_length': 'The new password must be at least 6 characters long.',
        'config.error_password_match': 'The confirmation does not match the new password.',
        'config.success_password': 'Your password was updated successfully.',
        'config.error_password_save': 'Could not update your password. Please try again.',
        'config.error_deactivate': 'Could not deactivate your account. Please try again or contact an administrator.',
        'config.no_assets': 'Your assets will be unassigned.',
        'inventory.no_items': 'No assets found.',

        // Tickets
        'tickets.title': 'Tickets | Kinetix',

        // Inventario
        'inventario.title': 'Inventory | Kinetix',

        // Preguntas frecuentes
        'faq.title': 'Frequently asked questions | Kinetix',

        // Alertas
        'toast.error': 'error',
        'toast.success': 'success',

        'common.back': 'Back',
        'common.register': 'Register',
        'common.show_password': 'Show password',
        'common.hide_password': 'Hide password',
        'register.title': 'Create account | Kinetix',
        'registrarse.title': 'Create account | Kinetix',
        'ticket_create.heading': 'Create ticket',
        'ticket_create.subtitle': 'Complete the form to send a request to the support team.',
        'ticket_create.subject': 'Subject *',
        'ticket_create.subject_placeholder': 'E.g.: I cannot log in',
        'ticket_create.type': 'Ticket type',
        'ticket_create.priority': 'Priority *',
        'ticket_create.description': 'Description *',
        'ticket_create.description_placeholder': 'Describe the problem in detail...',
        'ticket_create.cancel': 'Cancel',
        'ticket_create.submit': 'Create ticket',
        'faq.heading': 'Frequently asked questions',
        'faq.subtitle': 'Helpful information for managing assets, tickets, and service requests across locations.',
        'faq.search_placeholder': 'Type your question here...',
        'faq.search_label': 'Search frequently asked questions',
        'faq.no_results': 'No matching results were found. Consider opening a ticket to talk to a technician.',
        'faq.still_questions': 'Still have questions?',
        'faq.support_text': 'If you could not find the answer, create a ticket with the details and affected location so the support team can help you.',
        'faq.contact_support': 'Contact support',
        'inventory.heading': 'Inventory',
        'inventory.description': 'Select an item to view its information.',
        'inventory.add_item': 'Add item',
        'inventory.my_items': 'View my items',
        'inventory.all_items': 'View all inventory',
        'inventory.search_placeholder': 'Search items',
        'inventory.search_label': 'Search items',
        'inventory.all_categories': 'All categories',
        'inventory.all_states': 'All statuses',
        'inventory.filter_category': 'Filter by category',
        'inventory.filter_state': 'Filter by status',
        'inventory.category': 'Category:',
        'inventory.filters': 'Filter results',
        'inventory.status': 'Status',
        'inventory.view_item': 'View item',
        'tickets.requests': 'Requests',
        'tickets.request_type': 'Request type',
        'tickets.filter_category': 'Category',
        'tickets.filter_status': 'Status',
        'tickets.all_statuses': 'All statuses',
        'tickets.filter_age': 'Age',
        'tickets.newest_first': 'Newest first',
        'tickets.oldest_first': 'Oldest first',
        'tickets.state.open': 'Open',
        'tickets.state.reviewed': 'Reviewed',
        'tickets.state.closed': 'Closed',
        'tickets.state.resolved': 'Resolved',
        'tickets.state.cancelled': 'Cancelled',
        'tickets.admin.requester': 'Requester:',
        'tickets.admin.assigned_tech': 'Assigned technician:',
        'tickets.admin.unassigned': 'No technician assigned',
        'tickets.admin.unassign': 'Remove assigned technician',
        'tickets.all': 'All',
        'tickets.cancel': 'Cancel',
        'tickets.request': 'Service request',
        'tickets.select_request': 'Select a request',
        'tickets.select_ticket': 'Select a ticket',
        'tickets.requester': 'Requester',
        'tickets.asset': 'Asset',
        'tickets.service_type': 'Service type',
        'tickets.priority': 'Priority',
        'tickets.status': 'Status',
        'tickets.location': 'Location',
        'tickets.created': 'Created',
        'tickets.request_history': 'Request history',
        'tickets.select_request_from_list': 'Select a request from the list.',
        'tickets.accept_review': 'Accept and review',
        'tickets.mark_resolved': 'Mark as resolved',
        'tickets.close_request': 'Close request',
        'tickets.delete': 'Delete',
        'tickets.resolution': 'Ticket resolution',
        'tickets.closed_placeholder': 'This ticket is closed.',
        'tickets.solution_placeholder': 'Write the solution that was applied...',
        'tickets.close_save_resolution': 'Close ticket and save resolution',
        'tickets.change_category': 'Change category',
        'tickets.change_priority': 'Change priority',
        'tickets.change_status': 'Change status',
        'tickets.message_placeholder': 'Write a message...',
        'tickets.send': 'Send',
        'tickets.asset_history': 'Asset history',
        'tickets.ticket_history': 'Ticket history',
        'tickets.no_selection': 'Nothing selected',
        'tickets.history_empty': 'The selected history will appear here.',
        'tickets.ticket_category': 'Category:',
        'tickets.asset_history_empty': 'The selected asset history will appear here.',
        'tickets.ticket_history_empty': 'The selected ticket history will appear here.',
        'tickets.undefined_location': 'Not specified',
        'usuario.detail.phone': 'Phone',
        'inicio.float.connect': 'Connect your ideas',
        'inicio.float.advance': 'Make them happen',
        'inicio.overview.inventory.h3': 'Information about your assets',
        'inicio.overview.inventory.p': 'View registered equipment, including its category, brand, and status. Depending on your account access, you can also review assets assigned to you.',
        'inicio.overview.inventory.link': 'Explore inventory →',
        'inicio.overview.support.h3': 'Support requests you can track',
        'inicio.overview.support.p': 'Create tickets with a description, category, and priority. Track their status, review their history, and stay in touch with the technical team.',
        'inicio.overview.support.link_user': 'Go to support →',
        'inicio.overview.support.link_guest': 'Visit the help center →',
        'inicio.overview.services': 'SERVICES',
        'inicio.overview.services.h3': 'Support for every device',
        'inicio.overview.services.p': 'When an asset needs attention, request maintenance, repair, inspection, or installation and choose a priority level.',
        'inicio.overview.services.link_user': 'View my assets →',
        'inicio.overview.services.link_guest': 'Create an account →',
        'inicio.card.ayuda.guest_p': 'Explore answers and guidance to get started.',
        'inicio.guest.login_p': 'Sign in to access your Kinetix workspace.',
        'inicio.guest.register_p': 'Sign up to access your Kinetix workspace.',
        'inicio.overview.inventory_index': 'INVENTORY',
        'inicio.overview.support_index': 'SUPPORT',
        'register.heading': 'Create an account',
        'register.subtitle': 'Complete your details to register for our service.',
        'register.first_name': 'First name',
        'register.last_name': 'Last name',
        'register.first_name_placeholder': 'Your first name',
        'register.last_name_placeholder': 'Your last name',
        'register.work_location': 'This user works at',
        'register.choose_location': 'Select a location',
        'register.role_question': 'Which role would you like to work in?',
        'register.role_tooltip': 'In this role, you will mainly interact with the technician. You can create and reply to tickets and request products.',
        'register.client': 'Client',
        'register.technician': 'Technician',
        'register.terms_prefix': 'By creating an account, you accept our',
        'register.terms': 'Terms and Privacy Policy.',
        'register.submit': 'Register',
        'register.have_account': 'Already have an account?',
        'register.sign_in': 'Sign in',
        'register.footer': '© 2026 Kinetix Software. All rights reserved.',
        'login.success': 'You have successfully signed in.',
        'register.required_fields': 'Please complete every field in the form.',
        'register.email_exists': 'This email address is already registered.',
        'register.error': 'Could not register the user. Please try again.',
        'register.success': 'Account created successfully! Enter your password to sign in.',
        'inventory.all_items': 'View all inventory',
        'inventory.items': 'items',
        'tickets.empty': 'No tickets or service requests to show.',
        'tickets.all_requests': 'Requests',
        'tickets.request_status': 'Change request status',
        'tickets.select_asset': 'Select an asset',
        'tickets.ticket_closed': 'This ticket is closed.',
        'tickets.message_send': 'Send',
        'tickets.request_history_empty': 'The selected request history will appear here.',
        'tickets.category': 'Category:',
        'tickets.view_request': 'View request',
        'usuario.role.admin': 'Administrator',
        'usuario.role.user': 'User',
        'usuario.role.technician': 'Technician',
        'ticket_create.title': 'Create ticket | Kinetix',
    },
};

(function () {
    const root = document.documentElement;
    const supportedLanguages = ['es', 'en'];
    const originalContent = new WeakMap();
    const dynamicTranslations = {
        en: {
            'procesadores': 'Processors',
            'memorias': 'Memory',
            'almacenamiento': 'Storage',
            'tarjetas gráficas': 'Graphics cards',
            'periféricos': 'Peripherals',
            'otros': 'Other',
            'hardware': 'Hardware',
            'software': 'Software',
            'redes': 'Networking',
            'correo': 'Email',
            'acceso': 'Access',
            'inventario': 'Inventory',
            'disponible': 'Available',
            'prestado': 'Assigned',
            'en reparacion': 'Under repair',
            'en reparación': 'Under repair',
            'discontinuado': 'Discontinued',
            'abierto': 'Open',
            'cancelado': 'Cancelled',
            'revisado': 'Under review',
            'cerrado': 'Closed',
            'resuelto': 'Resolved',
            'creada': 'Created',
            'revisada': 'Reviewed',
            'cerrada': 'Closed',
            'solucionada': 'Resolved',
            'baja': 'Low',
            'media': 'Medium',
            'alta': 'High',
            'usuario': 'User',
            'tecnico': 'Technician',
            'técnico': 'Technician',
            'administrador': 'Administrator',
            'mantenimiento': 'Maintenance',
            'reparacion': 'Repair',
            'reparación': 'Repair',
            'revision': 'Inspection',
            'revisión': 'Inspection',
            'instalacion': 'Installation',
            'instalación': 'Installation',
            'sin prioridad': 'No priority',
        },
    };

    function readStoredLanguage() {
        try {
            return localStorage.getItem('kinetix_lang');
        } catch (error) {
            console.error('Kinetix i18n could not read the saved language:', error);
            return null;
        }
    }

    function saveLanguage(lang) {
        try {
            localStorage.setItem('kinetix_lang', lang);
        } catch (error) {
            console.error('Kinetix i18n could not save the selected language:', error);
        }
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (character) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
        })[character]);
    }

    function setLang(lang) {
        if (!supportedLanguages.includes(lang)) {
            console.error(`Kinetix i18n received an unsupported language: ${lang}`);
            return;
        }

        saveLanguage(lang);
        root.lang = lang;
        root.setAttribute('data-lang', lang);

        document.querySelectorAll('[data-i18n]').forEach((el) => {
            const key = el.getAttribute('data-i18n');
            const translation = key && i18n[lang][key];
            if (!originalContent.has(el)) {
                originalContent.set(
                    el,
                    el.dataset.i18nHtml === 'true' ? el.innerHTML : el.textContent
                );
            }

            let text = typeof translation === 'string' ? translation : originalContent.get(el);
            if (el.dataset.i18nParams) {
                try {
                    const params = JSON.parse(el.dataset.i18nParams);
                    Object.entries(params).forEach(([name, value]) => {
                        text = text.split(`{${name}}`).join(
                            el.dataset.i18nHtml === 'true' ? escapeHtml(value) : String(value)
                        );
                    });
                } catch (error) {
                    console.error(`Kinetix i18n could not read parameters for "${key}":`, error);
                }
            }

            if (el.dataset.i18nHtml === 'true') {
                el.innerHTML = text;
            } else {
                el.textContent = text;
            }
        });

        document.querySelectorAll('[data-i18n-attrs]').forEach((el) => {
            try {
                const attributes = JSON.parse(el.dataset.i18nAttrs);
                const originalAttributes = el.dataset.i18nOriginalAttributes
                    ? JSON.parse(el.dataset.i18nOriginalAttributes)
                    : Object.keys(attributes).reduce((originals, attribute) => {
                        originals[attribute] = el.getAttribute(attribute);
                        return originals;
                    }, {});
                el.dataset.i18nOriginalAttributes = JSON.stringify(originalAttributes);

                Object.entries(attributes).forEach(([attribute, key]) => {
                    const translation = i18n[lang][key];
                    if (typeof translation === 'string') {
                        el.setAttribute(attribute, translation);
                    } else if (originalAttributes[attribute] !== null) {
                        el.setAttribute(attribute, originalAttributes[attribute]);
                    }
                });
            } catch (error) {
                console.error('Kinetix i18n could not read translated attributes:', error);
            }
        });

        document.querySelectorAll('[data-i18n-db]').forEach((el) => {
            if (!Object.hasOwn(el.dataset, 'i18nOriginal')) {
                el.dataset.i18nOriginal = el.textContent.trim();
            }
            const original = el.dataset.i18nOriginal;
            const translated = dynamicTranslations[lang]?.[original.toLocaleLowerCase()];
            el.textContent = lang === 'en' ? (translated || original) : original;
        });

        document.querySelectorAll('[data-i18n-toggle], #langToggle').forEach((btn) => {
            btn.setAttribute('aria-label', lang === 'es' ? 'Cambiar a inglés' : 'Switch to Spanish');
        });

        const langToggle = document.getElementById('langToggle');
        langToggle?.classList.toggle('lang-en', lang === 'en');

        const themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            const isDark = root.dataset.theme === 'dark';
            themeToggle.setAttribute(
                'aria-label',
                lang === 'en'
                    ? (isDark ? 'Switch to light mode' : 'Switch to dark mode')
                    : (isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro')
            );
        }
    }

    const pageLanguage = root.lang && root.lang.toLowerCase().startsWith('en') ? 'en' : 'es';
    const storedLanguage = readStoredLanguage();
    const currentLang = supportedLanguages.includes(storedLanguage)
        ? storedLanguage
        : pageLanguage;
    const langToggle = document.getElementById('langToggle');

    langToggle?.addEventListener('click', () => {
        const current = root.getAttribute('data-lang') || currentLang;
        const next = current === 'es' ? 'en' : 'es';
        setLang(next);
    });

    setLang(currentLang);
})();
