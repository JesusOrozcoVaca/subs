<?php
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/CPC.php';
require_once BASE_PATH . '/models/OfferSubmission.php';
require_once BASE_PATH . '/models/OfferRating.php';
require_once BASE_PATH . '/models/InitialOfferSubmission.php';
require_once BASE_PATH . '/models/PujaConfig.php';
require_once BASE_PATH . '/models/Bid.php';
require_once BASE_PATH . '/services/ReverseAuctionEngine.php';

class ParticipantController {
    private $productModel;
    private $userModel;
    private $cpcModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->cpcModel = new CPC();
    }

    public function profile() {
        $userId = $_SESSION['user_id'];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isAjaxRequest()) {
                header('Location: ' . url('participant/profile'));
                exit;
            }
    
            $action = $_POST['action'] ?? '';
    
            if (empty($action)) {
                $this->sendJsonResponse(true, "No se realizó ninguna acción.", null);
                return;
            }
    
            try {
                $result = false;

                switch ($action) {
                    case 'update_profile':
                        $correo = isset($_POST['correo_electronico']) ? trim((string)$_POST['correo_electronico']) : '';
                        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                            $this->sendJsonResponse(false, "Correo electrónico inválido. Verifica el formato.");
                            return;
                        }
                        if ($this->userModel->emailExists($correo, $userId)) {
                            $this->sendJsonResponse(false, "El correo electrónico ya existe y no puede ser duplicado.\nCorreo: {$correo}");
                            return;
                        }
                        $telefono = isset($_POST['telefono']) ? trim((string)$_POST['telefono']) : '';
                        if ($telefono === '' || !preg_match('/^\d{10}$/', $telefono)) {
                            $this->sendJsonResponse(false, "Teléfono inválido. Asegúrate de haber ingresado el teléfono correcto.");
                            return;
                        }
                        if ($this->userModel->phoneExists($telefono, $userId)) {
                            $this->sendJsonResponse(false, "El teléfono ya existe y no puede ser duplicado.\nTeléfono: {$telefono}");
                            return;
                        }
                        $result = $this->userModel->updateUser($userId, $_POST);
                        break;
                    case 'add_cpc':
                        $result = $this->userModel->addCPC($userId, $_POST['cpc_id']);
                        break;
                    case 'add_cpcs_bulk':
                        $cpcIds = $_POST['cpc_ids'] ?? [];
                        if (!is_array($cpcIds)) {
                            $this->sendJsonResponse(false, "No se seleccionaron CPCs.");
                            return;
                        }
                        $cpcIds = array_values(array_filter(array_map('intval', $cpcIds), function ($id) {
                            return $id > 0;
                        }));
                        if (empty($cpcIds)) {
                            $this->sendJsonResponse(false, "No se seleccionaron CPCs.");
                            return;
                        }
                        $result = $this->userModel->addCPCs($userId, $cpcIds);
                        break;
                    case 'remove_cpc':
                        $result = $this->userModel->removeCPC($userId, $_POST['cpc_id']);
                        break;
                    default:
                        throw new Exception("Acción no reconocida.");
                }
                
                if ($result) {
                    $user = $this->userModel->getUserById($userId);
                    $userCPCs = $this->userModel->getUserCPCs($userId);
                    $allCPCs = $this->cpcModel->getUnassignedCPCs($userId);
    
                    $this->sendJsonResponse(true, "Operación realizada con éxito.", [
                        'user' => $user,
                        'userCPCs' => $userCPCs,
                        'availableCPCs' => $allCPCs
                    ]);
                } else {
                    $this->sendJsonResponse(false, "Error al realizar la operación.");
                }
            } catch (Exception $e) {
                $this->sendJsonResponse(false, "Error: " . $e->getMessage());
            }
            return;
        }
    
        $user = $this->userModel->getUserById($userId);
        $userCPCs = $this->userModel->getUserCPCs($userId);
        $allCPCs = $this->cpcModel->getUnassignedCPCs($userId);
        
        $pageTitle = "Mi Perfil";
        $content = $this->renderView('part_profile.php', compact('user', 'userCPCs', 'allCPCs'));
        
        $this->renderResponse($pageTitle, $content);
    }

    public function searchProcess() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = $_POST['codigo'] ?? '';
            $searchResult = $this->productModel->getProductByCode($codigo);
            
            if ($this->isAjaxRequest()) {
                if ($searchResult) {
                    $resultHtml = $this->renderView('part_search_results.php', ['searchResult' => $searchResult]);
                    $this->sendJsonResponse(true, 'Búsqueda realizada con éxito.', ['content' => $resultHtml]);
                } else {
                    $this->sendJsonResponse(true, 'No se encontraron resultados.', ['content' => '<p>No se encontraron resultados.</p>']);
                }
                return;
            }
        }
    
        $pageTitle = "Buscar Proceso";
        $content = $this->renderView('part_search_process.php', ['searchResult' => null]);
        
        $this->renderResponse($pageTitle, $content);
    }

    public function dashboard() {
        error_log("=== DASHBOARD METHOD START ===");
        $userId = $_SESSION['user_id'];
        error_log("User ID: " . $userId);
        
        error_log("=== CALLING GETPARTICIPANTPRODUCTS ===");
        $products = $this->productModel->getParticipantProducts($userId);
        error_log("Products retrieved: " . count($products) . " items");
        
        $pageTitle = "Dashboard de Participante";
        error_log("=== RENDERING VIEW ===");
        $content = $this->renderView('part_dashboard.php', ['products' => $products]);
        error_log("=== VIEW RENDERED SUCCESSFULLY ===");
        
        error_log("=== RENDERING RESPONSE ===");
        $this->renderResponse($pageTitle, $content);
        error_log("=== DASHBOARD METHOD COMPLETED ===");
    }

    public function viewProduct($id) {
        error_log("=== VIEWPRODUCT METHOD START ===");
        $userId = $_SESSION['user_id'];
        error_log("User ID: " . $userId);
        error_log("Product ID: " . $id);
        
        $product = $this->productModel->getProductById($id);
        error_log("Product retrieved: " . print_r($product, true));
        
        // Obtener la información del CPC
        $cpcInfo = $this->cpcModel->getCPCById($product['cpc_id']);
        $product['cpc_descripcion'] = $cpcInfo ? $cpcInfo['descripcion'] : 'CPC no encontrado';
        $product['cpc_codigo'] = $cpcInfo ? $cpcInfo['codigo'] : 'CPC no encontrado';
        
        $userStatus = $this->productModel->getParticipantStatus($id, $userId);
        $dates = $this->productModel->calculateDates($id);
        $documents = $this->productModel->getProductDocuments($id);
    
        // Obtener estados desde la base de datos
        require_once BASE_PATH . '/models/ProductState.php';
        $productStateModel = new ProductState();
        $allPhases = $productStateModel->getStatesForSelect();
        
        // Implementar lógica de desbloqueo de fases basada en el estado del producto
        $phases = $this->getUnlockedPhases($product, $allPhases);
        $currentStateCode = $this->getCurrentStateCode($product);
        
        error_log("=== RENDERING VIEW PRODUCT ===");
        require_once BASE_PATH . '/views/participant/part_view_product.php';
        error_log("=== VIEW PRODUCT RENDERED ===");
    }
    
    public function loadPhaseContent($phase) {
        error_log("=== LOADPHASECONTENT START ===");
        error_log("Phase: " . $phase);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        
        // Obtener el ID del producto desde el parámetro GET
        $productId = $_GET['producto_id'] ?? null;
        error_log("Product ID from GET: " . $productId);
        
        if (!$productId) {
            error_log("No product ID found in GET parameter");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de producto no encontrado']);
            return;
        }
        
        // Mapear números a códigos de fase
        $phaseMap = [
            '1' => 'pyr',
            '2' => 'eof', 
            '3' => 'conv',
            '4' => 'calif',
            '5' => 'ofini',
            '6' => 'puja',
            '7' => 'adj'
        ];
        
        // Si es un número, convertirlo a código de fase
        if (isset($phaseMap[$phase])) {
            $phase = $phaseMap[$phase];
            error_log("Phase mapped to: " . $phase);
        }

        require_once BASE_PATH . '/models/ProductState.php';
        $productStateModel = new ProductState();
        $phaseState = $productStateModel->getStateByCode($phase);
        if (!$phaseState) {
            error_log("Phase not allowed: " . $phase);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Fase no válida']);
            return;
        }
    
        // Construir la ruta del archivo de vista
        $viewFile = BASE_PATH . "/views/participant/phases/{$phase}.php";
        error_log("View file: " . $viewFile);
        error_log("File exists: " . (file_exists($viewFile) ? 'YES' : 'NO'));
        
        if (file_exists($viewFile)) {
            // Limpiar todos los buffers existentes ANTES de generar el contenido
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Iniciar un nuevo buffer para capturar el contenido de la vista
            ob_start();
            
            // Obtener información completa del producto
            $product = $this->productModel->getProductById($productId);
            if (!$product) {
                ob_end_clean();
                error_log("Product not found: " . $productId);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                exit;
            }

            $currentStateCode = $this->getCurrentStateCode($product);
            $isReadOnly = ($phase !== $currentStateCode);

            $userId = $_SESSION['user_id'] ?? null;
            $hasOffer = false;
            $offerSubmissionModel = new OfferSubmission();
            if ($userId) {
                require_once BASE_PATH . '/models/Document.php';
                $documentModel = new Document();
                $userDocuments = $documentModel->getUserDocuments($productId, $userId);
                $hasOffer = (!empty($userDocuments)) || $offerSubmissionModel->exists($productId, $userId);
            }

            $normalize = function ($value) {
                $value = strtolower(trim((string)$value));
                return strtr($value, [
                    'á' => 'a',
                    'é' => 'e',
                    'í' => 'i',
                    'ó' => 'o',
                    'ú' => 'u',
                    'ü' => 'u',
                    'ñ' => 'n'
                ]);
            };

            $allowedNoOfferDescriptions = [
                'preguntas y respuestas',
                'por adjudicar',
                'adjudicado - registro de contrato',
                'adjudicado registro de contrato',
                'en ejecucion',
                'en recepcion',
                'finalizado'
            ];

            $allowedNoOfferCodes = [
                'pyr',
                'por_adj',
                'por-adj',
                'por_adjudicar',
                'por-adjudicar',
                'poradj',
                'adj',
                'adjudicado',
                'registro_contrato',
                'reg_contrato',
                'ejec',
                'ejecucion',
                'en-ejecucion',
                'en_ejecucion',
                'recep',
                'recepcion',
                'en-recepcion',
                'en_recepcion',
                'fin',
                'final',
                'finalizado'
            ];

            $phaseDescription = $phaseState['descripcion'] ?? '';
            $normalizedDescription = $normalize($phaseDescription);
            $isAllowedNoOffer = in_array($phase, $allowedNoOfferCodes, true)
                || in_array($normalizedDescription, $allowedNoOfferDescriptions, true);

            if ($phase === 'pyr') {
                $isAllowedNoOffer = true;
            }

            if ($currentStateCode === 'eof' && $phase === 'eof') {
                $isAllowedNoOffer = true;
            }

            $blockWithoutOffer = !$hasOffer && !$isAllowedNoOffer;

            $initialOfferSent = false;
            $initialOfferDocument = null;
            if ($phase === 'ofini' && $userId) {
                $initialOfferModel = new InitialOfferSubmission();
                $initialOfferSubmission = $initialOfferModel->getByProductAndUser($productId, $userId);
                if ($initialOfferSubmission) {
                    $initialOfferSent = true;
                    $offerDetail = $offerSubmissionModel->getByProductAndUser($productId, $userId);
                    $user = $this->userModel->getUserById($userId);
                    if ($offerDetail && $user) {
                        $initialOfferDocument = $this->buildInitialOfferDocumentData(
                            $product,
                            $user,
                            $offerDetail,
                            $initialOfferSubmission
                        );
                    }
                }
            }

            $pujaSchedule = null;
            if ($phase === 'puja') {
                $pujaConfigModel = new PujaConfig();
                $pujaConfig = $pujaConfigModel->getByProductId($productId);
                if ($pujaConfig && !empty($pujaConfig['hora_inicio'])) {
                    $pujaSchedule = $this->buildPujaSchedule(
                        $pujaConfig['hora_inicio'],
                        $pujaConfig['duracion_minutos'] ?? 0,
                        $pujaConfig['zona_horaria'] ?? 'UTC'
                    );
                }
            }

            $blockPuja = false;
            $pujaBlockMessage = '';
            $pujaSummary = null;
            if ($phase === 'puja' && $userId) {
                $pujaEligibility = $this->evaluatePujaEligibility($productId, $userId);
                $blockPuja = !$pujaEligibility['eligible'];
                $pujaBlockMessage = $pujaEligibility['message'];

                if (!empty($pujaSchedule['end_ts_ms'])) {
                    $nowMs = (int)round(microtime(true) * 1000);
                    if ($nowMs > (int)$pujaSchedule['end_ts_ms']) {
                        $timezone = $pujaSchedule['timezone'] ?? 'UTC';
                        $participants = $this->productModel->getEligiblePujaParticipants($productId);
                        $bidModel = new Bid();
                        $columns = [];
                        $maxEntries = 0;
                        // Ganador = menor valor ofertado (subasta inversa).
                        // Se compara el mejor valor de cada participante (mínimo entre pujas
                        // y, si no hubo pujas, su oferta inicial).
                        $winnerName = null;
                        $winnerValue = null;
                        $winnerTimeMs = null;

                        foreach ($participants as $participant) {
                            $userBids = $bidModel->getUserBids($productId, $participant['id']);
                            $rows = [];
                            $bestValue = null;
                            $bestTimeMs = null;

                            if (!empty($userBids)) {
                                foreach ($userBids as $bid) {
                                    $bidValue = (float)$bid['valor'];
                                    $bidTimeMs = isset($bid['fecha_puja_ms']) ? (int)$bid['fecha_puja_ms'] : 0;
                                    $rows[] = [
                                        'value' => '$ ' . number_format($bidValue, 2, ',', '.'),
                                        'time' => $this->formatPujaTimestamp(
                                            $bid['fecha_puja_ms'] ?? 0,
                                            $bid['fecha_puja'] ?? null,
                                            $timezone
                                        )
                                    ];

                                    if ($bestValue === null || $bidValue < $bestValue || ($bidValue === $bestValue && ($bestTimeMs === null || $bidTimeMs < $bestTimeMs))) {
                                        $bestValue = $bidValue;
                                        $bestTimeMs = $bidTimeMs;
                                    }
                                }
                            }

                            // Siempre agregar la oferta inicial al final (es la más antigua),
                            // incluso si el participante hizo pujas durante la fase.
                            $initialInfo = $offerSubmissionModel->getInitialOfferInfo($productId, $participant['id']);
                            if ($initialInfo) {
                                $initialValue = (float)($initialInfo['oferta_inicial_user'] ?? 0);
                                $initialTimeMs = !empty($initialInfo['fecha_oferta_inicial'])
                                    ? ((int)strtotime($initialInfo['fecha_oferta_inicial']) * 1000)
                                    : 0;
                                $rows[] = [
                                    'value' => '$ ' . number_format($initialValue, 2, ',', '.'),
                                    'time' => $this->formatPujaTimestamp(
                                        0,
                                        $initialInfo['fecha_oferta_inicial'] ?? null,
                                        $timezone
                                    )
                                ];

                                // Si no hubo pujas, la oferta inicial es su valor a comparar.
                                if ($bestValue === null) {
                                    $bestValue = $initialValue;
                                    $bestTimeMs = $initialTimeMs;
                                }
                            }

                            if ($bestValue !== null) {
                                if (
                                    $winnerValue === null
                                    || $bestValue < $winnerValue
                                    || ($bestValue === $winnerValue && ($winnerTimeMs === null || $bestTimeMs < $winnerTimeMs))
                                ) {
                                    $winnerValue = $bestValue;
                                    $winnerTimeMs = $bestTimeMs;
                                    $winnerName = $participant['nombre_completo'];
                                }
                            }

                            $maxEntries = max($maxEntries, count($rows));
                            $columns[] = [
                                'name' => $participant['nombre_completo'],
                                'rows' => $rows
                            ];
                        }

                        $pujaSummary = [
                            'columns' => $columns,
                            'max_entries' => $maxEntries,
                            'winner_name' => $winnerName,
                            'winner_value' => $winnerValue
                        ];
                    }
                }
            }
            
            include $viewFile;
            $content = ob_get_clean();
            error_log("Content length: " . strlen($content));
            
            // Asegurar que no hay output previo
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'content' => $content]);
            error_log("=== LOADPHASECONTENT COMPLETED SUCCESSFULLY ===");
            exit;
        } else {
            // Limpiar todos los buffers
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            error_log("View file not found: " . $viewFile);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Contenido de fase no encontrado']);
            exit;
        }
    }

    private function getProductIdFromReferer() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        error_log("Referer URL: " . $referer);
        
        // Extraer ID del producto de la URL de referencia
        if (preg_match('/participant\/view-product\/(\d+)/', $referer, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    private function getUnlockedPhases($product, $allPhases) {
        // Obtener el código del estado actual del producto
        $currentStateCode = $this->getCurrentStateCode($product);
        error_log("Current state code: " . $currentStateCode);
        
        // Mapeo de códigos de estado a fases desbloqueadas
        $statePhaseMap = [
            'pyr' => ['pyr'], // Preguntas y Respuestas - siempre disponible
            'eof' => ['pyr', 'eof'], // Entrega de Ofertas - desbloquea Preguntas y Respuestas (solo lectura) + Entrega de Ofertas
            'conv' => ['pyr', 'eof', 'conv'], // Convalidación - desbloquea todas las anteriores
            'calif' => ['pyr', 'eof', 'conv', 'calif'], // Calificación
            'ofini' => ['pyr', 'eof', 'conv', 'calif', 'ofini'], // Oferta Inicial
            'puja' => ['pyr', 'eof', 'conv', 'calif', 'ofini', 'puja'], // Puja
            'adj' => ['pyr', 'eof', 'conv', 'calif', 'ofini', 'puja', 'adj'] // Adjudicado
        ];
        
        $unlockedPhases = $statePhaseMap[$currentStateCode] ?? ['pyr'];
        
        // Filtrar solo las fases desbloqueadas
        $phases = [];
        foreach ($allPhases as $phaseKey => $phaseName) {
            if (in_array($phaseKey, $unlockedPhases)) {
                $phases[$phaseKey] = $phaseName;
            }
        }
        
        error_log("Current state code: " . $currentStateCode);
        error_log("Unlocked phases: " . implode(', ', $unlockedPhases));
        error_log("Available phases: " . implode(', ', array_keys($phases)));
        
        return $phases;
    }

    private function getCurrentStateCode($product) {
        error_log("getCurrentStateCode - Product estado_id: " . ($product['estado_id'] ?? 'null'));
        
        // Si el producto tiene estado_id, obtener el código del estado
        if (isset($product['estado_id']) && $product['estado_id']) {
            require_once BASE_PATH . '/models/ProductState.php';
            $productStateModel = new ProductState();
            $stateCode = $productStateModel->getStateCodeById($product['estado_id']);
            error_log("getCurrentStateCode - State code: " . $stateCode);
            return $stateCode;
        }
        
        // Si no se encuentra, asumir que está en estado inicial
        error_log("getCurrentStateCode - Defaulting to pyr");
        return 'pyr';
    }

    public function addCpc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $userId = $_SESSION['user_id'];
            $cpcId = $_POST['cpc_id'] ?? null;
    
            if ($cpcId) {
                $result = $this->userModel->addCPC($userId, $cpcId);
                if ($result) {
                    $userCPCs = $this->userModel->getUserCPCs($userId);
                    $availableCPCs = $this->cpcModel->getUnassignedCPCs($userId);
    
                    $this->sendJsonResponse(true, "CPC agregado exitosamente.", [
                        'userCPCs' => $userCPCs,
                        'availableCPCs' => $availableCPCs
                    ]);
                } else {
                    $this->sendJsonResponse(false, "Error al agregar el CPC.");
                }
            } else {
                $this->sendJsonResponse(false, "CPC no especificado.");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido.");
        }
    }
    
    public function removeCpc() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $userId = $_SESSION['user_id'];
            $cpcId = $_POST['cpc_id'] ?? null;
    
            if ($cpcId) {
                $result = $this->userModel->removeCPC($userId, $cpcId);
                if ($result) {
                    $userCPCs = $this->userModel->getUserCPCs($userId);
                    $availableCPCs = $this->cpcModel->getUnassignedCPCs($userId);
    
                    $this->sendJsonResponse(true, "CPC eliminado exitosamente.", [
                        'userCPCs' => $userCPCs,
                        'availableCPCs' => $availableCPCs
                    ]);
                } else {
                    $this->sendJsonResponse(false, "Error al eliminar el CPC.");
                }
            } else {
                $this->sendJsonResponse(false, "CPC no especificado.");
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido.");
        }
    }

    private function renderView($viewName, $data = []) {
        error_log("=== RENDERVIEW START ===");
        error_log("View name: " . $viewName);
        error_log("Data: " . print_r($data, true));
        
        extract($data);
        ob_start();
        error_log("=== INCLUDING VIEW FILE ===");
        require_once BASE_PATH . '/views/participant/' . $viewName;
        $content = ob_get_clean();
        error_log("=== VIEW CONTENT GENERATED ===");
        return $content;
    }

    private function sendJsonResponse($success, $message, $data = null) {
        error_log("=== SENDJSONRESPONSE START ===");
        error_log("Success: " . ($success ? 'YES' : 'NO'));
        error_log("Message: " . $message);
        
        // Limpiar TODOS los buffers de salida
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Asegurar que no haya salida previa
        if (headers_sent($file, $line)) {
            error_log("WARNING: Headers already sent in $file on line $line");
        }
        
        header('Content-Type: application/json');
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data
        ];
        
        error_log("Response: " . json_encode($response));
        echo json_encode($response);
        exit;
    }

    private function renderResponse($pageTitle, $content) {
        error_log("=== RENDERRESPONSE START ===");
        error_log("Page title: " . $pageTitle);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        
        if ($this->isAjaxRequest()) {
            error_log("=== SENDING AJAX RESPONSE ===");
            $this->sendJsonResponse(true, '', ['content' => $content, 'title' => $pageTitle]);
        } else {
            error_log("=== INCLUDING LAYOUT FILE ===");
            require_once BASE_PATH . '/views/participant/participant_layout.php';
            error_log("=== LAYOUT INCLUDED SUCCESSFULLY ===");
        }
    }

    private function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    public function submitQuestion() {
        error_log("=== SUBMITQUESTION START ===");
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Is AJAX: " . ($this->isAjaxRequest() ? 'YES' : 'NO'));
        error_log("POST data: " . print_r($_POST, true));
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $pregunta = trim($_POST['pregunta'] ?? '');
            
            error_log("Producto ID: " . $productoId);
            error_log("Pregunta: " . $pregunta);
            
            if (!$productoId || empty($pregunta)) {
                error_log("Datos incompletos");
                $this->sendJsonResponse(false, "Datos incompletos");
                return;
            }
            
            if (strlen($pregunta) > 500) {
                error_log("Pregunta muy larga: " . strlen($pregunta));
                $this->sendJsonResponse(false, "La pregunta no puede exceder 500 caracteres");
                return;
            }
            
            require_once BASE_PATH . '/models/Question.php';
            $questionModel = new Question();
            
            $result = $questionModel->create([
                'producto_id' => $productoId,
                'usuario_id' => $_SESSION['user_id'],
                'pregunta' => $pregunta
            ]);
            
            error_log("Create result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result) {
                $this->sendJsonResponse(true, "Pregunta enviada exitosamente");
            } else {
                $this->sendJsonResponse(false, "Error al enviar la pregunta");
            }
        } else {
            error_log("Método no permitido");
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function getQuestions() {
        if ($this->isAjaxRequest()) {
            $productoId = $_GET['producto_id'] ?? null;
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 5);
            
            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }
            
            require_once BASE_PATH . '/models/Question.php';
            $questionModel = new Question();
            
            $data = $questionModel->getProductQuestionsPaginated($productoId, $page, $limit);
            
            $this->sendJsonResponse(true, "", $data);
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function uploadOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            
            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }
            
            if (!isset($_FILES['documento_oferta']) || $_FILES['documento_oferta']['error'] !== UPLOAD_ERR_OK) {
                $this->sendJsonResponse(false, "Error al subir el archivo");
                return;
            }
            
            $file = $_FILES['documento_oferta'];
            
            // Validaciones
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            $maxSize = 512 * 1024; // 512KB
            
            if (!in_array($file['type'], $allowedTypes)) {
                $this->sendJsonResponse(false, "Tipo de archivo no permitido. Solo PDF, JPG y PNG");
                return;
            }
            
            if ($file['size'] > $maxSize) {
                $this->sendJsonResponse(false, "El archivo excede el tamaño máximo de 512KB");
                return;
            }
            
            require_once BASE_PATH . '/models/Document.php';
            $documentModel = new Document();
            
            $result = $documentModel->uploadDocument($productoId, $usuarioId, $file);
            
            if ($result['success']) {
                $this->sendJsonResponse(true, "Archivo subido exitosamente", $result);
            } else {
                $this->sendJsonResponse(false, $result['message']);
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function getOffers() {
        if ($this->isAjaxRequest()) {
            $productoId = $_GET['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            
            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }
            
            require_once BASE_PATH . '/models/Document.php';
            $documentModel = new Document();
            $offerSubmissionModel = new OfferSubmission();
            
            $ofertas = $documentModel->getUserDocuments($productoId, $usuarioId);
            $isProcessed = $documentModel->isOfferProcessed($productoId, $usuarioId);
            $summary = $offerSubmissionModel->getByProductAndUser($productoId, $usuarioId);
            
            $this->sendJsonResponse(true, "", [
                'ofertas' => $ofertas,
                'processed' => $isProcessed,
                'offer_summary' => $summary
            ]);
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function getOfferRating() {
        if ($this->isAjaxRequest()) {
            $productoId = $_GET['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];

            if (!$productoId || !is_numeric($productoId)) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }

            $offerRatingModel = new OfferRating();
            $rating = $offerRatingModel->getUserOfferRating((int)$productoId, (int)$usuarioId);

            $this->sendJsonResponse(true, "", [
                'rating' => $rating
            ]);
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function deleteOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $fileId = $_POST['file_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            
            if (!$fileId) {
                $this->sendJsonResponse(false, "ID de archivo requerido");
                return;
            }
            
            require_once BASE_PATH . '/models/Document.php';
            $documentModel = new Document();
            
            $result = $documentModel->deleteDocument($fileId, $usuarioId);
            
            if ($result['success']) {
                $this->sendJsonResponse(true, "Archivo eliminado exitosamente");
            } else {
                $this->sendJsonResponse(false, $result['message']);
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function processOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            $tiempoEntrega = trim($_POST['tiempo_entrega'] ?? '');
            $plazoOferta = trim($_POST['plazo_oferta'] ?? '');
            $ofertaInicialUser = trim($_POST['oferta_inicial_user'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            
            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }

            if ($tiempoEntrega === '' || $plazoOferta === '' || $ofertaInicialUser === '' || $descripcion === '') {
                $this->sendJsonResponse(false, "Debe completar todos los datos solicitados");
                return;
            }

            if (strlen($tiempoEntrega) > 100 || strlen($plazoOferta) > 100) {
                $this->sendJsonResponse(false, "Los campos de tiempo de entrega y plazo de la oferta no pueden exceder 100 caracteres");
                return;
            }

            if (!is_numeric($ofertaInicialUser) || floatval($ofertaInicialUser) < 0) {
                $this->sendJsonResponse(false, "La oferta inicial debe ser un número válido mayor o igual a 0");
                return;
            }

            if (strlen($descripcion) > 1000) {
                $this->sendJsonResponse(false, "La descripción no puede exceder 1000 caracteres");
                return;
            }

            $product = $this->productModel->getProductById($productoId);
            if (!$product || !isset($product['presupuesto_referencial'])) {
                $this->sendJsonResponse(false, "No se pudo validar el presupuesto referencial del producto");
                return;
            }

            $presupuestoCents = $this->toCents($product['presupuesto_referencial']);
            $ofertaCents = $this->toCents($ofertaInicialUser);

            if ($presupuestoCents === null || $ofertaCents === null) {
                $this->sendJsonResponse(false, "No se pudo validar el valor de la oferta inicial");
                return;
            }

            if ($ofertaCents >= $presupuestoCents) {
                $this->sendJsonResponse(false, "La oferta inicial debe ser menor al presupuesto referencial del producto (al menos 0.01 menos).");
                return;
            }

            $plazoEntregaProducto = $this->normalizeNumeric($product['plazo_entrega'] ?? null);
            $vigenciaOfertaProducto = $this->normalizeNumeric($product['vigencia_oferta'] ?? null);
            $plazoEntregaIngresado = $this->normalizeNumeric($tiempoEntrega);
            $vigenciaOfertaIngresado = $this->normalizeNumeric($plazoOferta);

            if ($plazoEntregaProducto === null || $vigenciaOfertaProducto === null) {
                $this->sendJsonResponse(false, "No se pudo validar los plazos del producto");
                return;
            }

            if ($plazoEntregaIngresado === null || $vigenciaOfertaIngresado === null) {
                $this->sendJsonResponse(false, "Los valores de tiempo de entrega y plazo de la oferta deben ser numéricos");
                return;
            }

            if ($plazoEntregaIngresado !== $plazoEntregaProducto || $vigenciaOfertaIngresado !== $vigenciaOfertaProducto) {
                $this->sendJsonResponse(false, "Los valores de tiempo de entrega y plazo de la oferta deben coincidir con los definidos para el producto");
                return;
            }
            
            require_once BASE_PATH . '/models/Document.php';
            require_once BASE_PATH . '/config/database.php';
            $documentModel = new Document();
            $offerSubmissionModel = new OfferSubmission();
            $connection = Database::getInstance()->getConnection();

            if ($documentModel->isOfferProcessed($productoId, $usuarioId)) {
                $this->sendJsonResponse(false, "La oferta ya fue procesada previamente");
                return;
            }

            $ofertasActuales = $documentModel->getUserDocuments($productoId, $usuarioId);
            if (!$ofertasActuales || count($ofertasActuales) === 0) {
                $this->sendJsonResponse(false, "Debe subir al menos un documento antes de procesar la oferta");
                return;
            }

            if ($offerSubmissionModel->exists($productoId, $usuarioId)) {
                $this->sendJsonResponse(false, "Ya existe un registro de oferta procesada para este producto");
                return;
            }

            try {
                $connection->beginTransaction();

                $created = $offerSubmissionModel->create(
                    $productoId,
                    $usuarioId,
                    $tiempoEntrega,
                    $plazoOferta,
                    floatval($ofertaInicialUser),
                    $descripcion
                );

                if (!$created) {
                    throw new Exception("No se pudo guardar la información adicional de la oferta");
                }

                $result = $documentModel->processOffer($productoId, $usuarioId);

                if (!$result) {
                    throw new Exception("No se pudo marcar la oferta como procesada");
                }

                $connection->commit();

                $summary = $offerSubmissionModel->getByProductAndUser($productoId, $usuarioId);
                $this->sendJsonResponse(true, "Entrega de ofertas procesada exitosamente", [
                    'offer_summary' => $summary,
                    'processed' => true
                ]);
            } catch (Exception $e) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                $this->sendJsonResponse(false, "Error al procesar la entrega: " . $e->getMessage());
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function submitInitialOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $valorOferta = trim($_POST['valor_oferta'] ?? '');
            $usuarioId = $_SESSION['user_id'];

            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }

            if ($valorOferta === '') {
                $this->sendJsonResponse(false, "Debe ingresar el valor de su oferta inicial");
                return;
            }

            $offerSubmissionModel = new OfferSubmission();
            $initialOfferModel = new InitialOfferSubmission();

            if ($initialOfferModel->exists($productoId, $usuarioId)) {
                $this->sendJsonResponse(false, "Usted ya ha enviado su oferta inicial.");
                return;
            }

            $offerDetail = $offerSubmissionModel->getByProductAndUser($productoId, $usuarioId);
            if (!$offerDetail) {
                $this->sendJsonResponse(false, "No se encontró la oferta ingresada al entregar la oferta");
                return;
            }

            $valorIngresadoCents = $this->toCents($valorOferta);
            $valorRegistradoCents = $this->toCents($offerDetail['oferta_inicial_user'] ?? null);

            if ($valorIngresadoCents === null || $valorRegistradoCents === null) {
                $this->sendJsonResponse(false, "El valor ingresado no corresponde a su oferta ingresada al entregar la oferta. Inténtelo nuevamente.");
                return;
            }

            if ($valorIngresadoCents !== $valorRegistradoCents) {
                $this->sendJsonResponse(false, "El valor ingresado no corresponde a su oferta ingresada al entregar la oferta. Inténtelo nuevamente.");
                return;
            }

            $product = $this->productModel->getProductById($productoId);
            if (!$product) {
                $this->sendJsonResponse(false, "Producto no encontrado");
                return;
            }

            $user = $this->userModel->getUserById($usuarioId);
            if (!$user) {
                $this->sendJsonResponse(false, "Usuario no encontrado");
                return;
            }

            $fechaEcuador = new DateTime('now', new DateTimeZone('America/Guayaquil'));
            $codigoAleatorio = $this->generateRandomCode(32);
            $createdAt = $fechaEcuador->format('Y-m-d H:i:s');

            if (!$initialOfferModel->create($productoId, $usuarioId, $codigoAleatorio, $createdAt)) {
                $this->sendJsonResponse(false, "No se pudo registrar la oferta inicial");
                return;
            }

            $documentData = $this->buildInitialOfferDocumentData($product, $user, $offerDetail, [
                'codigo' => $codigoAleatorio,
                'created_at' => $createdAt
            ]);

            $this->sendJsonResponse(true, "Validación exitosa", [
                'document' => $documentData
            ]);
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    private function buildInitialOfferDocumentData(array $product, array $user, array $offerDetail, array $submission) {
        return [
            'empresa' => $user['nombre_completo'] ?? '',
            'ruc' => $user['cedula'] ?? '',
            'usuario' => $user['nombre_completo'] ?? '',
            'fecha' => $this->formatEcuadorDateTime($submission['created_at'] ?? 'now'),
            'modulo' => 'Oferta Inicial',
            'entidad_contratante' => $product['entidad'] ?? '',
            'objeto_proceso' => $product['objeto_proceso'] ?? '',
            'codigo' => $product['codigo'] ?? '',
            'tipo_compra' => 'Servicio',
            'tipo_contratacion' => $product['tipo_contratacion'] ?? '',
            'estado_proceso' => 'Oferta Inicial',
            'oferta_inicial' => number_format((float)($offerDetail['oferta_inicial_user'] ?? 0), 2, ',', '.'),
            'codigo_aleatorio' => $submission['codigo'] ?? ''
        ];
    }

    private function formatEcuadorDateTime($value) {
        $timezone = new DateTimeZone('America/Guayaquil');
        try {
            $date = new DateTime($value, $timezone);
        } catch (Exception $e) {
            $date = new DateTime('now', $timezone);
        }
        return $date->format('d-M-Y H:i:s');
    }

    private function toCents($value) {
        if ($value === null) {
            return null;
        }
        $normalized = str_replace([' ', ','], ['', '.'], (string)$value);
        if (!is_numeric($normalized)) {
            return null;
        }
        return (int)round(((float)$normalized) * 100);
    }

    private function generateRandomCode($length = 32) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $maxIndex = strlen($characters) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $maxIndex)];
        }

        return $code;
    }

    private function normalizeNumeric($value) {
        if ($value === null) {
            return null;
        }
        $normalized = str_replace([' ', ','], ['', '.'], (string)$value);
        if (!is_numeric($normalized)) {
            return null;
        }
        if (strpos($normalized, '.') !== false) {
            $normalized = rtrim(rtrim($normalized, '0'), '.');
        }
        return $normalized === '' ? '0' : $normalized;
    }

    public function submitConvalidation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->isAjaxRequest()) {
            $productoId = $_POST['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];
            $detalle = trim($_POST['respuesta_convalidacion'] ?? '');

            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }

            if ($detalle === '') {
                $this->sendJsonResponse(false, "Debe ingresar el detalle de la convalidación");
                return;
            }

            if (strlen($detalle) > 2000) {
                $this->sendJsonResponse(false, "El detalle no puede exceder 2000 caracteres");
                return;
            }

            if (!isset($_FILES['documentos_convalidacion'])) {
                $this->sendJsonResponse(false, "Debe adjuntar al menos un archivo");
                return;
            }

            $files = $this->normalizeUploadedFiles($_FILES['documentos_convalidacion']);
            if (empty($files)) {
                $this->sendJsonResponse(false, "Debe adjuntar al menos un archivo");
                return;
            }

            $allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            $maxSize = 5 * 1024 * 1024;

            foreach ($files as $file) {
                if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    $this->sendJsonResponse(false, "Error al subir el archivo: " . ($file['name'] ?? ''));
                    return;
                }

                if (!in_array($file['type'], $allowedTypes)) {
                    $this->sendJsonResponse(false, "Tipo de archivo no permitido. Solo PDF, DOC y DOCX");
                    return;
                }

                if ($file['size'] > $maxSize) {
                    $this->sendJsonResponse(false, "El archivo excede el tamaño máximo de 5MB");
                    return;
                }
            }

            require_once BASE_PATH . '/models/ConvalidationSubmission.php';
            require_once BASE_PATH . '/models/ConvalidationDocument.php';
            require_once BASE_PATH . '/config/database.php';

            $connection = Database::getInstance()->getConnection();
            $convalidationModel = new ConvalidationSubmission($connection);
            $documentModel = new ConvalidationDocument($connection);

            if ($convalidationModel->exists($productoId, $usuarioId)) {
                $this->sendJsonResponse(false, "La convalidación ya fue enviada previamente");
                return;
            }

            date_default_timezone_set('America/Guayaquil');
            $now = date('Y-m-d H:i:s');
            $uploadedFiles = [];

            try {
                $connection->beginTransaction();

                $convalidationId = $convalidationModel->create($productoId, $usuarioId, $detalle, $now);
                if (!$convalidationId) {
                    throw new Exception("No se pudo registrar la convalidación");
                }

                foreach ($files as $file) {
                    $result = $documentModel->uploadDocument($convalidationId, $file, $now);
                    if (!$result['success']) {
                        throw new Exception($result['message'] ?? "Error al subir archivos");
                    }
                    if (!empty($result['full_path'])) {
                        $uploadedFiles[] = $result['full_path'];
                    }
                }

                $connection->commit();

                $summary = $convalidationModel->getByProductAndUser($productoId, $usuarioId);
                if ($summary) {
                    $summary['created_at_formatted'] = $this->formatEcuadorDate($summary['created_at'] ?? $now);
                }
                $filesList = $documentModel->getFilesByConvalidation($convalidationId);

                $this->sendJsonResponse(true, "Convalidación enviada exitosamente", [
                    'summary' => $summary,
                    'files' => $filesList
                ]);
            } catch (Exception $e) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                foreach ($uploadedFiles as $path) {
                    if ($path && file_exists($path)) {
                        unlink($path);
                    }
                }
                $this->sendJsonResponse(false, "Error al enviar la convalidación: " . $e->getMessage());
            }
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function getConvalidation() {
        if ($this->isAjaxRequest()) {
            $productoId = $_GET['producto_id'] ?? null;
            $usuarioId = $_SESSION['user_id'];

            if (!$productoId) {
                $this->sendJsonResponse(false, "ID de producto requerido");
                return;
            }

            require_once BASE_PATH . '/models/ConvalidationSubmission.php';
            require_once BASE_PATH . '/models/ConvalidationDocument.php';

            $convalidationModel = new ConvalidationSubmission();
            $documentModel = new ConvalidationDocument();

            $summary = $convalidationModel->getByProductAndUser($productoId, $usuarioId);
            $files = [];

            if ($summary) {
                $summary['created_at_formatted'] = $this->formatEcuadorDate($summary['created_at'] ?? 'now');
                $files = $documentModel->getFilesByConvalidation($summary['id']);
            }

            $this->sendJsonResponse(true, "", [
                'summary' => $summary,
                'files' => $files
            ]);
        } else {
            $this->sendJsonResponse(false, "Método no permitido");
        }
    }

    public function downloadConvalidationPdf() {
        $productoId = $_GET['producto_id'] ?? null;
        $usuarioId = $_SESSION['user_id'];

        date_default_timezone_set('America/Guayaquil');

        if (!$productoId) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "ID de producto requerido");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        require_once BASE_PATH . '/models/ConvalidationSubmission.php';
        require_once BASE_PATH . '/models/ConvalidationDocument.php';
        require_once BASE_PATH . '/services/ConvalidationPdfGenerator.php';

        $convalidationModel = new ConvalidationSubmission();
        $documentModel = new ConvalidationDocument();
        $summary = $convalidationModel->getByProductAndUser($productoId, $usuarioId);

        if (!$summary) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "No se encontró la convalidación enviada");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        $product = $this->productModel->getProductById($productoId);
        if (!$product) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "Producto no encontrado");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        $user = $this->userModel->getUserById($usuarioId);
        if (!$user) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "Usuario no encontrado");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        $files = $documentModel->getFilesByConvalidation($summary['id']);
        $pdfInfo = ConvalidationPdfGenerator::generate($product, $user, $summary, $files);

        if (!$pdfInfo) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "Error al generar el PDF");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        $fullPath = $pdfInfo['full_path'];
        if (!file_exists($fullPath)) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "El archivo PDF no existe");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: attachment; filename="' . $pdfInfo['file_name'] . '"');
        header('Cache-Control: private, max-age=3600');

        readfile($fullPath);
        exit;
    }

    public function pyrDirect($productId = null) {
        error_log("=== PYRDIRECT START ===");
        
        // Si no se proporciona el ID, intentar obtenerlo de la URL
        if (!$productId) {
            $productId = $this->getProductIdFromURL();
        }
        
        error_log("Product ID: " . $productId);
        
        if (!$productId) {
            error_log("No product ID found");
            header('Location: ' . url('participant/dashboard'));
            exit;
        }
        
        // Obtener información del producto
        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            error_log("Product not found");
            header('Location: ' . url('participant/dashboard'));
            exit;
        }
        
        error_log("Product found: " . $product['codigo']);
        
        // Renderizar vista directa
        $content = $this->renderView('pyr_test.php', [
            'product' => $product
        ]);
        
        // Enviar el contenido al navegador
        echo $content;
    }
    
    private function getProductIdFromURL() {
        $pathParts = explode('/', $_SERVER['REQUEST_URI']);
        $productId = end($pathParts);
        return is_numeric($productId) ? $productId : null;
    }

    private function normalizeUploadedFiles($fileInput) {
        if (!is_array($fileInput['name'])) {
            return [$fileInput];
        }

        $files = [];
        foreach ($fileInput['name'] as $index => $name) {
            $files[] = [
                'name' => $name,
                'type' => $fileInput['type'][$index] ?? '',
                'tmp_name' => $fileInput['tmp_name'][$index] ?? '',
                'error' => $fileInput['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $fileInput['size'][$index] ?? 0
            ];
        }
        return $files;
    }

    private function formatEcuadorDate($value) {
        $timezone = new DateTimeZone('America/Guayaquil');
        try {
            $date = new DateTime($value, $timezone);
        } catch (Exception $e) {
            $date = new DateTime('now', $timezone);
        }
        return $date->format('d/m/Y H:i');
    }

    private function buildPujaSchedule($horaInicioUtc, $duracionMinutos, $zonaHoraria) {
        return ReverseAuctionEngine::buildSchedule($horaInicioUtc, $duracionMinutos, $zonaHoraria);
    }

    public function pujaWindow($productId = null) {
        if (!$productId) {
            $productId = $this->getProductIdFromURL();
        }

        if (!$productId) {
            header('Location: ' . url('participant/dashboard'));
            exit;
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            header('Location: ' . url('participant/dashboard'));
            exit;
        }

        $currentStateCode = $this->getCurrentStateCode($product);
        $userId = $_SESSION['user_id'] ?? null;
        $offerSubmissionModel = new OfferSubmission();
        $offerDetail = $userId ? $offerSubmissionModel->getByProductAndUser($productId, $userId) : null;

        $pujaEligibility = $userId ? $this->evaluatePujaEligibility($productId, $userId) : [
            'eligible' => false,
            'message' => 'Debe iniciar sesión para participar en la puja.'
        ];
        $blockPuja = !$pujaEligibility['eligible'];
        $pujaBlockMessage = $pujaEligibility['message'];

        $pujaSchedule = null;
        $pujaConfigModel = new PujaConfig();
        $pujaConfig = $pujaConfigModel->getByProductId($productId);
        if ($pujaConfig && !empty($pujaConfig['hora_inicio'])) {
            $pujaSchedule = $this->buildPujaSchedule(
                $pujaConfig['hora_inicio'],
                $pujaConfig['duracion_minutos'] ?? 0,
                $pujaConfig['zona_horaria'] ?? 'UTC'
            );
        }

        $bidModel = new Bid();
        $lowestBid = $bidModel->getLowestBid($productId);
        $userLastBid = $userId ? $bidModel->getUserLastBid($productId, $userId) : null;
        $isUserBest = false;
        if ($userLastBid && $lowestBid !== null) {
            $isUserBest = abs((float)$userLastBid['valor'] - (float)$lowestBid) < 0.00001;
        } elseif ($userLastBid && $lowestBid === null) {
            $isUserBest = true;
        }

        $variationPercent = isset($product['variacion_minima']) ? (float)$product['variacion_minima'] : 0.0;
        $initialOfferValue = $offerDetail && isset($offerDetail['oferta_inicial_user'])
            ? (float)$offerDetail['oferta_inicial_user']
            : 0.0;
        $variationAmount = round($initialOfferValue * ($variationPercent / 100), 2);

        $content = $this->renderView('puja_window.php', [
            'product' => $product,
            'currentStateCode' => $currentStateCode,
            'blockPuja' => $blockPuja,
            'pujaBlockMessage' => $pujaBlockMessage,
            'pujaSchedule' => $pujaSchedule,
            'userLastBid' => $userLastBid,
            'lowestBid' => $lowestBid,
            'isUserBest' => $isUserBest,
            'variationAmount' => $variationAmount,
            'initialOfferValue' => $initialOfferValue
        ]);

        echo $content;
    }

    public function submitBid() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->isAjaxRequest()) {
            $this->sendJsonResponse(false, "Método no permitido.");
            return;
        }

        $productId = $_POST['producto_id'] ?? null;
        $valorRaw = $_POST['valor'] ?? '';
        $userId = $_SESSION['user_id'] ?? null;

        if (!$productId || !$userId) {
            $this->sendJsonResponse(false, "Datos incompletos para registrar la puja.");
            return;
        }

        $product = $this->productModel->getProductById($productId);
        if (!$product) {
            $this->sendJsonResponse(false, "Producto no encontrado.");
            return;
        }

        $pujaConfigModel = new PujaConfig();
        $pujaConfig = $pujaConfigModel->getByProductId($productId);
        if ($pujaConfig && !empty($pujaConfig['hora_inicio'])) {
            $schedule = ReverseAuctionEngine::buildSchedule(
                $pujaConfig['hora_inicio'],
                $pujaConfig['duracion_minutos'] ?? 0,
                $pujaConfig['zona_horaria'] ?? 'UTC'
            );
            $window = ReverseAuctionEngine::evaluateWindow($schedule);
            if (!$window['open']) {
                $this->sendJsonResponse(false, $window['message']);
                return;
            }
        }

        $currentStateCode = $this->getCurrentStateCode($product);
        if ($currentStateCode !== 'puja') {
            $this->sendJsonResponse(false, "La fase de puja no está activa.");
            return;
        }

        $pujaEligibility = $this->evaluatePujaEligibility($productId, $userId);
        if (!$pujaEligibility['eligible']) {
            $this->sendJsonResponse(false, $pujaEligibility['message']);
            return;
        }

        $offerSubmissionModel = new OfferSubmission();
        $offerDetail = $offerSubmissionModel->getByProductAndUser($productId, $userId);
        $initialOfferValue = $offerDetail && isset($offerDetail['oferta_inicial_user'])
            ? (float)$offerDetail['oferta_inicial_user']
            : 0.0;

        $bidModel = new Bid();
        $result = ReverseAuctionEngine::submitBid($valorRaw, [
            'presupuesto_referencial' => $product['presupuesto_referencial'] ?? 0,
            'variacion_minima' => $product['variacion_minima'] ?? 0,
            'oferta_inicial' => $initialOfferValue,
            'user_last_bid' => $bidModel->getUserLastBid($productId, $userId),
            'lowest_bid' => $bidModel->getLowestBid($productId),
            'create_callback' => function ($valor, $fechaMs) use ($bidModel, $productId, $userId) {
                return $bidModel->create([
                    'producto_id' => $productId,
                    'usuario_id' => $userId,
                    'valor' => $valor,
                    'fecha_puja_ms' => $fechaMs
                ]);
            }
        ]);

        if (!$result['success']) {
            $this->sendJsonResponse(false, $result['message']);
            return;
        }

        $statusData = $this->buildPujaStatusData($productId, $userId);
        $this->sendJsonResponse(true, $result['message'], $statusData);
    }

    public function getPujaStatus($productId = null) {
        if (!$this->isAjaxRequest()) {
            $this->sendJsonResponse(false, "Método no permitido.");
            return;
        }

        if (!$productId) {
            $productId = $_GET['producto_id'] ?? ($_GET['id'] ?? null);
        }
        $userId = $_SESSION['user_id'] ?? null;

        if (!$productId || !$userId) {
            $this->sendJsonResponse(false, "Datos incompletos para obtener estado.");
            return;
        }

        $pujaEligibility = $this->evaluatePujaEligibility($productId, $userId);
        if (!$pujaEligibility['eligible']) {
            $this->sendJsonResponse(false, $pujaEligibility['message']);
            return;
        }

        $statusData = $this->buildPujaStatusData($productId, $userId);
        $this->sendJsonResponse(true, "", $statusData);
    }

    private function buildPujaStatusData($productId, $userId) {
        $bidModel = new Bid();
        return ReverseAuctionEngine::buildStatusData(
            $bidModel->getUserLastBid($productId, $userId),
            $bidModel->getLowestBid($productId)
        );
    }

    private function formatPujaTimestamp($timestampMs, $fallbackDateTime, $timezone) {
        return ReverseAuctionEngine::formatPujaTimestamp($timestampMs, $fallbackDateTime, $timezone);
    }

    private function evaluatePujaEligibility($productId, $userId) {
        $offerSubmissionModel = new OfferSubmission();
        require_once BASE_PATH . '/models/Document.php';
        $documentModel = new Document();
        $userDocuments = $documentModel->getUserDocuments($productId, $userId);
        $hasOffer = (!empty($userDocuments)) || $offerSubmissionModel->exists($productId, $userId);

        $offerRatingModel = new OfferRating();
        $rating = $offerRatingModel->getUserOfferRating($productId, $userId);
        $hasRatingCumple = $rating && ($rating['calificacion'] === 'Cumple');

        $initialOfferModel = new InitialOfferSubmission();
        $initialOfferSubmission = $initialOfferModel->getByProductAndUser($productId, $userId);
        $hasInitialOffer = (bool)$initialOfferSubmission;

        if (!$hasOffer) {
            return [
                'eligible' => false,
                'message' => 'Usted no cargo oferta para este proceso por lo tanto, no puede participar en el mismo.'
            ];
        }

        if (!$hasRatingCumple) {
            return [
                'eligible' => false,
                'message' => 'Usted no puede participar en la puja porque su oferta no fue calificada como Cumple.'
            ];
        }

        if (!$hasInitialOffer) {
            return [
                'eligible' => false,
                'message' => 'Usted no puede participar en la puja porque no ingresó su oferta inicial a tiempo.'
            ];
        }

        return [
            'eligible' => true,
            'message' => ''
        ];
    }

    public function downloadOfferPdf() {
        $productoId = $_GET['producto_id'] ?? null;
        $usuarioId = $_SESSION['user_id'];
        
        if (!$productoId) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "ID de producto requerido");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        require_once BASE_PATH . '/models/OfferSubmission.php';
        require_once BASE_PATH . '/services/OfferPdfGenerator.php';
        
        $offerSubmissionModel = new OfferSubmission();
        $offerDetail = $offerSubmissionModel->getByProductAndUser($productoId, $usuarioId);
        
        if (!$offerDetail) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "No se encontró información de oferta procesada");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        $product = $this->productModel->getProductById($productoId);
        if (!$product) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "Producto no encontrado");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        $user = $this->userModel->getUserById($usuarioId);
        if (!$user) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "Usuario no encontrado");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        $cpc = $this->cpcModel->getCPCById($product['cpc_id']);
        if (!$cpc) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "CPC no encontrado");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        $pdfInfo = OfferPdfGenerator::generate($product, $user, $cpc, $offerDetail);
        
        if (!$pdfInfo) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "Error al generar el PDF");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        // Servir el archivo PDF
        $fullPath = $pdfInfo['full_path'];
        
        if (!file_exists($fullPath)) {
            if ($this->isAjaxRequest()) {
                $this->sendJsonResponse(false, "El archivo PDF no existe");
            } else {
                header('Location: ' . BASE_URL . 'participant/dashboard');
            }
            return;
        }

        // Limpiar cualquier output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Establecer headers para descargar el PDF
        header('Content-Type: application/pdf');
        header('Content-Length: ' . filesize($fullPath));
        header('Content-Disposition: attachment; filename="' . $pdfInfo['file_name'] . '"');
        header('Cache-Control: private, max-age=3600');

        // Leer y enviar el archivo
        readfile($fullPath);
        exit;
    }
}