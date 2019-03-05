<?php
namespace App\Classes\System;

class ToolExcel {
    // Vars
    private $self;
    private $encoding;
    
    private $fileName;
    private $fileOptions;
    private $fileElements;
    private $file;
    
    private $sheets;
    
    // Properties
    
    // Functions public
    public function __construct($self = null, $encoding = "UTF-8") {
        $this->self = $self;
        $this->encoding = $encoding;
        
        $this->fileName = "no_name";
        
        $this->fileOptions = Array(
            'encoding' => $encoding
        );
        
        $this->fileElements = Array(
            '_rels/.rels' => "<Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\">"
                                . "<Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument\" Target=\"xl/workbook.xml\"/>"
                            . "</Relationships>",
            
            'xl/_rels/workbook.xml.rels' => "<Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\">"
                                                . "<Relationship Id=\"rId2\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/>"
                                                . "{workbookRelationshipSheet}"
                                            . "</Relationships>",
            
            'xl/workbook.xml' => "<workbook xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\">"
                                    . "<workbookPr defaultThemeVersion=\"124226\"/>"
                                    . "<sheets>"
                                        . "{workbookSheetName}"
                                    . "</sheets>"
                                . "</workbook>",
            
            'xl/styles.xml' => "<styleSheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:mc=\"http://schemas.openxmlformats.org/markup-compatibility/2006\" mc:Ignorable=\"x14ac\" xmlns:x14ac=\"http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac\">"
                                    . "<fonts count=\"2\" x14ac:knownFonts=\"1\">"
                                        . "<font><sz val=\"11\"/><color theme=\"1\"/><name val=\"Calibri\"/><family val=\"2\"/><scheme val=\"minor\"/></font>"
                                        . "<font><b/><sz val=\"11\"/><color theme=\"1\"/><name val=\"Calibri\"/><family val=\"2\"/><charset val=\"204\"/><scheme val=\"minor\"/></font>"
                                    . "</fonts>"
                                    . "<fills count=\"3\">"
                                        . "<fill>"
                                            . "<patternFill patternType=\"none\"/>"
                                        . "</fill>"
                                        . "<fill>"
                                            . "<patternFill patternType=\"gray125\"/>"
                                        . "</fill>"
                                        . "<fill>"
                                            . "<patternFill patternType=\"solid\">"
                                                . "<fgColor theme=\"0\" tint=\"-0.14999847407452621\"/>"
                                                . "<bgColor indexed=\"64\"/>"
                                            . "</patternFill>"
                                        . "</fill>"
                                    . "</fills>"
                                    . "<borders count=\"1\">"
                                        . "<border></border>"
                                    . "</borders>"
                                    . "<cellXfs count=\"2\">"
                                        . "<xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" />"
                                        . "<xf numFmtId=\"0\" fontId=\"1\" fillId=\"1\" />"
                                    . "</cellXfs>"
                                    . "<cellStyles count=\"1\">"
                                        . "<cellStyle name=\"Normal\" xfId=\"0\" builtinId=\"0\"/>"
                                    . "</cellStyles>"
                                . "</styleSheet>",
            
            '[Content_Types].xml' => "<Types xmlns=\"http://schemas.openxmlformats.org/package/2006/content-types\">"
                                        . "<Default Extension=\"rels\" ContentType=\"application/vnd.openxmlformats-package.relationships+xml\"/>"
                                        . "<Default Extension=\"xml\" ContentType=\"application/xml\"/>"
                                            . "<Override PartName=\"/xl/workbook.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml\"/>"
                                            . "<Override PartName=\"/xl/styles.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml\"/>"
                                            . "{ContentTypeSheet}"
                                    . "</Types>"
        );
        
        $this->file = null;
        
        $this->sheets = Array();
    }
    
    public function fileName($fileName) {
        $this->fileName = trim($fileName) . ".xlsx";
    }
    
    public function readCsv($path, $separator) {
        $elements = Array();
        
        $index = 0;
        
        if (file_exists($path) == true) {
            if (($handle = fopen($path, "r")) !== false) {
                while (($cell = fgetcsv($handle, 0, $separator)) !== false) {
                    if ($this->self == null)
                        $elements['items'][] = $cell;
                    else {
                        $result = $this->self->toolExcelCsvCallback($path, $index, $cell);
                        
                        if ($result == false)
                            break;
                    }
                    
                    $index ++;
                }

                fclose($handle);
            }
        }
        
        return $elements;
    }
    
    public function convertCsvInXlsx($elements) {
        $this->createSheet("Converted", $elements);
    }
    
    public function createSheet($label, $elements, $options = Array()) {
        $newOptions = array_merge($this->fileOptions, $options);
        
        $count = count($this->sheets);
        
        $worksheetName = "sheet$count.xml";
        $id = $count + 3;
        
        $relationship = "<Relationship Id=\"rId$id\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/$worksheetName\"/>{workbookRelationshipSheet}";
        $name = "<sheet name=\"$label\" sheetId=\"$id\" r:id=\"rId$id\"/>{workbookSheetName}";
        $contentType = "<Override PartName=\"/xl/worksheets/$worksheetName\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>{ContentTypeSheet}";
        $worksheet = "<worksheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\" xmlns:mc=\"http://schemas.openxmlformats.org/markup-compatibility/2006\" mc:Ignorable=\"x14ac\" xmlns:x14ac=\"http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac\">"
                        . "<sheetData>" . $this->row($elements, $newOptions) . "</sheetData>"
                    . "</worksheet>";
        
        $this->fileElements['xl/_rels/workbook.xml.rels'] = str_replace("{workbookRelationshipSheet}", $relationship, $this->fileElements['xl/_rels/workbook.xml.rels']);
        $this->fileElements['xl/workbook.xml'] = str_replace("{workbookSheetName}", $name, $this->fileElements['xl/workbook.xml']);
        $this->fileElements['[Content_Types].xml'] = str_replace("{ContentTypeSheet}", $contentType, $this->fileElements['[Content_Types].xml']);
        $this->fileElements['xl/worksheets/' . $worksheetName] = $worksheet;
        
        $this->sheets[] = $worksheetName;
    }
    
    public function save($path) {
        $zipArchive = new \ZipArchive();
        
        $zipArchive->open("$path/{$this->fileName}", \ZIPARCHIVE::CREATE);
        
        foreach ($this->fileElements as $key => $value) {
            $newValue = "<?xml version=\"1.0\" encoding=\"{$this->encoding}\" standalone=\"yes\"?>$value";
            
            if ($key == "xl/_rels/workbook.xml.rels")
                $newValue = str_replace("{workbookRelationshipSheet}", "", $newValue);
            else if ($key == "xl/workbook.xml")
                $newValue = str_replace("{workbookSheetName}", "", $newValue);
            else if ($key == "[Content_Types].xml")
                $newValue = str_replace("{ContentTypeSheet}", "", $newValue);
            
            $zipArchive->addFromString($key, $newValue);
        }
        
        $zipArchive->close();
        
        $this->file = "$path/{$this->fileName}";
    }
    
    public function download() {
        /*$this->save(__DIR__ . "/");
        
        ob_clean();
        
        header("Content-Disposition: attachment; filename=" . basename($this->file));
        header("Content-Length: " . filesize($this->file));
        header("Content-Type: application/vnd.openxmlformats-package.relationships+xml");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        readfile($this->file);
        
        if (file_exists($this->file) == true)
            unlink($this->file);
        
        exit;*/
        
        $this->save(__DIR__ . "/");
        
        ob_start();
        header("Content-Disposition: attachment; filename=" . basename($this->file));
        header("Content-Length: " . filesize($this->file));
        header("Content-Type: application/vnd.openxmlformats-package.relationships+xml");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        readfile($this->file);
        
        $obContent = ob_get_contents();
        ob_end_clean();

        $fileInfo = new \finfo(FILEINFO_MIME);
        $fileMimeContentType = $fileInfo->buffer($obContent) . PHP_EOL;
        $fileMimeContentTypeExplode = explode(";", $fileMimeContentType);
        
        if (file_exists($this->file) == true)
            unlink($this->file);
    }
    
    // Functions private
    private function row($elements, $options) {
        $result = "";
        
        if (count($elements['labels']) > 0) {
            $result .= "<row>";
            
            foreach ($elements['labels'] as $key => $value) {
                if (is_numeric($value) == true)
                    $result .= "<c s=\"1\" t=\"inlineStr\"><is><t>" . trim($value) . "</t></is></c>";
                else {
                    if (defined("ENT_XML1") == true)
                        $newValue = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, $options['encoding']);
                    else
                        $newValue = htmlspecialchars($value);

                    $result .= "<c s=\"1\" t=\"inlineStr\"><is><t>$newValue</t></is></c>";
                }
            }
            
            $result .= "</row>";
        }
        
        foreach ($elements['items'] as $key => $value) {
            $result .= "<row>";
            
            foreach ($value as $keySub => $valueSub) {
                if (is_numeric($valueSub) == true)
                    $result .= "<c><v>" . trim($valueSub) . "</v></c>";
                else {
                    if (defined("ENT_XML1") == true)
                        $newValue = htmlspecialchars($valueSub, ENT_QUOTES | ENT_XML1, $options['encoding']);
                    else
                        $newValue = htmlspecialchars($valueSub);
                    
                    $result .= "<c t=\"inlineStr\"><is><t>$newValue</t></is></c>";
                }
            }
            
            $result .= "</row>";
        }
        
        return $result;
    }
}
