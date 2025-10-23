// URL Helper - Compatible con ambos sistemas (legacy y nuevo)
// Este archivo proporciona funciones para generar URLs correctas según el entorno

/**
 * Detecta si estamos en el nuevo sistema (query parameters) o sistema legacy
 */
function isNewSystem() {
    // Si la URL contiene index_new.php o query parameters de acción, es el nuevo sistema
    const isNew = window.location.href.includes('index_new.php') || 
           window.location.search.includes('action=') ||
           (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1');
    
    console.log('=== URL SYSTEM DETECTION ===');
    console.log('Current URL:', window.location.href);
    console.log('Search params:', window.location.search);
    console.log('Hostname:', window.location.hostname);
    console.log('Is new system:', isNew);
    
    return isNew;
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
    const isNew = isNewSystem();
    
    console.log('=== GENERATING URL ===');
    console.log('Action:', action);
    console.log('Params:', params);
    console.log('Base URL:', baseUrl);
    console.log('Is new system:', isNew);
    
    if (isNew) {
        // Nuevo sistema - usar query parameters
        // En producción, index_new.php se renombra a index.php
        const indexFile = (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') 
            ? 'index_new.php' 
            : 'index.php';
        let url = baseUrl + indexFile + '?action=' + action;
        if (Object.keys(params).length > 0) {
            url += '&' + new URLSearchParams(params).toString();
        }
        console.log('Generated URL (new system):', url);
        return url;
    } else {
        // Sistema legacy - usar URLs amigables
        console.log('Using legacy system for action:', action);
        let url;
        switch (action) {
            case 'login':
                url = baseUrl + 'login';
                break;
            case 'admin_dashboard':
                url = baseUrl + 'admin/dashboard';
                break;
            case 'admin_create_user':
                url = baseUrl + 'admin/create-user';
                break;
            case 'admin_create_product':
                url = baseUrl + 'admin/create-product';
                break;
            case 'admin_create_cpc':
                url = baseUrl + 'admin/create-cpc';
                break;
            case 'admin_edit_user':
                url = baseUrl + 'admin/edit-user/' + (params.id || '');
                break;
            case 'admin_edit_product':
                url = baseUrl + 'admin/edit-product/' + (params.id || '');
                break;
            case 'admin_edit_cpc':
                url = baseUrl + 'admin/edit-cpc/' + (params.id || '');
                break;
            case 'admin_toggle_user_status':
                url = baseUrl + 'admin/toggle-user-status';
                break;
            case 'admin_manage_product':
                url = baseUrl + 'admin/manage-product/' + (params.id || '');
                break;
            case 'admin_delete_user':
                url = baseUrl + 'admin/delete-user';
                break;
            case 'admin_delete_product':
                url = baseUrl + 'admin/delete-product';
                break;
            case 'admin_delete_cpc':
                url = baseUrl + 'admin/delete-cpc';
                break;
            case 'moderator_dashboard':
                url = baseUrl + 'moderator/dashboard';
                break;
            case 'moderator_manage_cpcs':
                url = baseUrl + 'moderator/manage-cpcs';
                break;
            case 'moderator_edit_cpc':
                url = baseUrl + 'moderator/edit-cpc/' + (params.id || '');
                break;
            case 'moderator_manage_questions':
                url = baseUrl + 'moderator/manage-questions/' + (params.id || '');
                break;
            case 'moderator_evaluate_participants':
                url = baseUrl + 'moderator/evaluate-participants/' + (params.id || '');
                break;
            case 'moderator_delete_cpc':
                url = baseUrl + 'moderator/delete-cpc';
                break;
            case 'participant_dashboard':
                url = baseUrl + 'participant/dashboard';
                break;
            case 'participant_view_product':
                url = baseUrl + 'participant/view-product/' + (params.id || '');
                break;
            case 'participant_profile':
                url = baseUrl + 'participant/profile';
                break;
            case 'participant_search_process':
                url = baseUrl + 'participant/search-process';
                break;
            case 'participant_add_cpc':
                url = baseUrl + 'participant/add-cpc';
                break;
            case 'participant_remove_cpc':
                url = baseUrl + 'participant/remove-cpc';
                break;
            case 'participant_phase':
                url = baseUrl + 'participant/phase/' + (params.phase || '');
                break;
            default:
                console.warn('Acción no reconocida:', action);
                url = baseUrl;
        }
        
        console.log('Generated URL (legacy system):', url);
        return url;
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
