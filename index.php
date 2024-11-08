<?php
$csvHandler = new CsvHandler();
$bracketInput = $csvHandler->ReadCsv("сетка.csv");
$tornament = new TournamentBracketHandler($bracketInput, 10);

$tornament->ShuffleMembers();
$outputBracket = $tornament->FormDefaultTournamentBracket();

$csvHandler->WriteCsv("qq.csv", $outputBracket);

class CsvHandler
{
    private $separator = ';';


    private function DetectDelimiter($input_file)
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

        if (($handle = fopen($input_file, "r")) !== false) {

            $firstLine = fgets($handle);
            fclose($handle);

            foreach ($delimiters as $delimiter => &$count) {
                $count = count(str_getcsv($firstLine, $delimiter));
            }
            $delimiter = array_search(max($delimiters), $delimiters);
            $this->separator = $delimiter;
        } else {
            throw new Exception("Не удалось открыть файл: " . $input_file);
        }
    }

    function ReadCsv($input_file, $separator = null)
    {
        $this->DetectDelimiter($input_file);
        $separator = $separator ?? $this->separator;
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
    private $consolationTime;
    private $tornamentTime = null;
   
    
    public $fightBreakTime;
    public $fightTime; 
    public $bracketBreakTime;
    
    function __construct($memberList, $fightTime,$fightBreakTime = null, $bracketBreakTime = null)
    {
        unset($memberList[0]);
        $memberList = array_values($memberList);
        $this->memberList = $memberList;
        $this->fightTime = $fightTime;
        $this->fightBreakTime= $fightBreakTime;
    }

    function ShuffleMembers()
    {
        shuffle($this->memberList);
    }

    private function ImitateBracket($bracket, $bracketName, $stages = null, $isConsolation = false)
    {
        $time = 0;
        $stages = $stages ?? log(count($bracket), 2);
        if($isConsolation)
        {
            $stages+=1;
        }
        for ($i = 0; $i < $stages; $i++) {
            $currStage = array_filter(array_map(function ($item, $key) use ($i) {
                if (!empty($item[$i])) {
                    return [$item[0], $key];
                }
            }, $bracket, array_keys($bracket)));

            $currStage = array_values($currStage);

            for ($j = 0; $j < count($currStage); $j += 2) {
                $bracket[$currStage[$j][1]][$i + 1] = $bracket[$currStage[$j][1]][0];
                $time += $this -> fightTime;
                if (isset($currStage[$j + 1][1]) && !$isConsolation) {
                    
                    if (!empty($bracket[$currStage[$j + 1][1]][1]) || $j <= count($currStage)) {
                        array_push($this->consolationBracket, $bracket[$currStage[$j + 1][1]]);
                    } else {
                        array_unshift($this->consolationBracket, $bracket[$currStage[$j + 1][1]]);
                    }
                }
            }
        }
        
        array_unshift($bracket, [$bracketName, $time]);
print_r($bracket);
        return $bracket;
    }

    private function FormPreliminaryBracket($preliminaryFightsCount)
    {
        $memberList = [];
        $membersCount = count($this->memberList);
    
        for ($i = 0; $i < $preliminaryFightsCount; $i++) {
            array_push($memberList, $this->memberList[$i], $this->memberList[$membersCount - ($i + 1)]);
        }

        return $this->ImitateBracket($memberList,"предварительная", 1);
    }

    function FormDefaultTournamentBracket()
    {
        $membersCount = count($this->memberList);
          if($membersCount < 2)
        {
           
            throw new Exception("Недостаточно участников для формирования сетки");
        }
        $mainMembersCount = null;
        $preliminaryFightsCount = null;
        $mainBracket = $this->memberList;
        $preliminaryBracket = [];

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

        $mainBracket = $this->ImitateBracket($mainBracket, "основная");
        $consolationBracket = $this->ImitateBracket($this->consolationBracket,"утешительная", null, true);
        echo $this -> consolationTime;
        return array_merge(
            $preliminaryBracket,
            [[' ']],
            $mainBracket,
            [[' ']],
            $consolationBracket,
            
        );
    }
}

