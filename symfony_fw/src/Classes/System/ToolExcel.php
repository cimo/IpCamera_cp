<?php
namespace App\Classes\System;

class ToolExcel {
    // Vars
    private $encoding;
    
    private $file;
    private $fileName;
    private $fileOptions;
    private $fileElements;
    
    private $sheets;
    private $sheetData;
    private $worksheetName;
    
    // Properties
    
    // Functions public
    public function __construct($self = null, $encoding = "UTF-8") {
        $this->self = $self;
        $this->encoding = $encoding;
        
        $this->file = null;
        
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
        
        $this->sheets = Array();
        $this->sheetData = Array();
        $this->worksheetName = "";
    }
    
    public function fileName($fileName) {
        $this->fileName = trim($fileName) . ".xlsx";
    }
    
    public function totalLineCsv($path, $separator) {
        $index = 0;
        
        if (file_exists($path) == true) {
            if (($handle = fopen($path, "r")) !== false) {
                while (($cell = fgetcsv($handle, 0, $separator)) !== false) {
                    $index ++;
                }
                
                fclose($handle);
            }
        }
        
        return $index;
    }
    
    public function readCsv($path, $separator, $extras = Array()) {
        $result = false;
        
        if (file_exists($path) == true) {
            if (($handle = fopen($path, "r")) !== false) {
                $index = 0;
                
                while (($cell = fgetcsv($handle, 0, $separator)) !== false) {
                    if ($this->self == null)
                        $result = $this->populateSheetElements($index, $cell, $extras);
                    else
                        $result = $this->self->toolExcelCsvCallback($path, $index, $cell, $extras);
                    
                    if ($result == false)
                        break;
                    
                    $index ++;
                }
                
                fclose($handle);
            }
        }
        
        return $result;
    }
    
    public function createSheet($label) {
        $count = count($this->sheets);
        
        $this->worksheetName = "sheet{$count}.xml";
        $id = $count + 3;
        
        $relationship = "<Relationship Id=\"rId{$id}\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/{$this->worksheetName}\"/>{workbookRelationshipSheet}";
        $name = "<sheet name=\"$label\" sheetId=\"$id\" r:id=\"rId{$id}\"/>{workbookSheetName}";
        $contentType = "<Override PartName=\"/xl/worksheets/{$this->worksheetName}\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>{ContentTypeSheet}";
        $worksheet = "<worksheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\" xmlns:mc=\"http://schemas.openxmlformats.org/markup-compatibility/2006\" mc:Ignorable=\"x14ac\" xmlns:x14ac=\"http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac\"><sheetData></sheetData></worksheet>";
        
        $this->fileElements['xl/_rels/workbook.xml.rels'] = str_replace("{workbookRelationshipSheet}", $relationship, $this->fileElements['xl/_rels/workbook.xml.rels']);
        $this->fileElements['xl/workbook.xml'] = str_replace("{workbookSheetName}", $name, $this->fileElements['xl/workbook.xml']);
        $this->fileElements['[Content_Types].xml'] = str_replace("{ContentTypeSheet}", $contentType, $this->fileElements['[Content_Types].xml']);
        $this->fileElements['xl/worksheets/' . $this->worksheetName] = $worksheet;
        
        $this->sheets[] = $this->worksheetName;
    }
    
    public function createSheetElements($elements, $extras = Array()) {
        if (count($this->sheets) == 0)
            return false;
        
        $options = array_merge($this->fileOptions, $extras);
        
        $index = 0;
        
        foreach($elements as $key => $value) {
            $this->sheetData[$this->worksheetName] .= $this->sheetData($index, $value, $options);
            
            $index ++;
        }
        
        return true;
    }
    
    public function save($path) {
        $zipArchive = new \ZipArchive();
        
        $zipArchive->open("$path/{$this->fileName}", \ZIPARCHIVE::CREATE);
        
        $worksheet = false;
        
        foreach ($this->fileElements as $key => $value) {
            $newValue = "<?xml version=\"1.0\" encoding=\"{$this->encoding}\" standalone=\"yes\"?>$value";
            
            if ($key == "xl/_rels/workbook.xml.rels")
                $newValue = str_replace("{workbookRelationshipSheet}", "", $newValue);
            else if ($key == "xl/workbook.xml")
                $newValue = str_replace("{workbookSheetName}", "", $newValue);
            else if ($key == "[Content_Types].xml")
                $newValue = str_replace("{ContentTypeSheet}", "", $newValue);
            else if (strpos($key, "xl/worksheets/") !== false && $worksheet == false) {
                $worksheet = true;
                
                foreach($this->sheets as $keySub => $valueSub) {
                    $tmp = "<worksheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\" xmlns:mc=\"http://schemas.openxmlformats.org/markup-compatibility/2006\" mc:Ignorable=\"x14ac\" xmlns:x14ac=\"http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac\">"
                            . "<sheetData>" . $this->sheetData[$valueSub] . "</sheetData>"
                        . "</worksheet>";
                    
                    $zipArchive->addFromString('xl/worksheets/' . $valueSub, $tmp);
                }
            }
            
            if ($worksheet == false)
                $zipArchive->addFromString($key, $newValue);
        }
        
        $zipArchive->close();
        
        $this->file = "$path/{$this->fileName}";
    }
    
    public function download() {
        $this->save(__DIR__);
        
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
        
        exit;
    }
    
    // Functions private
    private function populateSheetElements($index, $cell, $extras = Array()) {
        if (count($this->sheets) == 0)
            return false;
        
        $options = array_merge($this->fileOptions, $extras);
        
        $this->sheetData[$this->worksheetName] .= $this->sheetData($index, $cell, $options);
        
        return true;
    }
    
    private function sheetData($index, $cell, $options) {
        $result = "";
        
        if ($index == 0) {
            $result .= "<row>";
            
            foreach ($cell as $key => $value) {
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
        
        if ($index > 0) {
            if (is_array($cell[0]) == true) {
                foreach ($cell as $key => $value) {
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
            }
            else {
                $result .= "<row>";
                
                foreach ($cell as $key => $value) {
                    if (is_numeric($value) == true)
                        $result .= "<c><v>" . trim($value) . "</v></c>";
                    else {
                        if (defined("ENT_XML1") == true)
                            $newValue = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, $options['encoding']);
                        else
                            $newValue = htmlspecialchars($value);
                        
                        $result .= "<c t=\"inlineStr\"><is><t>$newValue</t></is></c>";
                    }
                }
                
                $result .= "</row>";
            }
        }
        
        return $result;
    }
}