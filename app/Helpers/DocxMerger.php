<?php

namespace App\Helpers;

use ZipArchive;
use Illuminate\Support\Facades\File;

class DocxMerger
{
    /**
     * Merge multiple .docx files into one master .docx file.
     *
     * @param array $files Array of absolute paths to .docx files.
     * @param string $outputPath Absolute path to the output .docx file.
     * @return bool True on success, false on failure.
     */
    public static function merge(array $files, string $outputPath): bool
    {
        if (empty($files)) {
            return false;
        }

        // Use the first file as the master template
        $masterFile = array_shift($files);
        
        // Ensure destination directory exists
        $dir = dirname($outputPath);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        if (!File::copy($masterFile, $outputPath)) {
            return false;
        }

        if (empty($files)) {
            return true; // Only one file, no need to merge
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath) !== true) {
            return false;
        }

        // 1. Read document.xml
        $documentXml = $zip->getFromName('word/document.xml');
        if ($documentXml === false) {
            $zip->close();
            return false;
        }

        // 2. Read document.xml.rels
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        if ($relsXml === false) {
            $zip->close();
            return false;
        }

        $altChunksXml = '';
        $chunkIndex = 1;

        foreach ($files as $file) {
            if (!file_exists($file)) continue;

            $chunkId = 'altChunkId' . $chunkIndex;
            $chunkPath = 'word/chunks/chunk' . $chunkIndex . '.docx';

            // Add the sub-document to the zip
            $zip->addFile($file, $chunkPath);

            // Add relationship
            $relNode = '<Relationship Id="' . $chunkId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/aFChunk" Target="chunks/chunk' . $chunkIndex . '.docx"/>';
            $relsXml = str_replace('</Relationships>', $relNode . '</Relationships>', $relsXml);

            // Prepare altChunk tag (add a page break before each new document)
            $altChunksXml .= '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
            $altChunksXml .= '<w:altChunk r:id="' . $chunkId . '"/>';

            $chunkIndex++;
        }

        // Insert altChunks right before the end of the body
        $documentXml = str_replace('</w:body>', $altChunksXml . '</w:body>', $documentXml);

        // Save modifications
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->addFromString('word/_rels/document.xml.rels', $relsXml);
        
        // Add Content_Types overrides for chunks
        $contentTypes = $zip->getFromName('[Content_Types].xml');
        if ($contentTypes !== false) {
            $overrideTags = '';
            for ($i = 1; $i < $chunkIndex; $i++) {
                $overrideTags .= '<Override PartName="/word/chunks/chunk' . $i . '.docx" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document"/>';
            }
            $contentTypes = str_replace('</Types>', $overrideTags . '</Types>', $contentTypes);
            $zip->addFromString('[Content_Types].xml', $contentTypes);
        }

        $zip->close();
        return true;
    }
}
