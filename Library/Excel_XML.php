<?php
/*
 *  https://github.com/oliverschwarz/php-excel support multi sheet ,support fileext .xls .xml
 *	require dirname(__FILE__) . '/php-excel.class.php';
 *
 *	$data = array(
 *		array('Nr.', 'Name', 'E-Mail'),
		array(1, 'Oliver Schwarz', 'oliver.schwarz@gmail.com'),
		array(2, 'Hasematzel', 'hasematzel@gmail.com')
	);

	$xls = new Excel_XML;
	$xls->addWorksheet('Names', $data);
	$xls->addWorksheet('Names2',$data);
	$xls->sendWorkbook('test.xls');

*/


/**
 * Excel_XML
 */

/**
 * Class Excel_XML
 * 
 * A simple export library for dumping array data into an excel
 * readable format. Supports OpenOffice Calc as well.
 * 
 * @author    Oliver Schwarz <oliver.schwarz@gmail.com>
 */
class Excel_XML
{

        /**
         * MicrosoftXML Header for Excel
         * @var string
         */
        const sHeader = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

        /**
         * MicrosoftXML Footer for Excel
         * @var string
         */
        const sFooter = "</Workbook>";

        /**
         * Worksheet & Data
         * @var array
         */
        private $aWorksheetData;

        /**
         * Output string
         * @var string
         */
        private $sOutput;

        /**
         * Encoding to be used
         * @var string
         */
        private $sEncoding;

        /**
         * Constructor
         *
         * Instanciates the class allowing a user-defined encoding.
         *
         * @param string $sEncoding Charset encoding to be used
         */
        public function __construct($sEncoding = 'UTF-8')
        {
                $this->sEncoding = $sEncoding;
                $this->sOutput = '';
        }

        /**
         * Add a worksheet
         *
         * Creates a new worksheet and adds the given data to it.
         * @param string $title Title of worksheet
         * @param array $data 2-dimensional array of data
         */
        public function addWorksheet($title, $data)
        {
                $this->aWorksheetData[] = array(
                        'title' => $this->getWorksheetTitle($title),
                        'data'  => $data
                );
        }

        /**
         * Send workbook to browser
         *
         * Sends the finished workbook to the browser using PHP's header
         * directive.
         *
         * @param string $filename Filename to use for sending the workbook
         */
        public function sendWorkbook($filename)
        {
                if (!preg_match('/\.(xml|xls)$/', $filename)):
                        throw new Exception('Filename mimetype must be .xml or .xls');
                endif;
                $filename = $this->getWorkbookTitle($filename);
                $this->generateWorkbook();
                if (preg_match('/\.xls$/', $filename)):
                        header("Content-Type: application/vnd.ms-excel; charset=" . $this->sEncoding);
                        header("Content-Disposition: inline; filename=\"" . $filename . "\"");
                else:
                        header("Content-Type: application/xml; charset=" . $this->sEncoding);
                        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
                endif;
                echo $this->sOutput;
        }

        /**
         * Write workbook to file
         *
         * Writes the workbook into the file/path given as a parameters.
         * The method checks whether the directory is writable and the
         * file is not existing and writes the file.
         *
         * @param string $filename Filename to use for writing (must contain mimetype)
         * @param string $path Path to use for writing [optional]
         */
        public function writeWorkbook($filename, $path = '')
        {
                $this->generateWorkbook();
                $filename = $this->getWorkbookTitle($filename);
                if (!$handle = @fopen($path . $filename, 'w+')):
                        throw new Exception(sprintf("Not allowed to write to file %s", $path . $filename));
                endif;
                if (@fwrite($handle, $this->sOutput) === false):
                        throw new Exception(sprintf("Error writing to file %s", $path . $filename));
                endif;
                @fclose($handle);
                return sprintf("File %s written", $path . $filename);
        }

        /**
         * Get workbook output
         *
         * Just returns the generated workbook content.
         *
         * @return string Output generated by the class
         */
        public function getWorkbook()
        {
                $this->generateWorkbook();
                return $this->sOutput;
        }

        /**
         * Compatibility: Add an array
         *
         * This method implements compatibility to version 1.1. Though using
         * self::addWorksheet surely is an improvement, nobody should be
         * forced to rewrite his code.
         *
         * @param array $data Data to be added
         */
        public function addArray($data)
        {
                $this->addWorksheet('Table1', $data);
        }

        /**
         * Compatibility: Generate XML
         *
         * This method implements compatibility to version 1.1 and generates
         * the excel file.
         *
         * @param string $filename Filename (without mimetype)
         */
        public function generateXML($filename)
        {
                $filename = $this->getWorkbookTitle($filename);
                $filename .= '.xls';
                $this->sendWorkbook($filename);
        }

        /**
         * Workbook title correction
         *
         * Corrects filename (if necessary) stripping out non-allowed
         * characters.
         *
         * @param string $filename Desired filename
         * @return string Corrected filename
         */
        private function getWorkbookTitle($filename)
        {
                return preg_replace('/[^aA-zZ0-9\_\-\.]/', '', $filename);
        }

        /**
         * Worksheet title correction
         *
         * Corrects the worksheet title (given by the user) by the allowed
         * characters by Excel.
         *
         * @param string $title Desired worksheet title
         * @return string Corrected worksheet title
         */
        private function getWorksheetTitle($title)
        {
                $title = preg_replace ("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);
                return substr ($title, 0, 31);
        }

        /**
         * Generate the workbook
         *
         * This is the main wrapper to generate the workbook.
         * It will invoke the creation of worksheets, rows and
         * columns.
         */
        private function generateWorkbook()
        {
                $this->sOutput .= stripslashes(sprintf(self::sHeader, $this->sEncoding)) . "\n";
                foreach ($this->aWorksheetData as $item):
                        $this->generateWorksheet($item);
                endforeach;
                $this->sOutput .= self::sFooter;
        }

        /**
         * Generate the Worksheet
         *
         * The second wrapper generates the worksheet. When the worksheet
         * data seems to be more than the excel allowed maximum lines, the
         * array is sliced.
         *
         * @param array $item Worksheet data
         * @todo Add a security check to testify whether this is an array
         */
        private function generateWorksheet($item)
        {
                $this->sOutput .= sprintf("<Worksheet ss:Name=\"%s\">\n    <Table>\n", $item['title']);
                if (count($item['data']))
                        $item['data'] = array_slice($item['data'], 0, 65536);
                foreach ($item['data'] as $k => $v):
                        $this->generateRow($v);
                endforeach;
                $this->sOutput .= "    </Table>\n</Worksheet>\n";
        }

        /**
         * Generate the single row
         * @param array Item with row data
         */
        private function generateRow($item)
        {
                $this->sOutput .= "        <Row>\n";
                foreach ($item as $k => $v):
                        $this->generateCell($v);
                endforeach;
                $this->sOutput .= "        </Row>\n";
        }

        /**
         * Generate the single cell
         * @param string $item Cell data
         */
        private function generateCell($item)
        {
                $type = 'String';
                if (is_numeric($item)):
                        $type = 'Number';
                        if ($item{0} == '0' && strlen($item) > 1 && $item{1} != '.'):
                                $type = 'String';
                        endif;
                endif;
                $item = str_replace('&#039;', '&apos;', htmlspecialchars($item, ENT_QUOTES));
                $this->sOutput .= sprintf("            <Cell><Data ss:Type=\"%s\">%s</Data></Cell>\n", $type, $item);
        }

        /**
         * Deconstructor
         * Resets the main variables/objects
         */
        public function __destruct()
        {
                unset($this->aWorksheetData);
                unset($this->sOutput);
        }

}

?>