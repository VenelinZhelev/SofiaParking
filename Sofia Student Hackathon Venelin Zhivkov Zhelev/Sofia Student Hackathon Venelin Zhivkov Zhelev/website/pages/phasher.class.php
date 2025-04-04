<?PHP

class PHasher{

private static $Instance;

	private function __construct(){
	}
	
	public static function Instance(){
		 if (is_null(self::$Instance)){
			self::$Instance = new self();
		}
		return self::$Instance;
	}
	
	public function Detect($res1, $res2, $precision = 1){
		$hash1 = $this->HashImage($res1);
		$result = 0;
		for($rot=0; $rot<=270; $rot+=90){
			$new_result = $this->Compare($res1, $res2, $rot, $precision);
			if($new_result > $result){
				$result = $new_result;
			}
		}
		
		return $result;
	}
	
	public function CompareStrings($hash1, $hash2, $precision = 1){
		
		$similarity = strlen($hash1);
		
		for($i=0; $i<strlen($hash1); $i++){
			if($hash1[$i] != $hash2[$i]){
				$similarity--;
			}
		}
		
		$percentage = round(($similarity/strlen($hash1)*100), $precision);
		return $percentage;
	}
	
	public function Compare($res1, $res2, $rot=0, $precision = 1){
		
		$hash1 = $this->HashImage($res1); 
		$hash2 = $this->HashImage($res2, $rot);
		
		$similarity = count($hash1);
		
		foreach($hash1 as $key=>$val){
			if($hash1[$key] != $hash2[$key]){
				$similarity--;
			}
		}
		$percentage = round(($similarity/count($hash1)*100), $precision);
		return $percentage;
	}
	
	public function ArrayAverage($arr){
		return floor(array_sum($arr) / count($arr));
	}
		
	public function HashImage($res, $rot=0, $mir=0, $size = 8, $WhichHash = 'aHash'){
		
		$res = $this->NormalizeAsResource($res);
		$rescached = imagecreatetruecolor($size, $size);
		
		imagecopyresampled($rescached, $res, 0, 0, 0, 0, $size, $size, imagesx($res), imagesy($res));
		imagecopymergegray($rescached, $res, 0, 0, 0, 0, $size, $size, 50);
		
		$w = imagesx($rescached);
		$h = imagesy($rescached);
		
		$pixels = array();

		for($y = 0; $y < $size; $y++) {
		
			for($x = 0; $x < $size; $x++) { 
					
				switch($rot){
					case 90:	$rx=(($h-1)-$y);	$ry=$x;			break;
					case 180:	$rx=($w-$x)-1;		$ry=($h-1)-$y;	break;
					case 270:	$rx=$y;				$ry=($h-$x)-1;	break;
					default:	$rx=$x;				$ry=$y;
				}
				
				switch($mir){
					case 1: $rx = (($w-$rx)-1); break;
					case 2: $ry = ($h-$ry); 	break;
					case 3: $rx = (($w-$rx)-1);
							$ry = ($h-$ry); 	break;
					default: 					break;
				}
				
				$rgb = imagecolorsforindex($rescached, imagecolorat($rescached, $rx, $ry));
				
    			$r = $rgb['red'];
				$g = $rgb['green'];
				$b = $rgb['blue'];
				
				$gs = (($r*0.299)+($g*0.587)+($b*0.114));
				$gs = floor($gs);
				
				$pixels[] = $gs; 
			}
		}		
		
		$avg = $this->ArrayAverage($pixels);
		
		$index = 0;
		if($WhichHash == 'dHash') 
		{
			foreach($pixels as $ind => $px)
			{
				if(!isset($pixels[($ind + 1)]))
					$ind = -1;
				if($px > $pixels[($ind + 1)])
					$hash[] = 1;
				else
					$hash[] = 0;
			}
		}
		else
		{
			foreach($pixels as $px){
				if($px > $avg){
					$hash[$index] = 1;
				}
				else{
					$hash[$index] = 0;
				}
				$index += 1;
			}
		}
		return $hash;
	}

	function  FastHashImage($res, $scale=8)  {

		$res = $this->NormalizeAsResource($res);
	
		$hash = array();
		$src_w = imagesx($res);
		$src_h = imagesy($res);
		
		$rX = $src_w / $scale;
		$rY = $src_h / $scale;
		$w = 0;
		for ($y = 0; $y < $scale; $y++)  {
			$ow = $w; $w = round(($y + 1) * $rY);
			$t = 0;
			for ($x = 0; $x < $scale; $x++)  {
				$r = $g = $b = 0; $a = 0;
				$ot = $t; $t = round(($x + 1) * $rX);
				for ($u = 0; $u < ($w - $ow); $u++)  {
					for ($p = 0; $p < ($t - $ot); $p++)  {
					
						$rgb = imagecolorat($res, $ot + $p, $ow + $u);
						
						$r = ($rgb >> 16) & 0xFF;
						$g = ($rgb >> 8) & 0xFF;
						$b = $rgb & 0xFF;
						
						$gs = floor((($r*0.299)+($g*0.587)+($b*0.114))); 
						$hash[$x][$y] = $gs;
					}
					
				}
			}
		}
		
		$nhash = array();
		
		$xnormal=0;

		foreach($hash as $xkey=>$xval){
			foreach($hash[$xkey] as $ykey=>$yval){
				unset($hash[$xkey]);
				$nhash[$xnormal][] = $yval;
			}
			$xnormal++;
		} 
		
		$phash = array();
		
		for($x=0; $x<$scale; $x++){
		
			$avg = floor(array_sum($nhash[$x]) / count(array_filter($nhash[$x])));
		
		for($y=0; $y<$scale; $y++){
				$rgb = $nhash[$x][$y];
				if($rgb > $avg){
					$phash[] = 1;
				}
				else{
					$phash[] = 0;
				}
			}
		}
		
		return $phash;
		
	}
			
	private function NormalizeAsResource($resource){
		if(gettype($resource) == 'resource'){
			return $resource;
		}
		else{
			if(file_exists(realpath($resource)) &&  getimagesize($resource)){
				return imagecreatefromstring(file_get_contents($resource));
			}
		}
	}

	public function HashAsString($hash, $hex=true){
		$i = 0;
		$bucket=null;
		$return = null;
		if($hex == true){
			foreach($hash as $bit){
				$i++;
				$bucket.=$bit;
				if($i==4){
					$return.= dechex(bindec($bucket));
					$i=0;
					$bucket=null;
				}
			}
			return $return;
		}
		return implode(null, $hash);
	}
	
	public function HashAsTable($hash, $size=8, $cellsize=8){
		
		$index = 0;
		$table = "<table cellpadding=\"0\" cellspacing=\"0\" style=\"table-layout: fixed;display:inline-block;\"><tr><td><tbody>";
		for($x=0; $x<$size; $x++){
			$table.="<tr>";
			for($y=0; $y<$size; $y++){
				$bit = (bool)($hash[$index]);
				$bitcolor = ($bit)?"#ddd":"#000";
				$abitcolor = ($bit)?"#000":"#fff";
				$sizepx = $size."px";
				$style="width:{$size}px;height:{$size}px;background-color:$bitcolor;color:$abitcolor;text-align:center;padding:0px;";
				$table.="<td style=\"$style\"></td>";
				$index++;
			}
			$table.="</tr>";
		}
		$table.="</tbody></table>";
		return $table;
	}	


}

?>