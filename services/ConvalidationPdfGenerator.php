<?php

require_once __DIR__ . '/PyrPdfGenerator.php';

class ConvalidationPdfGenerator
{
    public static function generate(array $product, array $user, array $convalidation, array $files)
    {
        if (empty($product['id']) || empty($user['id']) || empty($convalidation['id'])) {
            return null;
        }

        $pdfDir = BASE_PATH . '/uploads/convalidation_pdfs';
        if (!is_dir($pdfDir) && !mkdir($pdfDir, 0775, true) && !is_dir($pdfDir)) {
            return null;
        }

        $fileName = sprintf('convalidacion_producto_%d_usuario_%d.pdf', (int)$product['id'], (int)$user['id']);
        $fullPath = $pdfDir . '/' . $fileName;
        $relativePath = 'uploads/convalidation_pdfs/' . $fileName;

        $builder = new SimplePdfBuilder();
        $builder->addParagraph('Justificante de Convalidacion de Errores', [
            'font' => 'bold',
            'fontSize' => 16,
            'spacingAfter' => 10,
        ]);

        $builder->addParagraph(sprintf('Fecha de entrega: %s', self::formatDate($convalidation['created_at'] ?? 'now')), [
            'fontSize' => 11,
            'spacingAfter' => 12,
        ]);

        $builder->addParagraph('Datos del proceso', [
            'font' => 'bold',
            'fontSize' => 12,
            'spacingAfter' => 4,
        ]);
        $builder->addParagraph(sprintf('Codigo del proceso: %s', $product['codigo'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);
        $builder->addParagraph(sprintf('Entidad: %s', $product['entidad'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);
        $builder->addParagraph(sprintf('Objeto del proceso: %s', $product['objeto_proceso'] ?? 'N/A'), [
            'fontSize' => 11,
            'spacingAfter' => 12,
        ]);

        $builder->addParagraph('Datos del participante', [
            'font' => 'bold',
            'fontSize' => 12,
            'spacingAfter' => 4,
        ]);
        $builder->addParagraph(sprintf('Nombre: %s', $user['nombre_completo'] ?? 'N/A'), [
            'fontSize' => 11,
        ]);
        $builder->addParagraph(sprintf('Identificacion: %s', $user['cedula'] ?? 'N/A'), [
            'fontSize' => 11,
            'spacingAfter' => 12,
        ]);

        $builder->addParagraph('Detalle de la convalidacion', [
            'font' => 'bold',
            'fontSize' => 12,
            'spacingAfter' => 4,
        ]);
        $builder->addParagraph($convalidation['detalle_texto'] ?? '', [
            'fontSize' => 11,
            'spacingAfter' => 10,
            'indent' => 8,
        ]);

        $builder->addParagraph('Archivos cargados', [
            'font' => 'bold',
            'fontSize' => 12,
            'spacingAfter' => 4,
        ]);

        if (empty($files)) {
            $builder->addParagraph('No se registraron archivos adjuntos.', [
                'fontSize' => 11,
            ]);
        } else {
            foreach ($files as $file) {
                $builder->addParagraph('- ' . ($file['nombre_archivo'] ?? 'Archivo'), [
                    'fontSize' => 11,
                ]);
            }
        }

        $builder->addParagraph('La convalidacion del proceso ha sido cargada y entregada de forma satisfactoria.', [
            'fontSize' => 11,
            'spacingBefore' => 12,
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

