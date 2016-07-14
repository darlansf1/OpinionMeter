<?php
    
			
	function  endsWith($haystack, $needle) {
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}
	
    class PorterStemmerPT
    {
		private static $vowels  = array ('a', 'e', 'i', 'o', 'u', 'á', 'é', 'í', 'ó', 'ú', 'â', 'ê', 'ô');
        private static $suffix1 = array ("amentos", "imentos", "amento", "imento", "adoras", "adores", "aço~es", "ismos", "istas", "adora", "aça~o", "antes", "ância", "ezas", "icos", "icas", "ismo", "ável", "ível", "ista", "osos", "osas", "ador", "ante", "eza", "ico", "ica", "oso", "osa");
		private static $suffix2 = array ("logías", "logía");
		private static $suffix3 = array ("uciones", "ución");
		private static $suffix4 = array ("ências", "ência");
		private static $suffix5 = array ("amente");
		private static $suffix6 = array ("mente");
		private static $suffix7 = array ("idades", "idade");
		private static $suffix8 = array ("ivas", "ivos", "iva", "ivo");
		private static $suffix9 = array ("iras", "ira");
		private static $suffixv = array ("aríamos", "eríamos", "iríamos", "ássemos", "êssemos", "íssemos", "aríeis", "eríeis", "iríeis", "ásseis", "ésseis", "ísseis", "áramos", "éramos", "íramos", "ávamos", "aremos", "eremos", "iremos", "ariam", "eriam", "iriam", "assem", "essem", "issem", "ara~o", "era~o", "ira~o", "arias", "erias", "irias", "ardes", "erdes", "irdes", "asses", "esses", "isses", "astes", "estes", "istes", "áreis", "areis", "éreis", "ereis", "íreis", "ireis", "áveis", "íamos", "armos", "ermos", "irmos", "aria", "eria", "iria", "asse", "esse", "isse", "aste", "este", "iste", "arei", "erei", "irei", "aram", "eram", "iram", "avam", "arem", "erem", "irem", "ando", "endo", "indo", "adas", "idas", "arás", "aras", "erás", "eras", "irás", "avas", "ares", "eres", "ires", "íeis", "ados", "idos", "ámos", "amos", "emos", "imos", "iras", "ada", "ida", "ará", "ara", "erá", "era", "irá", "ava", "iam", "ado", "ido", "ias", "ais", "eis", "ira", "ia", "ei", "am", "em", "ar", "er", "ir", "as", "es", "is", "eu", "iu", "ou");
		private static $suffixr = array ("os", "a", "i", "o", "á", "í", "ó");
		private static $suffixf = array ("e", "é", "ê");
		
        public static function Stem($word){
            if (strlen($word) <= 2) {
                return $word;
            }

			$stem 	= self::processNasalidedVowels($word);
			
            $r1		= self::findR($stem);
            $r2		= self::findR($r1);
            $rv		= self::findRV($stem);
			
			$stem	= self::step1($stem, $r1, $r2, $rv);
			
			if($stem == $word){
				$stem	= self::step2($stem, $rv);
			}else{
				$r1		= self::findR($stem);
				$r2		= self::findR($r1);
				$rv		= self::findRV($stem);
			}
			
			if($stem != $word){
				$r1		= self::findR($stem);
				$r2		= self::findR($r1);
				$rv		= self::findRV($stem);
				$stem	= self::step3($stem, $r1, $r2, $rv);
			}else{
				$stem	= self::step4($stem, $r1, $r2, $rv);
			}
			
			if($stem != $word){
				$r1		= self::findR($stem);
				$r2		= self::findR($r1);
				$rv		= self::findRV($stem);
			}
			$stem	= self::step5($stem, $r1, $r2, $rv);
			
			return self::deprocessNasalidedVowels($stem);

        }

        private static function processNasalidedVowels($str){
			$str = str_replace ("ã","a~",$str);
			$str = str_replace ("õ","o~",$str);
			return $str;
        }
        
		private static function deprocessNasalidedVowels($str){
			$str = str_replace ("a~","a",$str);
			$str = str_replace ("o~","o",$str);
			return $str;
        }		
	
        private static function findR($str){
			$len = strlen($str) - 1;
			for ($i = 0; $i < $len ; $i++ ) 
				if(in_array($str{$i},self::$vowels) && !in_array($str{$i+1},self::$vowels) )
					return substr ($str, $i+2 );
            return "";
        }
		
        private static function findRV($str){
			$len = strlen($str);
			if( $len > 2){
				$len = $len - 1;
				if( !in_array($str{1},self::$vowels)){
					for ($i = 2; $i < $len ; $i++ )
						if (in_array($str{$i},self::$vowels))
								return substr ($str, $i+1);
					
				}else if(in_array($str{0},self::$vowels) && in_array($str{1},self::$vowels) ){
					for ($i = 2; $i < $len ; $i++ )
						if (!in_array($str{$i},self::$vowels))
								return substr ($str, $i+1);
					
				}else {
					return substr ($str, 2 );
				}
			}
            return "";
        }   
		
        public static function getLongestSuffix($suffixList, $str){
			$size = count($suffixList);
			$maxLen = 0;
			$result = "";
			
			for ($i = 0; $i < $size ; $i++ ){
				$needle = $suffixList[$i] ;
				$len = strlen ($needle);
				if( $len > $maxLen &&  strpos($str,$needle) !== FALSE ) {
					$maxLen = $len;
					$result = $needle;
				}
			}
			return $result;
        }

        
		private static function step1($str, $r1, $r2, $rv){
            
			$suffix = self::getLongestSuffix ( self::$suffix1 , $r2 );
			if( !empty ($suffix) )
				return substr ( $str , 0 , -strlen($suffix) ); //Removing suffix

			$suffix = self::getLongestSuffix ( self::$suffix2 , $r2 );
			if( !empty ($suffix) )
				return substr ( $str , 0 , -strlen($suffix) ) . "log"; 			

			$suffix = self::getLongestSuffix ( self::$suffix3 , $r2 );
			if( !empty ($suffix) )
				return substr ( $str , 0 , -strlen($suffix) ) . "u"; 		

			$suffix = self::getLongestSuffix ( self::$suffix4 , $r2 );
			if( !empty ($suffix) )
				return substr ( $str , 0 , -strlen($suffix) ) . "ente"; 		
			
			$suffix = self::getLongestSuffix ( self::$suffix5 , $r1 );
			if( !empty ($suffix) ){
				$str = substr ( $str , 0 , -strlen($suffix) ); //Removing suffix
				if(endsWith($str,"iv") && endsWith($r2,"iv".$suffix)){
					$str = substr ( $str , 0 , -2 ); //Removing "iv"
					if(endsWith($str,"at") && endsWith($r2,"ativ".$suffix))
						$str = substr ( $str , 0 , -2 ); //Removing "at"
				}else if ( endsWith($str,"os") && endsWith($r2,"os".$suffix) ){
					$str = substr ( $str , 0 , -2 ); //Removing "os"
				}else if ( endsWith($str,"ic") && endsWith($r2,"ic".$suffix) ){
					$str = substr ( $str , 0 , -2 ); //Removing "ic"
				}else if ( endsWith($str,"ad") && endsWith($r2,"ad".$suffix) ){
					$str = substr ( $str , 0 , -2 ); //Removing "ad"
				}
				return $str;
			}
				
			$suffix = self::getLongestSuffix ( self::$suffix6 , $r2 );	
			if( !empty ($suffix) ){
				$str = substr ( $str , 0 , -strlen($suffix) ); //Removing suffix
				if ( endsWith($str,"ante") && endsWith($r2,"ante".$suffix) ){
					$str = substr ( $str , 0 , -4 ); //Removing "ante"
				}else if ( endsWith($str,"avel") && endsWith($r2,"avel".$suffix) ){
					$str = substr ( $str , 0 , -4 ); //Removing "avel"
				}else if ( endsWith($str,"ível") && endsWith($r2,"ível".$suffix) ){
					$str = substr ( $str , 0 , -4 ); //Removing "ível"
				}
				return $str;
			}

			$suffix = self::getLongestSuffix ( self::$suffix7 , $r2 );	
			if( !empty ($suffix) ){
				$str = substr ( $str , 0 , -strlen($suffix) ); //Removing suffix
				if ( endsWith($str,"abil") && endsWith($r2,"abil".$suffix) ){
					$str = substr ( $str , 0 , -4 ); //Removing "abil"
				}else if ( endsWith($str,"ic") && endsWith($r2,"ic".$suffix) ){
					$str = substr ( $str , 0 , -2 ); //Removing "ic"
				}else if ( endsWith($str,"iv") && endsWith($r2,"iv".$suffix) ){
					$str = substr ( $str , 0 , -2 ); //Removing "iv"
				}
				return $str;
			}

			$suffix = self::getLongestSuffix ( self::$suffix8 , $r2 );	
			if( !empty ($suffix) ){
				$str = substr ( $str , 0 , -strlen($suffix) ); //Removing suffix
				if ( endsWith($str,"at") && endsWith($r2,"at".$suffix) )
					$str = substr ( $str , 0 , -2 ); //Removing "at"
				return $str;
			}

			$suffix = self::getLongestSuffix ( self::$suffix9 , $rv );	
			if( !empty ($suffix) && endsWith($str,"e".$suffix))
				$str = substr ( $str , 0 , -strlen($suffix)) . "ir";
			
			
			return $str;
        }  
		
        private static function step2($str, $rv){
			$suffix = self::getLongestSuffix ( self::$suffixv , $rv );
			if( !empty ($suffix) )
				return substr ( $str , 0 , -strlen($suffix) );
            return $str;
        }  		
		
		private static function step3($str, $rv){
			if( endsWith($rv, "i") && endsWith($str, "ci") )
				return substr($str, -1); //Removing last char
            return $str;
        }  	
		
		private static function step4($str, $rv){
			$suffix = self::getLongestSuffix ( self::$suffixr , $rv );
			if( !empty ($suffix) )
				return substr ( $str , 0 , -strlen($suffix) ); //Removing suffix
            return $str;
        }  	
		
		private static function step5($str, $rv){
			
			$suffix = self::getLongestSuffix ( self::$suffixf , $rv );
			$suffixLen = strlen ($suffix);
			
			if( $suffixLen > 0 ){
				
				$str = substr ($str, 0, -$suffixLen);
				
				if( (endsWith($str, "gu") && endsWith($rv, "u" . $suffix )) ||
					(endsWith($str, "ci") && endsWith($rv, "i" . $suffix )))
					$str = substr ($str, 0, -1);	//Removing last char
				
				return $str;
			}
			
			
			if( substr( $rv, -1) == "ç" )
				$str = substr ( $str , 0 , -1 )  . "c" ;	//Replacing ç
            return $str;
        }  	
    }
?>
