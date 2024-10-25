<?php
use League\Csv\Reader;
class CsvHandler
{
    private $input_file;
    private $separator;
    

    function __construct($input_file) 
    {
        $this -> input_file = $input_file;
        $this -> separator = $this -> DetectDelimiter();
    }

    function DetectDelimiter()
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

        if (($handle = fopen($this->input_file, "r")) !== false) {

            $firstLine = fgets($handle);
            fclose($handle);

            foreach ($delimiters as $delimiter => &$count) {
                $count = count(str_getcsv($firstLine, $delimiter));
            }

            return array_search(max($delimiters), $delimiters);
        } 
        else 
        {
            throw new Exception("Не удалось открыть файл: " . $this->input_file);
        }
    }

    function ReadCsv()
    {
        $result = [];
        if(($file = fopen($this -> input_file, "r")) !== false)
        {
            while(($data = fgetcsv($file, 1000, ";")) !== false)
            {
                
                
                
                
                if (!empty($data[0])) {
                    $result[] = $data;
                }
                
            }
            fclose($file);
            
            
        }
        else 
        {
            throw new Exception("Не удалось открыть файл: " . $this->input_file);
        }
        return $result;
    }

    function WriteCsv($output_file, $data)
    {

        $file = fopen($output_file, 'w');
        foreach($data as $line)
        {
            fputcsv($file, $line, ';');
        }
    }


    

}

class TournamentBracketsHandler
{

}
$p = new CsvHandler("сетка.csv");
$m = $p -> ReadCsv();
echo $p -> DetectDelimiter();
