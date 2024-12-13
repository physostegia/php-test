<?php
$csvHandler = new CsvHandler();
$bracketInput = $csvHandler->ReadCsv("сетка.csv");
$tornament = new TournamentBracketHandler($bracketInput, 10, 10, 10);

$tornament->ShuffleMembers();
$outputBracket = $tornament->FormDefaultTournamentBracket();

$csvHandler->WriteCsv("qq.csv", $outputBracket);

class CsvHandler
{
    private $separator = ';';


    private function DetectDelimiter($input_file)
    {
        $delimiters = [";" => 0, "," => 0, "\t" => 0, "|" => 0];

        if (($handle = fopen($input_file, "rb")) !== false) {

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

        if (($file = fopen($input_file, "rb")) !== false) {

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
        $file = fopen($output_file, 'wb');
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
    private $tornamentTime = null;
    private $bracketHeader = null;
    private $bracketCount = 0;
    private $output = [];

    public $fightBreakTime;
    public $fightTime;
    public $bracketBreakTime;

    function __construct($memberList, $fightTime, $fightBreakTime = null, $bracketBreakTime = null)
    {
        unset($memberList[0]);
        $memberList = array_values($memberList);
        $this->memberList = $memberList;
        $this->fightTime = $fightTime;
        $this->fightBreakTime = $fightBreakTime;
        $this->bracketBreakTime = $bracketBreakTime;
    }

    function ShuffleMembers()
    {
        shuffle($this->memberList);
        shuffle($this->memberList);
        shuffle($this->memberList);
    }

    private function ImitateBracket($bracket, $bracketName, $stages = null, $isConsolation = false)
    {
        $fightCount = 0;
        $stages = $stages ?? log(count($bracket), 2);
        $outputBracket = $bracket;
        
        if ($this->bracketHeader == null) {
            $this->bracketHeader = array_map(function ($item) {
                return "";
            }, $bracket[0]);
        }
        if(!$isConsolation){
        $bracketHeader = $this->bracketHeader;
        $bracketHeader[0] = $bracketName;
        array_unshift($outputBracket, $this->bracketHeader,  $bracketHeader);
        $this -> output = array_merge($this -> output, $outputBracket);
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
                $fightCount += 1;
                
                if (isset($currStage[$j + 1][1]) && !$isConsolation) {

                    if (!empty($bracket[$currStage[$j + 1][1]][1]) || $j <= count($currStage)) {
                        array_push($this->consolationBracket, $bracket[$currStage[$j + 1][1]]);
                    } else {
                        array_unshift($this->consolationBracket, $bracket[$currStage[$j + 1][1]]);
                    }
                }
            }
        }
       
        
        $this -> bracketCount += 1;
       

        $bracketAmount = $fightCount * $this->fightTime + ($fightCount - 1) * ($this->fightBreakTime ?? 0);
       
        echo $bracketName . " - " . $bracketAmount . " минут. \n";
        $this->tornamentTime += $bracketAmount;
       
        
        return $bracket;
        

    }
    private function ValidateMembers($members)
    {
        
        $membersCount = count($members);
       
        for ($i = 2; $i <= $membersCount; $i *= 2) {
            $membersValidCount = $i;
        }
        if($membersCount == $membersValidCount)
        {
            return true;
        }
        else{
            return $membersCount - $membersValidCount;
        }

    }

    private function FormPreliminaryBracket($preliminaryFightsCount, $bracket, $isConsolation = false)
    {
        $memberList = [];
        $membersCount = count($bracket);

        for ($i = 0; $i < $preliminaryFightsCount; $i++) {
            array_push($memberList, $bracket[$i], $bracket[$membersCount - ($i + 1)]);
        }

        return $this->ImitateBracket($memberList, "предварительная сетка" . $isConsolation, 1, $isConsolation);
    }

    function FormDefaultTournamentBracket()
{
    $mainBracket = $this->memberList;
    $preliminaryBracket = [];
    $preliminaryConsolationBracket = [];
    $preliminaryFightsCount = $this->ValidateMembers($this->memberList);
    
    if ($preliminaryFightsCount !== true) {
        $preliminaryBracket = $this->FormPreliminaryBracket($preliminaryFightsCount, $mainBracket);
        
        foreach ($preliminaryBracket as $key => $member) {
            if (empty($member[1])) { 
                foreach ($mainBracket as $mainKey => $mainMember) {
                    if ($mainMember === $member) {
                        unset($mainBracket[$mainKey]);
                        break;
                    }
                }
            }
        }
        $mainBracket = array_values($mainBracket);
    }
    $mainBracket = $this->ImitateBracket($mainBracket, "основная сетка");

    $preliminaryConsolationFightsCount = $this ->ValidateMembers($this -> consolationBracket);
    if ($preliminaryConsolationFightsCount !== true) {
        $preliminaryConsolationBracket = $this->FormPreliminaryBracket($preliminaryConsolationFightsCount,$this -> consolationBracket ,true);
        for ($i = 0; $i < $preliminaryConsolationFightsCount; $i++) {
               unset($this->consolationBracket[$i]);
        }
            
        
        $this -> consolationBracket = array_values($this -> consolationBracket);
    }
    
    $this->ImitateBracket($this->consolationBracket, "утешительная сетка", null, true);

    
    $this->tornamentTime += ($this->bracketCount - 1) * ($this->bracketBreakTime ?? 0);
    echo "время прохождения турнира - " . $this->tornamentTime . " минут.";

   
    $result = $this -> output;

    
    array_shift($result);

    return $result;
}

}
