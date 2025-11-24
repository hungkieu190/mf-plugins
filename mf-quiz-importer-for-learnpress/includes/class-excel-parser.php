<?php
/**
 * Excel Parser Class
 * Simple Excel parser without external dependencies
 *
 * @package MF_Quiz_Importer_For_LearnPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Excel parser class
 */
class MF_Excel_Parser {
    
    /**
     * Parse Excel file to array
     *
     * @param string $filepath Path to Excel file
     * @return array|WP_Error Parsed data or error
     */
    public static function parse($filepath) {
        if (!file_exists($filepath)) {
            return new WP_Error('file_not_found', __('Excel file not found.', 'mf-quiz-importer-lp'));
        }
        
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        
        if ($extension === 'xlsx') {
            return self::parse_xlsx($filepath);
        } elseif ($extension === 'xls') {
            return self::parse_xls($filepath);
        }
        
        return new WP_Error('invalid_format', __('Invalid Excel format.', 'mf-quiz-importer-lp'));
    }
    
    /**
     * Parse XLSX file (Office 2007+)
     *
     * @param string $filepath Path to XLSX file
     * @return array|WP_Error Parsed data
     */
    private static function parse_xlsx($filepath) {
        // XLSX is a ZIP file containing XML files
        $zip = new ZipArchive();
        
        if ($zip->open($filepath) !== true) {
            return new WP_Error('zip_error', __('Could not open XLSX file.', 'mf-quiz-importer-lp'));
        }
        
        // Read shared strings
        $shared_strings = array();
        $strings_xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($strings_xml) {
            $strings = simplexml_load_string($strings_xml);
            if ($strings) {
                foreach ($strings->si as $si) {
                    $shared_strings[] = (string)$si->t;
                }
            }
        }
        
        // Read worksheet
        $sheet_xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        
        if (!$sheet_xml) {
            return new WP_Error('sheet_error', __('Could not read worksheet.', 'mf-quiz-importer-lp'));
        }
        
        $sheet = simplexml_load_string($sheet_xml);
        if (!$sheet) {
            return new WP_Error('xml_error', __('Could not parse worksheet XML.', 'mf-quiz-importer-lp'));
        }
        
        $data = array();
        $row_index = 0;
        
        foreach ($sheet->sheetData->row as $row) {
            $row_data = array();
            $col_index = 0;
            
            foreach ($row->c as $cell) {
                $value = '';
                
                // Get cell value
                if (isset($cell->v)) {
                    $cell_value = (string)$cell->v;
                    
                    // Check if it's a shared string
                    if (isset($cell['t']) && (string)$cell['t'] === 's') {
                        $value = isset($shared_strings[(int)$cell_value]) ? $shared_strings[(int)$cell_value] : '';
                    } else {
                        $value = $cell_value;
                    }
                }
                
                $row_data[] = $value;
                $col_index++;
            }
            
            $data[] = $row_data;
            $row_index++;
        }
        
        return $data;
    }
    
    /**
     * Parse XLS file (Office 97-2003)
     * Note: This is a simplified parser. For complex XLS files, consider using a library.
     *
     * @param string $filepath Path to XLS file
     * @return array|WP_Error Parsed data
     */
    private static function parse_xls($filepath) {
        // XLS format is binary and complex
        // For better compatibility, we'll suggest converting to XLSX or CSV
        return new WP_Error(
            'xls_not_supported',
            __('XLS format (Excel 97-2003) is not fully supported. Please save your file as XLSX (Excel 2007+) or CSV format.', 'mf-quiz-importer-lp')
        );
    }
    
    /**
     * Convert parsed Excel data to CSV-like array
     *
     * @param array $data Raw Excel data
     * @return array CSV-like array with headers
     */
    public static function to_csv_format($data) {
        if (empty($data)) {
            return array();
        }
        
        $header = array_shift($data);
        $result = array();
        
        foreach ($data as $row) {
            // Ensure row has same number of columns as header
            $row_data = array();
            for ($i = 0; $i < count($header); $i++) {
                $row_data[$header[$i]] = isset($row[$i]) ? $row[$i] : '';
            }
            $result[] = $row_data;
        }
        
        return $result;
    }
    
    /**
     * Check if ZipArchive is available
     *
     * @return bool
     */
    public static function is_zip_available() {
        return class_exists('ZipArchive');
    }
    
    /**
     * Check if SimpleXML is available
     *
     * @return bool
     */
    public static function is_simplexml_available() {
        return function_exists('simplexml_load_string');
    }
    
    /**
     * Check if Excel parsing is supported
     *
     * @return bool|WP_Error True if supported, WP_Error otherwise
     */
    public static function check_requirements() {
        if (!self::is_zip_available()) {
            return new WP_Error(
                'zip_not_available',
                __('ZipArchive extension is not available. Please enable it in your PHP configuration to import Excel files.', 'mf-quiz-importer-lp')
            );
        }
        
        if (!self::is_simplexml_available()) {
            return new WP_Error(
                'simplexml_not_available',
                __('SimpleXML extension is not available. Please enable it in your PHP configuration to import Excel files.', 'mf-quiz-importer-lp')
            );
        }
        
        return true;
    }
}
