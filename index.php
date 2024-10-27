<?php

use League\Csv\Reader;

class CsvHandler
{
    private $input_file;
    private $separator;


    function __construct($input_file)
    {
        $this->input_file = $input_file;
        $this->separator = $this->DetectDelimiter();
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
        } else {
            throw new Exception("Не удалось открыть файл: " . $this->input_file);
        }
    }

    function ReadCsv()
    {
        $result = [];
        if (($file = fopen($this->input_file, "r")) !== false) {
            while (($data = fgetcsv($file, 1000, $this -> separator)) !== false) {




                if (!empty($data[0])) {
                    $result[] = $data;
                }
            }
            fclose($file);
        } else {
            throw new Exception("Не удалось открыть файл: " . $this->input_file);
        }
        return $result;
    }

    function WriteCsv($output_file, $data, $separator = null)
    {
        $separator = $separator ?? $this->separator;
        $file = fopen($output_file, 'w');
        foreach ($data as $line) {
            fputcsv($file, $line, $separator);
        }
        fclose($file);
    }
}

class TournamentBracketHandler
{
    private $memberList = [];


    function __construct($memberList)
    {
        unset($memberList[0]);
        $memberList = array_values($memberList);
        $this->memberList = $memberList;
    }

    function ShuffleMembers()
    {
        shuffle($this->memberList);
    }
    private function ImitateBracket($bracket)
    {
        for ($i = 0; $i < count($bracket); $i + 2) 
        {
            $bracket[$i];
        }
    }
    private function FormPreliminaryBracket($preliminaryFightsCount)
    {
        $memberList = [];
        $membersCount = count($this->memberList);
        for ($i = 0; $i < $preliminaryFightsCount; $i++) 
        {
            array_push($memberList, $this->memberList[$i], $this->memberList[$membersCount - ($i + 1)]);
        }
        return $memberList;
    }

    function FormDefaultTournamentBracket()
    {
        $membersCount = count($this->memberList);
        $mainMembersCount = null;
        $preliminaryFightsCount = null;


        for ($i = 2; $i <= $membersCount; $i *= 2) 
        {

            $mainMembersCount = $i;
        }

        $preliminaryFightsCount = $membersCount % $mainMembersCount;

        if ($preliminaryFightsCount > 0) 
        {
            if ($preliminaryFightsCount & 1) 
            {
                $preliminaryFightsCount++;
            }

            $preliminaryBracket = $this->FormPreliminaryBracket($preliminaryFightsCount);
        }
        return $preliminaryBracket;
    }
}
$p = new CsvHandler("сетка.csv");
$m = $p->ReadCsv();


$q = new TournamentBracketHandler($m);
print_r($q->FormDefaultTournamentBracket());
 