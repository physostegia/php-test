<?php

class CsvHandler
{
    private $separator = ';';
    


   

    function DetectDelimiter($input_file)
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

        if (($handle = fopen($input_file, "r")) !== false) {

            $firstLine = fgets($handle);
            fclose($handle);

            foreach ($delimiters as $delimiter => &$count) {
                $count = count(str_getcsv($firstLine, $delimiter));
            }
            
            $this -> separator = array_search(max($delimiters), $delimiters);
        } else {
            throw new Exception("Не удалось открыть файл: " . $input_file);
        }
    }

    function ReadCsv($input_file, $separator = null)
    {
        $this -> DetectDelimiter($input_file);
        $separator = $separator ?? $this -> separator;
       
        $result = [];
        if (($file = fopen($input_file, "r")) !== false) {
            while (($data = fgetcsv($file, 2000, $separator)) !== false) {




                if (!empty($data[0])) {
                    $result[] = $data;
                }
            }
            fclose($file);
        } else {
            throw new Exception("Не удалось открыть файл: " . $input_file);
        }
        return $result;
    }

    function WriteCsv($output_file, $data, $separator = null)
    {
        $separator = $separator ?? $this -> separator;
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
    public $consolationBracket = [];

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
    public function ImitateBracket($bracket, $stages = null)
    {
        
        $stages = $stages ?? log(count($bracket), 2);
       


        for ($i = 0; $i < $stages; $i++) {
            $currStage = array_filter(array_map(function ($item, $key) use ($i) {
                if (!empty($item[$i])) {
                    return [$item[0], $key];
                }
            }, $bracket, array_keys($bracket)));
            
            $currStage = array_values($currStage);
           
            for ($j = 0; $j < count($currStage); $j += 2) {
                
                $bracket[$currStage[$j][1]][$i + 1] = $this -> memberList[$currStage[$j][1]][0];
                if(!empty($bracket[$currStage[$j + 1][1]][1]) || $j <=  count($currStage))
                {
                    array_push($this -> consolationBracket, $this -> memberList[$currStage[$j + 1][1]]);
                    
                }
                else
                {
                    array_unshift($this -> consolationBracket, $this -> memberList[$currStage[$j + 1][1]]);
                }
            }
            
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

        return $this->ImitateBracket($memberList, 1);
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
        $consolationBracket = $this->ImitateBracket($this->consolationBracket);
        return array_merge($preliminaryBracket, [[" "]], [["основная"]],[[" "]], $mainBracket,[[" "]],  [["утешительная"]], [[" "]], $consolationBracket);
    }
}
$p = new CsvHandler();
$m = $p->ReadCsv("сетка.csv");
$q = new TournamentBracketHandler($m);

$q->ShuffleMembers();


$p->WriteCsv("qq.csv", $q->FormDefaultTournamentBracket());
