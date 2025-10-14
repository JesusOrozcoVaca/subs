// URL Helper - Compatible con ambos sistemas (legacy y nuevo)
// Este archivo proporciona funciones para generar URLs correctas según el entorno

/**
 * Detecta si estamos en el nuevo sistema (query parameters) o sistema legacy
 */
function isNewSystem() {
    // Si la URL contiene index_new.php o query parameters de acción, es el nuevo sistema
    return window.location.href.includes('index_new.php') || 
           window.location.search.includes('action=') ||
           (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1');
}

/**
 * Obtiene la URL base correcta según el entorno
 */
function getBaseUrl() {
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        // Desarrollo local - incluir el directorio del proyecto
        const pathParts = window.location.pathname.split('/').filter(part => part !== '');
        return window.location.origin + '/' + (pathParts.length > 0 ? pathParts[0] + '/' : '');
    } else {
        // Producción - usar la raíz del dominio
        return window.location.origin + '/';
    }
}

/**
 * Genera una URL usando el sistema apropiado
 */
function generateUrl(action, params = {}) {
    const baseUrl = getBaseUrl();
    
    if (isNewSystem()) {
        // Nuevo sistema - usar query parameters
        let url = baseUrl + 'index_new.php?action=' + action;
        if (Object.keys(params).length > 0) {
            url += '&' + new URLSearchParams(params).toString();
        }
        return url;
    } else {
        // Sistema legacy - usar URLs amigables
        switch (action) {
            case 'login':
                return baseUrl + 'login';
            case 'admin_dashboard':
                return baseUrl + 'admin/dashboard';
            case 'admin_create_user':
                return baseUrl + 'admin/create-user';
            case 'admin_create_product':
                return baseUrl + 'admin/create-product';
            case 'admin_create_cpc':
                return baseUrl + 'admin/create-cpc';
            case 'admin_edit_user':
                return baseUrl + 'admin/edit-user/' + (params.id || '');
            case 'admin_edit_product':
                return baseUrl + 'admin/edit-product/' + (params.id || '');
            case 'admin_edit_cpc':
                return baseUrl + 'admin/edit-cpc/' + (params.id || '');
            case 'admin_toggle_user_status':
                return baseUrl + 'admin/toggle-user-status';
            case 'admin_manage_product':
                return baseUrl + 'admin/manage-product/' + (params.id || '');
            case 'admin_delete_user':
                return baseUrl + 'admin/delete-user';
            case 'admin_delete_product':
                return baseUrl + 'admin/delete-product';
            case 'admin_delete_cpc':
                return baseUrl + 'admin/delete-cpc';
            case 'moderator_dashboard':
                return baseUrl + 'moderator/dashboard';
            case 'moderator_manage_cpcs':
                return baseUrl + 'moderator/manage-cpcs';
            case 'moderator_edit_cpc':
                return baseUrl + 'moderator/edit-cpc/' + (params.id || '');
            case 'moderator_manage_questions':
                return baseUrl + 'moderator/manage-questions/' + (params.id || '');
            case 'moderator_evaluate_participants':
                return baseUrl + 'moderator/evaluate-participants/' + (params.id || '');
            case 'moderator_delete_cpc':
                return baseUrl + 'moderator/delete-cpc';
            case 'participant_dashboard':
                return baseUrl + 'participant/dashboard';
            case 'participant_view_product':
                return baseUrl + 'participant/view-product/' + (params.id || '');
            case 'participant_profile':
                return baseUrl + 'participant/profile';
            case 'participant_search_process':
                return baseUrl + 'participant/search-process';
            case 'participant_add_cpc':
                return baseUrl + 'participant/add-cpc';
            case 'participant_remove_cpc':
                return baseUrl + 'participant/remove-cpc';
            case 'participant_phase':
                return baseUrl + 'participant/phase/' + (params.phase || '');
            default:
                console.warn('Acción no reconocida:', action);
                return baseUrl;
        }
    }
}

/**
 * Función de conveniencia para URLs comunes
 */
const URLS = {
    login: () => generateUrl('login'),
    adminDashboard: () => generateUrl('admin_dashboard'),
    moderatorDashboard: () => generateUrl('moderator_dashboard'),
    participantDashboard: () => generateUrl('participant_dashboard'),
    adminCreateUser: () => generateUrl('admin_create_user'),
    adminCreateProduct: () => generateUrl('admin_create_product'),
    adminCreateCpc: () => generateUrl('admin_create_cpc'),
    adminEditUser: (id) => generateUrl('admin_edit_user', { id }),
    adminEditProduct: (id) => generateUrl('admin_edit_product', { id }),
    adminEditCpc: (id) => generateUrl('admin_edit_cpc', { id }),
    adminToggleUserStatus: () => generateUrl('admin_toggle_user_status'),
    adminManageProduct: (id) => generateUrl('admin_manage_product', { id }),
    adminDeleteUser: () => generateUrl('admin_delete_user'),
    adminDeleteProduct: () => generateUrl('admin_delete_product'),
    adminDeleteCpc: () => generateUrl('admin_delete_cpc'),
    moderatorManageCpcs: () => generateUrl('moderator_manage_cpcs'),
    moderatorEditCpc: (id) => generateUrl('moderator_edit_cpc', { id }),
    moderatorManageQuestions: (id) => generateUrl('moderator_manage_questions', { id }),
    moderatorEvaluateParticipants: (id) => generateUrl('moderator_evaluate_participants', { id }),
    moderatorDeleteCpc: () => generateUrl('moderator_delete_cpc'),
    participantViewProduct: (id) => generateUrl('participant_view_product', { id }),
    participantProfile: () => generateUrl('participant_profile'),
    participantSearchProcess: () => generateUrl('participant_search_process'),
    participantAddCpc: () => generateUrl('participant_add_cpc'),
    participantRemoveCpc: () => generateUrl('participant_remove_cpc'),
    participantPhase: (phase) => generateUrl('participant_phase', { phase })
};

// Exportar para uso global
window.URLS = URLS;
window.generateUrl = generateUrl;
window.isNewSystem = isNewSystem;
window.getBaseUrl = getBaseUrl;
