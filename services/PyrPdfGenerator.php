<?php

/**
 * Generador de actas PDF para el módulo de Preguntas y Respuestas.
 *
 * Esta implementación usa un generador PDF simplificado orientado a texto,
 * suficiente para construir actas legibles sin dependencias externas.
 */
class PyrPdfGenerator
{
    /**
     * Genera el acta de PyR y devuelve metadatos del archivo creado.
     *
     * @param array $product    Información del producto
     * @param array $questions  Listado de preguntas con sus respuestas
     * @return array|null       Información del archivo generado o null si falla
     */
    public static function generate(array $product, array $questions)
    {
        if (empty($product['id'])) {
            return null;
        }

        $actasDir = BASE_PATH . '/uploads/pyr_actas';
        if (!is_dir($actasDir) && !mkdir($actasDir, 0775, true) && !is_dir($actasDir)) {
            return null;
        }

        $productCode = $product['codigo'] ?? ('producto_' . $product['id']);
        $fileName = sprintf('acta_pyr_producto_%d.pdf', (int)$product['id']);
        $fullPath = $actasDir . '/' . $fileName;
        $relativePath = 'uploads/pyr_actas/' . $fileName;

        $builder = new SimplePdfBuilder();
        $builder->addParagraph('Acta de Preguntas y Respuestas', [
            'font' => 'bold',
            'fontSize' => 16,
            'spacingAfter' => 12,
        ]);

        $builder->addParagraph('Producto', [
            'font' => 'bold',
            'fontSize' => 12,
            'spacingAfter' => 2,
        ]);

        $builder->addParagraph(sprintf('Código: %s', $productCode), [
            'fontSize' => 11,
        ]);

        if (!empty($product['objeto_proceso'])) {
            $builder->addParagraph(sprintf('Objeto del Proceso: %s', $product['objeto_proceso']), [
                'fontSize' => 11,
            ]);
        }

        if (!empty($product['entidad'])) {
            $builder->addParagraph(sprintf('Entidad Contratante: %s', $product['entidad']), [
                'fontSize' => 11,
            ]);
        }

        $builder->addParagraph(sprintf('Fecha de generación: %s', self::formatDate('now')), [
            'fontSize' => 11,
            'spacingAfter' => 12,
        ]);

        if (empty($questions)) {
            $builder->addParagraph('No existen preguntas registradas para este proceso.', [
                'fontSize' => 11,
            ]);
        } else {
            foreach ($questions as $index => $question) {
                $builder->addParagraph(sprintf('Pregunta %d', $index + 1), [
                    'font' => 'bold',
                    'fontSize' => 12,
                    'spacingAfter' => 4,
                ]);

                $builder->addParagraph(sprintf(
                    'Participante: %s',
                    $question['nombre_usuario'] ?? 'Desconocido'
                ), [
                    'fontSize' => 11,
                ]);

                if (!empty($question['fecha_pregunta'])) {
                    $builder->addParagraph(sprintf(
                        'Fecha de pregunta: %s',
                        self::formatDate($question['fecha_pregunta'])
                    ), [
                        'fontSize' => 11,
                    ]);
                }

                $builder->addParagraph('Pregunta:', [
                    'font' => 'bold',
                    'fontSize' => 11,
                ]);

                $builder->addParagraph($question['pregunta'] ?? '', [
                    'fontSize' => 11,
                    'spacingAfter' => 6,
                    'indent' => 10,
                ]);

                $builder->addParagraph('Respuesta:', [
                    'font' => 'bold',
                    'fontSize' => 11,
                ]);

                if (!empty($question['respuesta'])) {
                    $builder->addParagraph($question['respuesta'], [
                        'fontSize' => 11,
                        'indent' => 10,
                    ]);

                    if (!empty($question['fecha_respuesta'])) {
                        $builder->addParagraph(sprintf(
                            'Fecha de respuesta: %s',
                            self::formatDate($question['fecha_respuesta'])
                        ), [
                            'fontSize' => 10,
                            'spacingAfter' => 10,
                        ]);
                    } else {
                        $builder->addParagraph('Fecha de respuesta: Pendiente', [
                            'fontSize' => 10,
                            'spacingAfter' => 10,
                        ]);
                    }
                } else {
                    $builder->addParagraph('Respuesta pendiente.', [
                        'fontSize' => 11,
                        'indent' => 10,
                        'spacingAfter' => 10,
                    ]);
                }
            }
        }

        $builder->addParagraph('Fin del documento.', [
            'fontSize' => 10,
            'spacingBefore' => 10,
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

        return $date->format('d/m/Y H:i');
    }
}

/**
 * Generador PDF muy simplificado basado en texto.
 * Soporta múltiples páginas, dos variantes de fuente (regular y bold) y párrafos con ajustes básicos.
 */
class SimplePdfBuilder
{
    private $pageWidth;
    private $pageHeight;
    private $marginLeft = 50;
    private $marginRight = 50;
    private $marginTop = 50;
    private $marginBottom = 50;

    private $pages = [];
    private $currentPageIndex = -1;
    private $currentY = 0;

    public function __construct($pageWidth = 595.28, $pageHeight = 841.89)
    {
        $this->pageWidth = $pageWidth;
        $this->pageHeight = $pageHeight;
        $this->startNewPage();
    }

    public function addParagraph($text, array $options = [])
    {
        $text = (string)($text ?? '');
        $fontSize = $options['fontSize'] ?? 12;
        $font = $options['font'] ?? 'regular';
        $indent = $options['indent'] ?? 0;
        $spacingAfter = $options['spacingAfter'] ?? 0;
        $spacingBefore = $options['spacingBefore'] ?? 0;
        $lineHeight = $options['lineHeight'] ?? ($fontSize * 1.4);

        if ($spacingBefore > 0) {
            $this->applySpacing($spacingBefore);
        }

        $lines = $this->wrapText($text, $fontSize, $indent);

        if (empty($lines)) {
            $lines = [''];
        }

        foreach ($lines as $line) {
            if ($this->currentY - $lineHeight < $this->marginBottom) {
                $this->startNewPage();
            }

            $this->pages[$this->currentPageIndex]['lines'][] = [
                'text' => $line,
                'x' => $this->marginLeft + $indent,
                'y' => $this->currentY,
                'font' => $font === 'bold' ? 'F2' : 'F1',
                'fontSize' => $fontSize,
            ];

            $this->currentY -= $lineHeight;
        }

        if ($spacingAfter > 0) {
            $this->applySpacing($spacingAfter);
        }
    }

    public function output($filePath)
    {
        if (empty($this->pages)) {
            return false;
        }

        $objects = [];
        $kids = [];
        $totalPages = count($this->pages);
        $maxObjectId = 4 + ($totalPages * 2);

        $objects[1] = ['type' => 'dict', 'value' => '<< /Type /Catalog /Pages 2 0 R >>'];
        $objects[3] = ['type' => 'dict', 'value' => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
        $objects[4] = ['type' => 'dict', 'value' => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>'];

        for ($i = 0; $i < $totalPages; $i++) {
            $contentObjId = 5 + ($i * 2);
            $pageObjId = $contentObjId + 1;
            $kids[] = sprintf('%d 0 R', $pageObjId);

            $contentStream = $this->buildContentStream($this->pages[$i]['lines']);
            $objects[$contentObjId] = [
                'type' => 'stream',
                'dict' => sprintf('<< /Length %d >>', strlen($contentStream)),
                'stream' => $contentStream,
            ];

            $objects[$pageObjId] = [
                'type' => 'dict',
                'value' => sprintf(
                    '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2F %.2F] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                    $this->pageWidth,
                    $this->pageHeight,
                    $contentObjId
                ),
            ];
        }

        $objects[2] = [
            'type' => 'dict',
            'value' => sprintf(
                '<< /Type /Pages /Kids [%s] /Count %d >>',
                implode(' ', $kids),
                $totalPages
            ),
        ];

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n", $id);

            if ($object['type'] === 'stream') {
                $pdf .= $object['dict'] . "\nstream\n" . $object['stream'] . "\nendstream\n";
            } else {
                $pdf .= $object['value'] . "\n";
            }

            $pdf .= "endobj\n";
        }

        $xrefPos = strlen($pdf);
        $pdf .= sprintf("xref\n0 %d\n", $maxObjectId + 1);
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $maxObjectId; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= sprintf("trailer\n<< /Size %d /Root 1 0 R >>\n", $maxObjectId + 1);
        $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

        return file_put_contents($filePath, $pdf) !== false;
    }

    private function startNewPage()
    {
        $this->pages[] = ['lines' => []];
        $this->currentPageIndex = count($this->pages) - 1;
        $this->currentY = $this->pageHeight - $this->marginTop;
    }

    private function applySpacing($height)
    {
        if ($this->currentY - $height < $this->marginBottom) {
            $this->startNewPage();
        } else {
            $this->currentY -= $height;
        }
    }

    private function wrapText($text, $fontSize, $indent)
    {
        $maxWidth = $this->pageWidth - $this->marginLeft - $this->marginRight - $indent;
        if ($maxWidth <= 0) {
            return [$text];
        }

        $approxCharWidth = $fontSize * 0.53;
        $maxChars = max(10, (int)floor($maxWidth / $approxCharWidth));

        $prepared = $this->prepareText($text);
        $wrapped = wordwrap($prepared, $maxChars, "\n", true);

        return explode("\n", $wrapped);
    }

    private function buildContentStream(array $lines)
    {
        $content = "BT\n";

        foreach ($lines as $line) {
            $content .= sprintf("/%s %.2F Tf\n", $line['font'], $line['fontSize']);
            $content .= sprintf("1 0 0 1 %.2F %.2F Tm\n", $line['x'], $line['y']);
            $content .= sprintf("(%s) Tj\n", $this->escapeText($line['text']));
        }

        $content .= "ET";

        return $content;
    }

    private function prepareText($text)
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
        if ($converted === false) {
            $converted = utf8_decode($text);
        }

        return $converted;
    }

    private function escapeText($text)
    {
        $text = $this->prepareText($text);
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );
    }
}

