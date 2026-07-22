// URL Helper - Compatible con local (/subs/) y producción (raíz del dominio)
// Fuente de verdad para armar URLs en JS. No hardcodear '/subs/' en otros archivos.

/**
 * ¿Estamos en entorno local?
 */
function isLocalHost() {
    const host = window.location.hostname;
    return host === 'localhost' || host === '127.0.0.1';
}

/**
 * Prefijo de aplicación: '/subs/' en local, '/' en producción.
 */
function getAppBasePath() {
    if (isLocalHost()) {
        const parts = window.location.pathname.split('/').filter(part => part !== '');
        if (parts.length > 0 && !parts[0].includes('.php')) {
            return '/' + parts[0] + '/';
        }
        return '/';
    }
    return '/';
}

/**
 * URL base absoluta según el entorno (incluye origin).
 */
function getBaseUrl() {
    return window.location.origin + getAppBasePath();
}

/**
 * Archivo índice a usar cuando el routing es por query parameters.
 */
function getIndexFile() {
    const path = window.location.pathname;
    if (path.includes('indexpro.php')) return 'indexpro.php';
    if (path.includes('index_new.php')) return 'index_new.php';
    if (path.includes('index.php')) return 'index.php';
    return 'index.php';
}

/**
 * Detecta si debemos usar query parameters (producción / index.php)
 * o rutas amigables legacy (local con .htaccess).
 */
function isNewSystem() {
    if (!isLocalHost()) {
        return true;
    }
    return window.location.pathname.includes('index.php')
        || window.location.pathname.includes('indexpro.php')
        || window.location.pathname.includes('index_new.php')
        || window.location.search.includes('action=');
}

/**
 * Mapeo legacy de acciones -> rutas amigables (solo local).
 */
function legacyPathForAction(action, params = {}) {
    const id = params.id || '';
    const phase = params.phase || '';

    const map = {
        login: 'login',
        logout: 'logout',
        unauthorized: 'unauthorized',
        admin_dashboard: 'admin/dashboard',
        admin_create_user: 'admin/create-user',
        admin_create_product: 'admin/create-product',
        admin_create_cpc: 'admin/create-cpc',
        admin_edit_user: 'admin/edit-user/' + id,
        admin_edit_product: 'admin/edit-product/' + id,
        admin_edit_cpc: 'admin/edit-cpc/' + id,
        admin_toggle_user_status: 'admin/toggle-user-status',
        admin_manage_product: 'admin/manage-product/' + id,
        admin_delete_user: 'admin/delete-user',
        admin_delete_product: 'admin/delete-product',
        admin_delete_cpc: 'admin/delete-cpc',
        admin_get_unanswered_questions: 'admin/get-unanswered-questions',
        admin_answer_questions: 'admin/answer-questions',
        admin_get_offer_ratings: 'admin/get-offer-ratings',
        admin_save_offer_rating: 'admin/save-offer-rating',
        moderator_dashboard: 'moderator/dashboard',
        moderator_manage_cpcs: 'moderator/manage-cpcs',
        moderator_edit_cpc: 'moderator/edit-cpc/' + id,
        moderator_manage_questions: 'moderator/manage-questions/' + id,
        moderator_evaluate_participants: 'moderator/evaluate-participants/' + id,
        moderator_delete_cpc: 'moderator/delete-cpc',
        moderator_get_unanswered_questions: 'moderator/get-unanswered-questions',
        moderator_answer_questions: 'moderator/answer-questions',
        moderator_get_offer_ratings: 'moderator/get-offer-ratings',
        moderator_save_offer_rating: 'moderator/save-offer-rating',
        participant_dashboard: 'participant/dashboard',
        participant_view_product: 'participant/view-product/' + id,
        participant_profile: 'participant/profile',
        participant_search_process: 'participant/search-process',
        participant_add_cpc: 'participant/add-cpc',
        participant_remove_cpc: 'participant/remove-cpc',
        participant_phase: 'participant/phase/' + phase,
        participant_get_questions: 'participant/get-questions',
        participant_submit_question: 'participant/submit-question',
        participant_get_offer_rating: 'participant/get-offer-rating',
        participant_submit_initial_offer: 'participant/submit-initial-offer',
        participant_download_offer_pdf: 'participant/download-offer-pdf',
        participant_process_offer: 'participant/process-offer',
        participant_upload_offer: 'participant/upload-offer',
        participant_get_offers: 'participant/get-offers',
        participant_delete_offer: 'participant/delete-offer',
        participant_download_convalidation_pdf: 'participant/download-convalidation-pdf',
        participant_get_convalidation: 'participant/get-convalidation',
        participant_submit_convalidation: 'participant/submit-convalidation',
        view_file: 'index.php'
    };

    return map[action] || '';
}

/**
 * Genera una URL usando el sistema apropiado.
 */
function generateUrl(action, params = {}) {
    const baseUrl = getBaseUrl();
    const isTrainingAction = typeof action === 'string' && (
        action.indexOf('admin_training_') === 0 || action.indexOf('participant_training_') === 0
    );
    const useQueryParams = isNewSystem() || action === 'view_file' || isTrainingAction;

    if (useQueryParams) {
        const indexFile = getIndexFile();
        const query = new URLSearchParams({ action, ...params });
        // Evitar duplicar action si venía en params
        query.set('action', action);
        return baseUrl + indexFile + '?' + query.toString();
    }

    const legacyPath = legacyPathForAction(action, params);
    let url = baseUrl + legacyPath;

    // Params que no van en el path (id/phase ya embebidios cuando aplica)
    const queryParams = { ...params };
    if (action === 'participant_phase') {
        delete queryParams.phase;
    }
    if (['admin_edit_user', 'admin_edit_product', 'admin_edit_cpc',
         'admin_manage_product', 'moderator_edit_cpc',
         'moderator_manage_questions', 'moderator_evaluate_participants',
         'participant_view_product'].includes(action)) {
        delete queryParams.id;
    }

    const remaining = Object.keys(queryParams).filter(k => queryParams[k] !== undefined && queryParams[k] !== null && queryParams[k] !== '');
    if (remaining.length > 0) {
        const qs = new URLSearchParams();
        remaining.forEach(k => qs.set(k, queryParams[k]));
        url += (url.includes('?') ? '&' : '?') + qs.toString();
    }

    return url;
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
    adminGetOfferRatings: (producto_id) => generateUrl('admin_get_offer_ratings', { producto_id }),
    adminSaveOfferRating: () => generateUrl('admin_save_offer_rating'),
    moderatorManageCpcs: () => generateUrl('moderator_manage_cpcs'),
    moderatorEditCpc: (id) => generateUrl('moderator_edit_cpc', { id }),
    moderatorManageQuestions: (id) => generateUrl('moderator_manage_questions', { id }),
    moderatorEvaluateParticipants: (id) => generateUrl('moderator_evaluate_participants', { id }),
    moderatorDeleteCpc: () => generateUrl('moderator_delete_cpc'),
    moderatorGetOfferRatings: () => generateUrl('moderator_get_offer_ratings'),
    moderatorSaveOfferRating: () => generateUrl('moderator_save_offer_rating'),
    participantViewProduct: (id) => generateUrl('participant_view_product', { id }),
    participantProfile: () => generateUrl('participant_profile'),
    participantSearchProcess: () => generateUrl('participant_search_process'),
    participantAddCpc: () => generateUrl('participant_add_cpc'),
    participantRemoveCpc: () => generateUrl('participant_remove_cpc'),
    participantPhase: (phase, producto_id) => generateUrl('participant_phase', { phase, producto_id }),
    participantGetQuestions: (producto_id, page = 1, limit = 5) => generateUrl('participant_get_questions', { producto_id, page, limit }),
    participantSubmitQuestion: () => generateUrl('participant_submit_question'),
    participantGetOfferRating: () => generateUrl('participant_get_offer_rating'),
    participantSubmitInitialOffer: () => generateUrl('participant_submit_initial_offer'),
    participantDownloadOfferPdf: (producto_id) => generateUrl('participant_download_offer_pdf', { producto_id }),
    participantProcessOffer: () => generateUrl('participant_process_offer'),
    participantUploadOffer: () => generateUrl('participant_upload_offer'),
    participantGetOffers: (producto_id) => generateUrl('participant_get_offers', { producto_id }),
    participantDeleteOffer: () => generateUrl('participant_delete_offer'),
    participantDownloadConvalidationPdf: (producto_id) => generateUrl('participant_download_convalidation_pdf', { producto_id }),
    participantGetConvalidation: (producto_id) => generateUrl('participant_get_convalidation', { producto_id }),
    participantSubmitConvalidation: () => generateUrl('participant_submit_convalidation'),
    viewFile: (path) => generateUrl('view_file', { path }),
    adminTrainingDashboard: () => generateUrl('admin_training_dashboard'),
    adminTrainingToggleInscripcion: () => generateUrl('admin_training_toggle_inscripcion'),
    participantTrainingList: () => generateUrl('participant_training_list'),
    participantTrainingJoin: (id) => generateUrl('participant_training_join', { id }),
    participantTrainingPuja: (id) => generateUrl('participant_training_puja', { id }),
    participantTrainingSubmitBid: () => generateUrl('participant_training_submit_bid'),
    participantTrainingPujaStatus: (id) => generateUrl('participant_training_puja_status', { id }),
    participantTrainingSummary: (id) => generateUrl('participant_training_summary', { id })
};

// Exportar para uso global
window.URLS = URLS;
window.generateUrl = generateUrl;
window.isNewSystem = isNewSystem;
window.isLocalHost = isLocalHost;
window.getBaseUrl = getBaseUrl;
window.getAppBasePath = getAppBasePath;
window.getIndexFile = getIndexFile;
