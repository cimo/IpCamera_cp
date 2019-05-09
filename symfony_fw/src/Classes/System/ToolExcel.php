<?php
namespace App\Classes\System;

class ToolExcel {
    // Vars
    private $self;
    private $encoding;
    
    private $path;
    private $name;
    
    private $file;
    private $fileOptions;
    private $fileElements;
    
    private $sheets;
    private $sheetData;
    private $worksheetName;
    
    // Properties
    public function setPath($value) {
        $this->path = $value;
    }
    
    public function setName($value) {
        $this->name = trim($value) . ".xlsx";
    }
    
    public function getName() {
        return $this->name;
    }
    
    // Functions public
    public function __construct($self = null, $encoding = "UTF-8") {
        $this->self = $self;
        $this->encoding = $encoding;
        
        $this->path = null;
        $this->name = "no_name";
        
        $this->file = null;
        
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
                        $result = $this->populateSheet($index, $cell, $extras);
                    else
                        $result = $this->self->toolExcelCsvCallback($index, $cell, $extras);
                    
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
        $this->sheetData[$this->worksheetName] = "";
    }
    
    public function populateSheet($index, $cell, $extras = Array()) {
        if (count($this->sheetData) == 0)
            return false;
        
        $options = array_merge($this->fileOptions, $extras);
        
        $this->sheetData[$this->worksheetName] .= $this->sheetDataLogic($index, $cell, $options);
        
        return true;
    }
    
    public function save() {
        if ($this->path == null || count($this->sheetData) == 0)
            return false;
        
        $zipArchive = new \ZipArchive();
        
        $zipArchive->open("{$this->path}/{$this->name}", \ZIPARCHIVE::CREATE);
        
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
        
        $this->file = "{$this->path}/{$this->name}";
        
        return true;
    }
    
    // Functions private
    private function sheetDataLogic($index, $cell, $options) {
        $result = "";
        
        $elements = $cell;
        
        if ($index == 0) {
            $result .= "<row>";
            
            if (isset($cell['labels']) == true)
                $elements = $cell['labels'];
            
            foreach ($elements as $key => $value) {
                if (is_numeric($value) == true)
                    $result .= "<c s=\"1\" t=\"inlineStr\"><is><t>" . trim($value) . "</t></is></c>";
                else {
                    if (defined("ENT_XML1") === true)
                        $newValue = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, $options['encoding']);
                    else
                        $newValue = htmlspecialchars($value);
                    
                    $result .= "<c s=\"1\" t=\"inlineStr\"><is><t>$newValue</t></is></c>";
                }
            }
            
            $result .= "</row>";
            
            if (isset($cell['items']) == true) {
                $elements = $cell['items'];
                
                $result .= "<row>";
                
                foreach ($elements as $key => $value) {
                    if (is_numeric($value) == true)
                        $result .= "<c t=\"inlineStr\"><is><t>" . trim($value) . "</t></is></c>";
                    else {
                        if (defined("ENT_XML1") === true)
                            $newValue = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, $options['encoding']);
                        else
                            $newValue = htmlspecialchars($value);
                        
                        $result .= "<c t=\"inlineStr\"><is><t>$newValue</t></is></c>";
                    }
                }
                
                $result .= "</row>";
            }
        }
        else if ($index > 0) {
            if (isset($cell['items']) == true)
                $elements = $cell['items'];
            
            $result .= "<row>";
            
            foreach ($elements as $key => $value) {
                if (is_numeric($value) == true)
                    $result .= "<c t=\"inlineStr\"><is><t>" . trim($value) . "</t></is></c>";
                else {
                    if (defined("ENT_XML1") === true)
                        $newValue = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, $options['encoding']);
                    else
                        $newValue = htmlspecialchars($value);
                    
                    $result .= "<c t=\"inlineStr\"><is><t>$newValue</t></is></c>";
                }
            }
            
            $result .= "</row>";
        }
        
        return $result;
    }
}