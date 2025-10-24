<?php
// Verificar que el producto esté disponible
if (!isset($product) || !$product) {
    echo '<div class="error">Producto no encontrado</div>';
    return;
}

// Obtener el estado actual del producto
$currentStateCode = $product['estado_id'] ?? 1;
$isReadOnly = false;

// Verificar si la fase está en modo solo lectura
if ($currentStateCode != 1) { // Si no es "Preguntas y Respuestas"
    $isReadOnly = true;
}

// Obtener ID del producto
$productId = isset($product['id']) ? $product['id'] : '1';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preguntas y Respuestas - Producto <?php echo htmlspecialchars($product['codigo']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 1.8em;
            color: #333;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .preguntas-respuestas {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .pregunta-form {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
        }
        .pregunta-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
            min-height: 80px;
            box-sizing: border-box;
        }
        .pregunta-form .char-counter {
            text-align: right;
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        .pregunta-form .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 10px;
        }
        .pregunta-form .btn-submit:hover {
            background-color: #0056b3;
        }
        .preguntas-list {
            margin-top: 30px;
        }
        .pregunta-item {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .pregunta-header {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
            color: #555;
            margin-bottom: 5px;
        }
        .pregunta-texto {
            font-size: 1em;
            color: #333;
            margin-bottom: 10px;
        }
        .respuesta {
            background-color: #e9f7ef;
            border-left: 4px solid #28a745;
            padding: 10px;
            margin-top: 10px;
            font-size: 0.95em;
            color: #218838;
        }
        .sin-respuesta {
            font-style: italic;
            color: #888;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .debug-info {
            border: 1px solid yellow;
            padding: 10px;
            margin-bottom: 15px;
            background-color: #fffacd;
        }
        .btn-debug {
            background-color: #ffc107;
            color: #333;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            margin-right: 5px;
        }
        .btn-debug:hover {
            background-color: #e0a800;
        }
        #debug-log {
            max-height: 150px;
            overflow-y: auto;
            background-color: #f0f0f0;
            padding: 5px;
            margin-top: 10px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Preguntas y Respuestas - Producto: <?php echo htmlspecialchars($product['codigo']); ?></h1>
            <a href="/subs/participant/view-product/<?php echo $productId; ?>" class="btn-back">Regresar al Producto</a>
        </div>

        <div class="preguntas-respuestas" id="pyr-container">
            <div class="debug-info">
                <h4>Debug PYR System</h4>
                <button id="debug-get-btn" class="btn-debug">Test GET Questions</button>
                <button id="debug-submit-btn" class="btn-debug">Test Submit Question</button>
                <div id="debug-log"></div>
            </div>

            <?php if (!$isReadOnly): ?>
            <form id="pregunta-form" class="pregunta-form">
                <div class="form-group">
                    <label for="pregunta">Escriba su pregunta:</label>
                    <textarea id="pregunta" name="pregunta" maxlength="500" placeholder="Escriba su pregunta aquí (máximo 500 caracteres)" required></textarea>
                    <div class="char-counter">
                        <span id="char-count">0</span>/500 caracteres
                    </div>
                </div>
                <button type="submit" class="btn-submit">Enviar Pregunta</button>
            </form>
            <?php else: ?>
            <div class="read-only-message">
                <p>Esta fase está en modo de solo lectura. No puede enviar nuevas preguntas.</p>
            </div>
            <?php endif; ?>

            <div class="preguntas-list">
                <h4>Preguntas Realizadas</h4>
                <div id="preguntas-container">
                    Cargando preguntas...
                </div>
            </div>
        </div>
    </div>

    <script>
        console.log('PYR-TEST - Page loaded');
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('PYR-TEST - DOM Content Loaded');
            initializePYRContent();
        });

        function getProductIdFromURL() {
            const pathParts = window.location.pathname.split('/');
            const productId = pathParts[pathParts.length - 1];
            return productId || '<?php echo htmlspecialchars($productId); ?>';
        }

        function initializePYRContent() {
            console.log('PYR-TEST - Initializing PYR content');
            
            const productId = getProductIdFromURL();
            console.log('PYR-TEST - Product ID:', productId);
            
            initializeDebugButtons();
            initializePreguntaForm();
            loadPreguntas(1);
        }

        function initializeDebugButtons() {
            console.log('PYR-TEST - Initializing debug buttons');
            
            const debugGetBtn = document.getElementById('debug-get-btn');
            if (debugGetBtn) {
                debugGetBtn.onclick = function() {
                    console.log('PYR-TEST - Debug GET button clicked');
                    testGetQuestions();
                };
            }
            
            const debugSubmitBtn = document.getElementById('debug-submit-btn');
            if (debugSubmitBtn) {
                debugSubmitBtn.onclick = function() {
                    console.log('PYR-TEST - Debug Submit button clicked');
                    testSubmitQuestion();
                };
            }
        }

        function initializePreguntaForm() {
            const form = document.getElementById('pregunta-form');
            const textarea = form ? form.querySelector('textarea') : null;
            const charCount = document.getElementById('char-count');
            
            if (!form || !textarea) {
                console.log('PYR-TEST - Form or textarea not found');
                return;
            }
            
            console.log('PYR-TEST - Initializing pregunta form');
            
            textarea.oninput = function() {
                const count = this.value.length;
                if (charCount) {
                    charCount.textContent = count;
                    charCount.style.color = count > 450 ? '#ff6b6b' : '#666';
                }
            };
            
            form.onsubmit = function(e) {
                e.preventDefault();
                const pregunta = textarea.value.trim();
                
                if (pregunta.length === 0) {
                    alert('Por favor, escriba una pregunta');
                    return;
                }
                
                if (pregunta.length > 500) {
                    alert('La pregunta no puede exceder 500 caracteres');
                    return;
                }
                
                submitPregunta(pregunta);
                textarea.value = '';
                if (charCount) {
                    charCount.textContent = '0';
                    charCount.style.color = '#666';
                }
            };
        }

        function testGetQuestions() {
            console.log('PYR-TEST - Testing GET questions...');
            const productId = getProductIdFromURL();
            const url = `/subs/participant/get-questions?producto_id=${productId}&page=1&limit=5`;
            
            showDebugLog('Testing GET questions...');
            showDebugLog('GET URL: ' + url);
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('PYR-TEST - GET Response status:', response.status);
                showDebugLog('GET Response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                console.log('PYR-TEST - GET Response text:', text);
                showDebugLog('GET Response text: ' + text.substring(0, 200) + '...');
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        console.log('PYR-TEST - GET Questions successful:', data.data);
                        showDebugLog('GET Questions successful: ' + data.data.questions.length + ' questions');
                        displayPreguntas(data.data.questions);
                    } else {
                        console.error('PYR-TEST - GET Questions failed:', data.message);
                        showDebugLog('GET Questions failed: ' + data.message);
                    }
                } catch (e) {
                    console.error('PYR-TEST - GET Response parse error:', e);
                    showDebugLog('GET Response parse error: ' + e.message);
                }
            })
            .catch(error => {
                console.error('PYR-TEST - GET Questions error:', error);
                showDebugLog('GET Questions error: ' + error.message);
            });
        }

        function testSubmitQuestion() {
            console.log('PYR-TEST - Testing POST submit question...');
            const productId = getProductIdFromURL();
            const url = '/subs/participant/submit-question';
            
            showDebugLog('Testing POST submit question...');
            showDebugLog('POST URL: ' + url);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `producto_id=${productId}&pregunta=Test question from PYR-TEST`
            })
            .then(response => {
                console.log('PYR-TEST - POST Response status:', response.status);
                showDebugLog('POST Response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                console.log('PYR-TEST - POST Response text:', text);
                showDebugLog('POST Response text: ' + text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        console.log('PYR-TEST - POST Submit successful:', data.message);
                        showDebugLog('POST Submit successful: ' + data.message);
                    } else {
                        console.error('PYR-TEST - POST Submit failed:', data.message);
                        showDebugLog('POST Submit failed: ' + data.message);
                    }
                } catch (e) {
                    console.error('PYR-TEST - POST Response parse error:', e);
                    showDebugLog('POST Response parse error: ' + e.message);
                }
            })
            .catch(error => {
                console.error('PYR-TEST - POST Submit error:', error);
                showDebugLog('POST Submit error: ' + error.message);
            });
        }

        function submitPregunta(pregunta) {
            console.log('PYR-TEST - Submitting pregunta:', pregunta);
            const productId = getProductIdFromURL();
            const url = '/subs/participant/submit-question';
            
            showDebugLog('Submitting pregunta: ' + pregunta);
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `producto_id=${productId}&pregunta=${encodeURIComponent(pregunta)}`
            })
            .then(response => {
                console.log('PYR-TEST - Submit Response status:', response.status);
                showDebugLog('Submit Response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                console.log('PYR-TEST - Submit Response text:', text);
                showDebugLog('Submit Response text: ' + text);
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        console.log('PYR-TEST - Pregunta enviada exitosamente');
                        showDebugLog('Pregunta enviada exitosamente');
                        alert('Pregunta enviada exitosamente');
                        loadPreguntas(1);
                    } else {
                        console.error('PYR-TEST - Error al enviar pregunta:', data.message);
                        showDebugLog('Error al enviar pregunta: ' + data.message);
                        alert('Error al enviar pregunta: ' + data.message);
                    }
                } catch (e) {
                    console.error('PYR-TEST - Submit Response parse error:', e);
                    showDebugLog('Submit Response parse error: ' + e.message);
                    alert('Error al procesar respuesta del servidor');
                }
            })
            .catch(error => {
                console.error('PYR-TEST - Submit error:', error);
                showDebugLog('Submit error: ' + error.message);
                alert('Error de conexión al enviar pregunta');
            });
        }

        function loadPreguntas(page) {
            console.log('PYR-TEST - Loading preguntas, page:', page);
            const productId = getProductIdFromURL();
            const url = `/subs/participant/get-questions?producto_id=${productId}&page=${page}&limit=5`;
            
            showDebugLog('Loading preguntas, page: ' + page);
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('PYR-TEST - Load Response status:', response.status);
                showDebugLog('Load Response status: ' + response.status);
                return response.text();
            })
            .then(text => {
                console.log('PYR-TEST - Load Response text:', text);
                showDebugLog('Load Response text: ' + text.substring(0, 200) + '...');
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        console.log('PYR-TEST - Questions loaded successfully:', data.data);
                        showDebugLog('Questions loaded successfully: ' + data.data.questions.length + ' questions');
                        displayPreguntas(data.data.questions);
                    } else {
                        console.error('PYR-TEST - Load questions failed:', data.message);
                        showDebugLog('Load questions failed: ' + data.message);
                    }
                } catch (e) {
                    console.error('PYR-TEST - Load Response parse error:', e);
                    showDebugLog('Load Response parse error: ' + e.message);
                }
            })
            .catch(error => {
                console.error('PYR-TEST - Load questions error:', error);
                showDebugLog('Load questions error: ' + error.message);
            });
        }

        function displayPreguntas(questions) {
            console.log('PYR-TEST - Displaying questions:', questions);
            const container = document.getElementById('preguntas-container');
            if (!container) {
                console.log('PYR-TEST - Questions container not found');
                showDebugLog('Questions container not found');
                return;
            }
            
            if (questions.length === 0) {
                container.innerHTML = '<p>No hay preguntas aún.</p>';
                return;
            }
            
            let html = '';
            questions.forEach(question => {
                html += `
                    <div class="pregunta-item">
                        <div class="pregunta-header">
                            <strong>${question.nombre_usuario}</strong>
                            <span class="fecha">${question.fecha_pregunta}</span>
                        </div>
                        <div class="pregunta-texto">${question.pregunta}</div>
                        ${question.respuesta ? `
                            <div class="respuesta">
                                <strong>Respuesta:</strong> ${question.respuesta}
                            </div>
                        ` : '<div class="sin-respuesta">Sin respuesta aún</div>'}
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        function showDebugLog(message) {
            const debugLog = document.getElementById('debug-log');
            if (debugLog) {
                const timestamp = new Date().toLocaleTimeString();
                debugLog.innerHTML += '<div>' + timestamp + ': ' + message + '</div>';
                debugLog.scrollTop = debugLog.scrollHeight;
            }
        }
    </script>
</body>
</html>
