<?php

// Intentar cargar autoloader de Composer si existe
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

/**
 * Generador de PDF para propuestas de ofertas usando HTML/CSS.
 * 
 * Genera un documento PDF con la información completa de la oferta del participante
 * usando HTML y CSS para un mejor formato visual.
 */
class OfferPdfGenerator
{
    /**
     * Genera el PDF de la oferta y devuelve metadatos del archivo creado.
     *
     * @param array $product    Información del producto
     * @param array $user       Información del usuario
     * @param array $cpc        Información del CPC
     * @param array $offerDetail Información de la oferta (ofertas_detalle)
     * @return array|null       Información del archivo generado o null si falla
     */
    public static function generate(array $product, array $user, array $cpc, array $offerDetail)
    {
        if (empty($product['id']) || empty($user['id']) || empty($offerDetail)) {
            return null;
        }

        $offersDir = BASE_PATH . '/uploads/offer_pdfs';
        if (!is_dir($offersDir) && !mkdir($offersDir, 0775, true) && !is_dir($offersDir)) {
            return null;
        }

        $fileName = sprintf('propuesta_oferta_producto_%d_usuario_%d.pdf', (int)$product['id'], (int)$user['id']);
        $fullPath = $offersDir . '/' . $fileName;
        $relativePath = 'uploads/offer_pdfs/' . $fileName;

        // Generar HTML
        $html = self::generateHtml($product, $user, $cpc, $offerDetail);

        // Convertir HTML a PDF usando DomPDF
        try {
            // Verificar si DomPDF está disponible
            $dompdfClass = 'Dompdf\Dompdf';
            if (class_exists($dompdfClass)) {
                // Usar DomPDF
                $dompdf = new $dompdfClass();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $output = $dompdf->output();
                file_put_contents($fullPath, $output);
            } else {
                // Fallback: usar SimplePdfBuilder si DomPDF no está disponible
                error_log("DomPDF no disponible, usando fallback SimplePdfBuilder");
                return self::generateWithFallback($product, $user, $cpc, $offerDetail, $fullPath, $relativePath, $fileName);
            }
        } catch (Exception $e) {
            error_log("Error generando PDF con DomPDF: " . $e->getMessage());
            // Intentar con fallback
            return self::generateWithFallback($product, $user, $cpc, $offerDetail, $fullPath, $relativePath, $fileName);
        } catch (Error $e) {
            error_log("Error fatal generando PDF: " . $e->getMessage());
            // Intentar con fallback
            return self::generateWithFallback($product, $user, $cpc, $offerDetail, $fullPath, $relativePath, $fileName);
        }

        return [
            'full_path' => $fullPath,
            'relative_path' => $relativePath,
            'file_name' => $fileName,
        ];
    }

    /**
     * Genera el HTML del documento
     */
    private static function generateHtml(array $product, array $user, array $cpc, array $offerDetail)
    {
        $fechaActual = self::formatDate('now');
        $cpcCodigo = $cpc['codigo'] ?? 'N/A';
        $cpcDescripcion = $cpc['descripcion'] ?? 'N/A';
        $ofertaInicialFormatted = number_format((float)($offerDetail['oferta_inicial_user'] ?? 0), 2, ',', '.');
        $precioFormatted = '$' . $ofertaInicialFormatted;
        
        $descripcion = $offerDetail['descripcion'] ?? 'N/A';
        if (strlen($descripcion) > 350) {
            $descripcion = substr($descripcion, 0, 347) . '...';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            margin: 2.5cm 3cm;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            padding: 0 15px;
            background: #fff;
            margin: 0;
        }
        
        /* Propuesta - H4, centrado, parte superior */
        .propuesta-header {
            text-align: center;
            font-size: 14pt;
            font-weight: normal;
            margin-bottom: 10px;
            color: #000;
        }
        
        /* Título principal - H2, centrado, color azul */
        .main-title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 20px;
            color: #0066cc;
        }
        
        /* Sección informativa con 4 espacios equitativos */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .info-item {
            display: table-cell;
            width: 25%;
            padding: 8px;
            vertical-align: top;
            border-right: 1px solid #ddd;
        }
        
        .info-item:last-child {
            border-right: none;
        }
        
        .info-label {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 4px;
            color: #333;
        }
        
        .info-value {
            font-size: 10pt;
            color: #000;
        }
        
        /* Línea amarilla */
        .yellow-line {
            border-top: 3px solid #ffd700;
            margin: 15px 0;
            width: 100%;
        }
        
        /* Subtítulo H3 */
        .section-h3 {
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
            color: #000;
        }
        
        /* Línea delgada */
        .thin-line {
            border-top: 0.5px solid #ccc;
            margin: 8px 0;
            width: 100%;
        }
        
        /* Línea normal (1px) */
        .normal-line {
            border-top: 1px solid #000;
            margin: 10px 0;
            width: 100%;
        }
        
        /* Detalles con tonalidad azul claro */
        .detail-section {
            margin: 15px 0;
        }
        
        .detail-item {
            padding: 8px 0;
            border-bottom: 0.5px solid #ddd;
            color: #4a90e2;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: bold;
            font-size: 11pt;
            color: #4a90e2;
            display: inline-block;
            min-width: 200px;
        }
        
        .detail-value {
            font-size: 11pt;
            color: #4a90e2;
        }
        
        /* Secciones normales */
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            margin-top: 20px;
            color: #000;
        }
        
        .section-content {
            font-size: 11pt;
            margin-bottom: 6px;
            line-height: 1.5;
            color: #000;
        }
        
        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 15px 0;
            border: 1px solid #000;
        }
        
        table th,
        table td {
            border: 1px solid #000;
            padding: 8px 6px;
            text-align: left;
            font-size: 10pt;
            vertical-align: middle;
        }
        
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #000;
        }
        
        table tbody tr {
            background-color: #fff;
        }
        
        table tfoot tr.total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        
        .footer-text {
            margin-top: 30px;
            font-size: 10pt;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Propuesta - H4, centrado -->
    <div class="propuesta-header">Propuesta</div>
    
    <!-- Título principal - H2, centrado, azul -->
    <div class="main-title">Sistema de Simulación de Subasta Inversa</div>
    
    <!-- Sección informativa con 4 espacios equitativos -->
    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Fecha actual | Hora actual</div>
            <div class="info-value">{$fechaActual}</div>
        </div>
        <div class="info-item">
            <div class="info-label">RUC:</div>
            <div class="info-value">{$user['cedula']}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Empresa:</div>
            <div class="info-value">{$user['nombre_completo']}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Usuario:</div>
            <div class="info-value">{$user['nombre_completo']}</div>
        </div>
    </div>
    
    <!-- Línea amarilla -->
    <div class="yellow-line"></div>
    
    <!-- Subtítulo H3 -->
    <div class="section-h3">>>PROPUESTA SUBASTAS INVERSA ELECTRÓNICA</div>
    
    <!-- Línea delgada -->
    <div class="thin-line"></div>
    
    <!-- Línea normal (1px) -->
    <div class="normal-line"></div>
    
    <!-- Detalles con tonalidad azul claro, separados por líneas de 0.5px -->
    <div class="detail-section">
        <div class="detail-item">
            <span class="detail-label">Entidad Contratante:</span>
            <span class="detail-value">{$product['entidad']}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Objeto del Proceso:</span>
            <span class="detail-value">{$product['objeto_proceso']}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Código:</span>
            <span class="detail-value">{$product['codigo']}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Tipo de Compra:</span>
            <span class="detail-value">{$product['tipo_compra']}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Tipo de Contratación:</span>
            <span class="detail-value">Subasta Inversa Electrónica<!--{$product['tipo_contratacion']}--></span>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Detalle: Bien/Obra/Servicio</div>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Bien/Obra/Servicio</th>
                    <th style="text-align: center;">Cant.</th>
                    <th>Unidad Medida</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>CPC: {$cpcCodigo}</td>
                    <td>{$cpcDescripcion}</td>
                    <td style="text-align: center;">1</td>
                    <td>Unidad</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <div class="section-title">Ingreso de ofertas</div>
    </div>
    
    <div class="detail-section">
        <div class="detail-item">
            <span class="detail-label">Tiempo Entrega Propuesto Plazo (días):</span>
            <span class="detail-value">{$offerDetail['tiempo_entrega']}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Tiempo de Garantía (Meses) (Sí Aplica):</span>
            <span class="detail-value">{$offerDetail['plazo_oferta']}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Razón de Aceptación (max 350 caracteres):</span>
            <span class="detail-value">{$descripcion}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Oferta Detallada</div>
        <table>
            <thead>
                <tr>
                    <th style="text-align: center;">Ítem</th>
                    <th>CPC</th>
                    <th>Bien/Servicio</th>
                    <th style="text-align: center;">Cantidad</th>
                    <th>Unidad</th>
                    <th style="text-align: right;">Precio Unitario</th>
                    <th style="text-align: right;">Precio total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>{$cpcCodigo}</td>
                    <td>{$cpcDescripcion}</td>
                    <td style="text-align: center;">1</td>
                    <td>Unidad</td>
                    <td style="text-align: right;">{$precioFormatted}</td>
                    <td style="text-align: right;">{$precioFormatted}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Total</td>
                    <td></td>
                    <td></td>
                    <td style="text-align: center;">1</td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;">{$precioFormatted}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <div class="footer-text">
        Documento educativo, sin validéz legal.
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Fallback: genera PDF usando SimplePdfBuilder si DomPDF no está disponible
     */
    private static function generateWithFallback(array $product, array $user, array $cpc, array $offerDetail, $fullPath, $relativePath, $fileName)
    {
        require_once __DIR__ . '/PyrPdfGenerator.php';
        
        $builder = new SimplePdfBuilder();
        
        // Título principal
        $builder->addParagraph('Sistema Oficial de Contratación Pública', [
            'font' => 'bold',
            'fontSize' => 16,
            'spacingAfter' => 8,
        ]);
        
        $builder->addParagraph('Propuesta', [
            'font' => 'bold',
            'fontSize' => 14,
            'spacingAfter' => 12,
        ]);

        // Fecha y hora actual
        $fechaActual = self::formatDate('now');
        $builder->addParagraph($fechaActual, [
            'fontSize' => 11,
            'spacingAfter' => 12,
        ]);

        // Información del usuario
        $builder->addParagraph('Datos del Participante', [
            'font' => 'bold',
            'fontSize' => 12,
            'spacingBefore' => 8,
            'spacingAfter' => 4,
        ]);

        $builder->addParagraph(sprintf('RUC: %s', $user['cedula'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);

        $builder->addParagraph(sprintf('Empresa: %s', $user['nombre_completo'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);

        $builder->addParagraph(sprintf('Usuario: %s', $user['nombre_completo'] ?? 'N/A'), [
            'fontSize' => 11,
            'spacingAfter' => 12,
        ]);

        // Título de sección
        $builder->addParagraph('>>PROPUESTA SUBASTAS INVERSA ELECTRÓNICA', [
            'font' => 'bold',
            'fontSize' => 12,
            'spacingBefore' => 8,
            'spacingAfter' => 8,
        ]);

        // Información del proceso
        $builder->addParagraph('Detalles del Proceso de Contratación', [
            'font' => 'bold',
            'fontSize' => 11,
            'spacingAfter' => 4,
        ]);

        $builder->addParagraph(sprintf('Entidad Contratante: %s', $product['entidad'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);

        $builder->addParagraph(sprintf('Objeto de Proceso de Contratación: %s', $product['objeto_proceso'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);

        $builder->addParagraph(sprintf('Código: %s', $product['codigo'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);

        $builder->addParagraph(sprintf('Tipo de Compra: %s', $product['tipo_compra'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);

        $builder->addParagraph(sprintf('Tipo de Contratación: %s', $product['tipo_contratacion'] ?? 'N/A'), [
            'fontSize' => 11,
            'spacingAfter' => 12,
        ]);

        // Tabla de detalle: Bien/Obra/Servicio
        $builder->addParagraph('Detalle: Bien/Obra/Servicio', [
            'font' => 'bold',
            'fontSize' => 11,
            'spacingBefore' => 8,
            'spacingAfter' => 6,
        ]);

        $cpcCodigo = $cpc['codigo'] ?? 'N/A';
        $cpcDescripcion = $cpc['descripcion'] ?? 'N/A';
        
        $headerLine = 'Categoría | Bien/Obra/Servicio | Cant. | Unidad Medida';
        $builder->addParagraph($headerLine, [
            'font' => 'bold',
            'fontSize' => 10,
            'spacingAfter' => 4,
        ]);

        $categoriaText = 'CPC: ' . $cpcCodigo;
        $dataLine = $categoriaText . ' | ' . $cpcDescripcion . ' | 1 | Unidad';
        $builder->addParagraph($dataLine, [
            'fontSize' => 10,
            'spacingAfter' => 8,
        ]);

        // Sección: Ingreso de ofertas
        $builder->addParagraph('Ingreso de ofertas', [
            'font' => 'bold',
            'fontSize' => 11,
            'spacingBefore' => 8,
            'spacingAfter' => 4,
        ]);

        $builder->addParagraph(sprintf('Tiempo Entrega Propuesto Plazo (días): %s', $offerDetail['tiempo_entrega'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);

        $builder->addParagraph(sprintf('Tiempo de Garantía (Meses) (Sí Aplica): %s', $offerDetail['plazo_oferta'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);

        $descripcion = $offerDetail['descripcion'] ?? 'N/A';
        if (strlen($descripcion) > 350) {
            $descripcion = substr($descripcion, 0, 347) . '...';
        }
        $builder->addParagraph(sprintf('Razón de Aceptación (max 350 caracteres): %s', $descripcion), [
            'fontSize' => 11,
            'spacingAfter' => 12,
        ]);

        // Tabla detallada de oferta
        $builder->addParagraph('Oferta Detallada', [
            'font' => 'bold',
            'fontSize' => 11,
            'spacingBefore' => 8,
            'spacingAfter' => 6,
        ]);

        $headerDetallada = 'Ítem | CPC | Bien/Servicio | Cantidad | Unidad | Precio Unitario | Precio total';
        $builder->addParagraph($headerDetallada, [
            'font' => 'bold',
            'fontSize' => 10,
            'spacingAfter' => 4,
        ]);

        $ofertaInicialFormatted = number_format((float)($offerDetail['oferta_inicial_user'] ?? 0), 2, ',', '.');
        $precioFormatted = '$' . $ofertaInicialFormatted;
        
        $dataDetallada = '1 | ' . $cpcCodigo . ' | ' . $cpcDescripcion . ' | 1 | Unidad | ' . $precioFormatted . ' | ' . $precioFormatted;
        $builder->addParagraph($dataDetallada, [
            'fontSize' => 10,
            'spacingAfter' => 6,
        ]);

        $totalLine = 'Total | | | 1 | | | ' . $precioFormatted;
        $builder->addParagraph($totalLine, [
            'font' => 'bold',
            'fontSize' => 10,
            'spacingAfter' => 12,
        ]);

        $builder->addParagraph('Fin del documento.', [
            'fontSize' => 10,
            'spacingBefore' => 20,
        ]);

        if (!$builder->output($fullPath)) {
            return null;
        }

        return [
            'full_path' => $fullPath,
            'relative_path' => $relativePath,
            'file_name' => $fileName,
        ];
    }

    private static function formatDate($value)
    {
        if ($value === 'now') {
            $date = new DateTime();
        } else {
            try {
                $date = new DateTime($value);
            } catch (Exception $e) {
                $date = new DateTime();
            }
        }

        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        
        $mes = $meses[(int)$date->format('n')] ?? '';
        $dia = $date->format('d');
        $anio = $date->format('Y');
        $hora = $date->format('H:i');
        
        return sprintf('%s %s de %s | %s', $dia, $mes, $anio, $hora);
    }
}
