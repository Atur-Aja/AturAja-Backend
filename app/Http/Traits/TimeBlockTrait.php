<?php
namespace App\Http\Traits;

trait TimeBlockTrait{
    private function stringToTimeBlock($time, $duration=15, $batas="bawah"){
		$time = strtotime ($time) - strtotime("today") - 60; //Get Timestamp
		$duration = $duration * 60;

		// Pembulatan Kebawah
		$selisih = $time % $duration;
		if($selisih!=0){
			if($batas=="bawah"){
				$time = $time - $selisih;
			}elseif($batas=="atas"){
				$time = $time + ($duration - $selisih);
			}
		}
		
		$time = $time/$duration;
		return $time;
	}

	private function timeBlockToString($time, $duration=15){
		$time = $time * $duration;
		$hours = floor($time / 60);
   		$minutes = ($time % 60);

        if($minutes<10){
            $minutes = "0" . $minutes;
        }
        if($hours<10){
            $hours = "0" . $hours;
        }

		$timeString = $hours . ":" . $minutes;
		
		return $timeString;
	}

    private function getFreeTimes($schedules, $startTime, $endTime){
        $scheduleTimeLength = $endTime - $startTime;
        $timeTable = array_fill(0, 96, FALSE);

        for ($i = 0; $i < count($schedules); $i++) {
            for ($j = $schedules[$i][0]; $j <= $schedules[$i][1]; $j++) {
                $timeTable[$j] = TRUE;
            }
        }
        
        // Check if the time is free
        $freeTimes = $this->checkFreeTimes($timeTable, $startTime, $endTime, $scheduleTimeLength);
        
        // Check free time on work hours
        if(count($freeTimes)>0){
            return $freeTimes;            
        }else{
            $freeTimes = $this->checkFreeTimes($timeTable, 36, 68, $scheduleTimeLength);
        }
        
        // Check free time on entire day
        if(count($freeTimes)>0){
            return $freeTimes;            
        }else{
            $freeTimes = $this->checkFreeTimes($timeTable, 0, 96, $scheduleTimeLength);
        }
    }

    private function checkFreeTimes($timeTable, $batasBawah, $batasAtas, $scheduleTimeLength){
        $freeTimes = [];
        $counter = 0;
        $startFreeTime = -1;    
    
        for ($i = $batasBawah; $i < $batasAtas; $i++) {
            if($timeTable[$i]){
                // simpan timeblock kosong
                if($startFreeTime!=-1 && $counter>=$scheduleTimeLength){
                    $endFreeTime = $startFreeTime + $counter;
                    $freeTime = [$startFreeTime, $endFreeTime];
                    array_push($freeTimes, $freeTime);
                }
                
                $counter = 0;
                $startFreeTime = -1;

            }else{
                // hitung timeblock kosong
                if($startFreeTime==-1 && $counter==0){
                    $startFreeTime = $i;
                    
                //simpan timeblock kosong
                }else if($startFreeTime!=-1 && $counter>=$scheduleTimeLength){
                    $endFreeTime = $startFreeTime + $counter;
                    $freeTime = [$startFreeTime, $endFreeTime];
                    array_push($freeTimes, $freeTime);
                    $counter = 0;
                    $startFreeTime = $i;
                }

                $counter++;
            }
        }
    
        // simpan timeblock kosong
        if($startFreeTime!=0 && $counter>=$scheduleTimeLength){
            $endFreeTime = $startFreeTime + $counter;
            $freeTime = [$startFreeTime, $endFreeTime];
            array_push($freeTimes, $freeTime);
        }

        if(count($freeTimes)>3){
            return (array_slice($freeTimes,0,3));
        }else{
            return $freeTimes;
        }
    }
}    
?>