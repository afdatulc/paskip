<?php require 'vendor/autoload.php'; $template = new \PhpOffice\PhpWord\TemplateProcessor('storage/app/templates/template_bukti_tindak_lanjut.docx'); print_r($template->getVariables());
