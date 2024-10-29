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
            while (($data = fgetcsv($file, 1000, $this->separator)) !== false) {




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
    private $consolationBracket = [];

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
    private function ImitateBracket($bracket, $stages = null, $numberOfRounds = null)
    {
        $numberOfRounds = $numberOfRounds ?? count($bracket) - 1;
        $stages = $stages ?? log(count($bracket), 2);
        $currBracket = $bracket;
        $matchesPerStage = [];

        for ($k = 1; $k <= $stages; $k++) {
            $matchesPerStage[$k] = count($bracket) / (2 ** $k);
        }

        


        $winnerFactor = 2;
        for ($i = 0; $i < $stages; $i++) {

            
            for ($j = 0; $j < $matchesPerStage[$i + 1]; $j++) {
                echo $winnerFactor;
                if (!empty($bracket[$j*$winnerFactor][$i])) {

                    $bracket[$j*$winnerFactor][$i + 1] = $bracket[$j*$winnerFactor][0];
                }
            }
            $winnerFactor*=2;

            
        }

        return $bracket;
    }
    private function FormPreliminaryBracket($preliminaryFightsCount)
    {
        $memberList = [];
        $membersCount = count($this->memberList);

        for ($i = 0; $i < $preliminaryFightsCount; $i++) {

            array_push($memberList, $this->memberList[$i], $this->memberList[$membersCount - ($i + 1)]);
        }

        return $this->ImitateBracket($memberList, 1, count($memberList) / 2);
    }

    function FormDefaultTournamentBracket()
    {
        $membersCount = count($this->memberList);
        $mainMembersCount = null;
        $preliminaryFightsCount = null;
        $mainBracket = $this->memberList;

        for ($i = 2; $i <= $membersCount; $i *= 2) {

            $mainMembersCount = $i;
        }

        $preliminaryFightsCount = $membersCount % $mainMembersCount;

        if ($preliminaryFightsCount > 0) {


            $preliminaryBracket = $this->FormPreliminaryBracket($preliminaryFightsCount);
            foreach ($preliminaryBracket as $member) {

                if (empty($member[1])) {
                    unset($mainBracket[array_search($member, $mainBracket)]);
                }
            }
            $mainBracket = array_values($mainBracket);
        }
        $mainBracket = $this->ImitateBracket($mainBracket);
        return $mainBracket;
    }
}
$p = new CsvHandler("сетка.csv");
$m = $p->ReadCsv();


$q = new TournamentBracketHandler($m);
$q->ShuffleMembers();
$p -> WriteCsv("dasda.csv", $q->FormDefaultTournamentBracket());
