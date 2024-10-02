<?php
/*
## Transforming the decoding result of KN-01

### Parent class reference:
http://dev.hsdn.org/wdparser/metar/

### Formula for calculating air relative humidity:
https://bmcnoldy.rsmas.miami.edu/Humidity.html

### License:
    Copyright (C) 2024, Spin Opel
    Copyright (C) 2013-2020, Information Networks, Ltd.
    Copyright (C) 2001-2006, Mark Woodward
*/

class SynopConv extends Synop
{
	public $observed_date_time;		//Дата и время
	public $desc_clouds = "";		//Облачность
	public $desc_weather = null;	//Метеорологическое явление

	//Пост обработка полученных результатов
	//DATAS` date NOT NULL DEFAULT '1000-01-01'
	//echo "\n\n"."Дата получения данных"."\n";
	/*
	private function convDate($date_time) {
		date_default_timezone_set('UTC'); //временная зона по умолчанию
		if (isset($date_time)) {
			$time_stamp = strtotime($date_time); // timestamp from RFC 2822
			$date_chg = date('Y-m-d', $time_stamp);		
		} else {
			$date_chg = null;
		}
		return $date_chg;
	}
	*/
	
	//TIMES` time NOT NULL DEFAULT '00:00:00'
	//echo "\n"."Срок наблюдения, UTC"."\n";
	/*
	private function convTime($date_time) {
		date_default_timezone_set('UTC'); //временная зона по умолчанию
		if (isset($date_time)) {
			$time_stamp = strtotime($date_time); // timestamp from RFC 2822
			$date_chg = date('H:i:s', $time_stamp);		
		} else {
			$date_chg = null;
		}
		return $date_chg;
	}
	*/
	
	//Speed` varchar(5) DEFAULT NULL
	//echo "\n"."Скорость ветра, м/с"."\n";
	private function convWindSpeed($speed) {
		if (is_numeric($speed)) {
			$speed_chg = round($speed, 0);
		} else {
			$speed_chg = null;
		}
		return $speed_chg;
	}
	
	//Dir` enum('Северный','Южный','Западный','Восточный','С-З','С-В','Ю-З','Ю-В','Переменный') DEFAULT NULL
	//echo "\n"."Направление ветра"."\n";
	//преобразование аббревиатуры в направление ветра
	private function convWindDir($str_dir) {
		$direction = array(
		'NNE'       => 'С-В',
		'NE'        => 'С-В',
		'ENE'       => 'С-В',
		'E'         => 'Восточный',
		'ESE'       => 'Ю-В',
		'SE'        => 'Ю-В',
		'SSE'       => 'Ю-В',
		'S'         => 'Южный',
		'SSW'       => 'Ю-З',
		'SW'        => 'Ю-З',
		'WSW'       => 'Ю-З',
		'W'         => 'Западный',
		'WNW'       => 'С-З',
		'NW'        => 'С-З',
		'NNW'       => 'С-З',
		'N'         => 'Северный',
		'VRB'       => 'Переменный',
		'calm wind' => '',
		''          => ''
		);
		return $direction[$str_dir];
	}
	
	//Temp` varchar(5) DEFAULT NULL
	//echo "\n"."Температура (выставляем знак перед числом - ошибка прошлого), °C"."\n";
	private function convTemp($temp) {
		if (!is_null($temp)) {
			$temp = number_format($temp, 1, '.', '');  //преобразовываем в число с плавающей точкой
			if ($temp >= 0) {
				$temp_chg = "%2b".$temp;  //плюс и число 0
			} elseif ($temp < 0) {
				$temp_chg = "%2d".abs($temp);  //минус
			}
		} else {
			$temp_chg = NULL;
		}
		return $temp_chg;
	}
	
	//Humidity` varchar(5) DEFAULT NULL
	//echo "\n"."Влажность, %"."\n";
	private function convHumidity($RH, $T, $Td) {
		if (empty($RH) && is_numeric($T) && is_numeric($Td)) {
			$humid_formula = 100*(exp((17.625*$Td)/(243.04+$Td))/exp((17.625*$T)/(243.04+$T)));
			$humid_chg = round($humid_formula, 0);
		} else {
			$humid_chg = $RH;
		}
		
		//проверяем значение влажности
		if ($humid_chg > 100) {
			$humid_chg = NULL;
		}					
		return $humid_chg;
	}
	
	//Clouds` enum('Малооблачно','Переменная облачность','Облачно с прояснениями','Сплошная облачность','') NOT NULL DEFAULT ''
	//echo "\n"."Облачность"."\n";
	private function convClouds($amount) {
		$arr_clouds = array(
		'NSW' => '',						//никакой существенной погоды не наблюдается	//no significant weather are observed
		'NCD' => '',						//облака не обнаружены							//nil cloud detected
		'NOBS'=> '',						//нет наблюдений								//no observation
		'SKC' => 'Ясно',					//Чистое небо или Ясно или Безоблачно			//clear skies
		'CLR' => 'Ясно',					//Чистое небо или Ясно или Безоблачно			//clear skies
		'FEW' => 'Малооблачно',				//Малооблачно									//partly cloudy
		'SCT' => 'Переменная облачность',	//Рассеянные облака или Переменная облачность	//scattered clouds
		'BKN' => 'Облачно с прояснениями',	//В основном облачно или Облачно с прояснениями	//mostly cloudy
		'BKM' => 'Облачно с прояснениями',	//В основном облачно или Облачно с прояснениями	//mostly cloudy
		'NSC' => 'Облачно с прояснениями',	//В основном облачно или Облачно с прояснениями	//mostly cloudy
		'OVC' => 'Сплошная облачность',		//Пасмурная погода или Сплошная облачность		//overcast
		'VV'  => 'Вертикальная видимость',	//Вертикальная видимость						//vertical visibility
		''    => ''
		);
		return $arr_clouds[$amount];
	}
	
	//weather` varchar(64) DEFAULT NULL
	//echo "\n"."Метеорологическое явление"."\n";
	/*
	private function convWeather($wxcode) {
		$arr_weather = array(
		'VC' => 'Неподалеку',					//неподалеку									//nearby
		'MI' => 'Мелкий',						//мелкий										//shallow
		'PR' => 'Частичный',					//частичный										//partial
		'BC' => 'Кусочки',						//кусочки или пятна								//patches of
		'DR' => 'Позёмка',						//позёмка										//low drifting
		'BL' => 'Ветрено',						//ветрено										//blowing
		'SH' => 'Ливневый',						//ливневый										//showers
		'TS' => 'Гроза',						//гроза											//thunderstorm
		'FZ' => 'Охлажденный',					//охлажденный или гололед						//freezing
		'DZ' => 'Морось',						//морось										//drizzle
		'RA' => 'Дождь',						//дождь											//rain
		'SN' => 'Снег',							//снег											//snow
		'SG' => 'Снежные зерна',				//снежные зерна									//snow grains
		'IC' => 'Ледяные кристаллы',			//ледяные кристаллы								//ice crystals
		'PE' => 'Ледяная крупа',				//ледяная крупа									//ice pellets
		'GR' => 'Град',							//град											//hail
		'GS' => 'Снежная крупа',				//мелкий град и/или снежная крупа				//small hail
		'UP' => 'Неизвестное явление',			//неизвестное явление							//unknown
		'BR' => 'Дымка',						//дымка или мгла								//mist
		'FG' => 'Туман',						//туман											//fog
		'FU' => 'Дым',							//дым											//smoke
		'VA' => 'Вулканический пепел',			//вулканический пепел							//volcanic ash
		'DU' => 'Пыль, взвешенная в воздухе',	//пыль, взвешенная в воздухе					//widespread dust
		'SA' => 'Песок',						//песок											//sand
		'HZ' => 'Легкий туман',					//легкий туман									//haze
		'PY' => 'Водяная пыль',					//водяная пыль									//spray
		'PO' => 'Песчаные вихри',				//хорошо развитые пылевые или песчаные вихри	//well-developed dust/sand whirls
		'SQ' => 'Шквал',						//шквал											//squalls
		'FC' => 'Смерч',						//воронкообразное облако, смерч или смерч		//funnel cloud, tornado, or waterspout
		'SS' => 'Песчаная буря',				//песчаная буря или пыльная буря				//sandstorm/duststorm
		''   => null
		);
		return $arr_weather[$wxcode];
	}
	*/
	
	private function convVisibility($dist) {
		//$dist_chg = $dist/1000; // перевод значения в километры
		$dist_chg = $dist; // перевод значения в километры
		if (empty($dist_chg)) {
			$dist_chg = null;
		} elseif ($dist_chg > 10) {
			$dist_chg = 10;
		}
		return $dist_chg;
	}
	
	//Pressure` varchar(75) DEFAULT NULL
	//echo "\n"."Давление на уровне моря, гПа"."\n";
	private function convPressure($pressureV) {
		if (is_numeric($pressureV)) {
			$pressure_chg = round($pressureV, 0);
		} else {
			$pressure_chg = null;
		}
		return $pressure_chg;
	}	
	
	//Trend` varchar(75) DEFAULT NULL
	//echo "\n"."Изменение давления"."\n";
	private function convPressureTrend($pressureT) {
		$tendency = array(
		"0" => 0,     //increasing
		"1" => 1,     //increasing
		"2" => 2,     //increasing
		"3" => 3,     //increasing
		"4" => NULL,  //no tendency
		"5" => 5,     //decreasing
		"6" => 6,     //decreasing
		"7" => 7,     //decreasing
		"8" => 8,     //decreasing
		""  => NULL
		);
		return $tendency[$pressureT];
	}	
	
	//Trend` varchar(75) DEFAULT NULL
	//echo "\n"."Изменение погоды"."\n";
	private function convCloudsReport($str) {
		//пример описания изменения погоды
		//Broken sky at 1006 meters, cumulonimbus; overcast sky at 2012 meters
		
		//приведение строки к нижнему регистру
		$str = strtolower($str);
		//echo $str;
		
		//список поисковых фраз
		$arr_phrases = array(
		//cloud_codes
		'особых погодных условий не наблюдается'  => 'no significant weather are observed',
		''  => 'no significant clouds are observed',  //существенных облаков не наблюдается
		'облака не обнаружены'  => 'nil cloud detected',
		'безоблачно'  => 'no significant changes expected',
		'чистое небо'  => 'clear skies',
		'существенных изменений не ожидается' => 'no observation',
		//
		'малооблачно'  => 'a few',
		'переменная облачность'  => 'scattered',
		'облачно с прояснениями'  => 'broken sky',
		'сплошная облачность'  => 'overcast sky',
		//
		'вертикальная видимость'   => 'vertical visibility',
		//cloud cover type codes
		'кучево-дождевые облака'  => 'cumulonimbus',
		'возвышающиеся кучевые облака' => 'towering cumulus',
		//runway visual range tendency codes
		'убывание' => 'decreasing',
		'увеличение' => 'increasing',
		'нет тенденции' => 'no tendency',
		//runway visual range prefix codes
		'более' => 'more',
		'менее' => 'less',
		//runway runway deposits codes
		'чисто и сухо' => 'clear and dry',
		'испарения' => 'damp',
		'влажно или водяные пятна' => 'wet or water patches',
		'изморозь или покрытый инеем' => 'rime or frost covered',
		'сухой снег' => 'dry snow',
		'мокрый снег' => 'wet snow',
		'слякоть' => 'slush',
		'лед' => 'ice',
		'утрамбованный или раскатанный снег' => 'compacted or rolled snow',
		'замерзшая колея или гребни' => 'frozen ruts or ridges',
		//runway runway deposits extent codes
		'от 10% или менее' => 'from 10% or less',
		'от 11% до 25%' => 'from 11% to 25%',
		'от 26% до 50%' => 'from 26% to 50%',
		'от 51% до 100%' => 'from 51% to 100%',
		//runway runway deposits depth codes
		'менее 1 мм' => 'less than 1 mm',
		'10 см' => '10 cm',
		'15 см' => '15 cm',
		'20 см' => '20 cm',
		'25 см' => '25 cm',
		'30 см' => '30 cm',
		'35 см' => '35 cm',
		'40 см или более' => '40 cm or more',
		//runway runway friction codes
		'плохой' => 'poor',
		'средний/плохой' => 'medium/poor',
		'средний' => 'medium',
		'средний/хороший' => 'medium/good',
		'хороший' => 'good',
		'цифры недостоверны' => 'figures unreliable',
		//trends flag codes
		'ожидается, что скоро возникнет' => 'expected to arise soon',
		'ожидается, что возникнет временно' => 'expected to arise temporarily',
		'ожидается, что возникнет с перерывами' => 'expected to arise intermittent',
		'предварительный прогноз'  => 'provisional forecast',
		'отмененный прогноз'   => 'cancelled forecast',
		'нулевой прогноз'   => 'nil forecast',
		//measurement units
		'м' => 'meters',  //метрах
		//trends flag codes
		'' => 'BECMG',  //ожидается, что скоро возникнет  //expected to arise soon
		'' => 'TEMPO',  //жидается временное возникновение  //expected to arise temporarily
		'' => 'INTER',  //ожидается прерывистое возникновение  //expected to arise intermittent
		'' => 'PROV',  //предварительный прогноз  //provisional forecast
		'' => 'CNL',  //прогноз отменен  //cancelled forecast
		'' => 'NIL',  //нулевой прогноз  //nil forecast
		//trends time codes
		'на' => ' at ',
		'от' => ' from ',
		'до' => ' until '	
		);

		//положение поисковых фраз в массиве
		foreach ($arr_phrases as $k => $v) {
			$str_offset = 0;
			$count_symbols = substr_count($str, $v);
			//в случае дублирования поисковых фраз
			for ($p = 1; $p <= $count_symbols; $p++) {
				//позиция поисковой фразы
				$pos = stripos($str, $v, $str_offset);
				if ($pos !== false) {
					$arr_convStr[$pos] = $k;  //заменяем значение массива на его ключ (перевод слова на русский)
					$str_offset = $pos + strlen($v);
					//echo $str_offset.PHP_EOL;
				}					
			}
		}
		
		//положение цифровых значений в массиве
		preg_match_all('/(\d+)/', $str, $matches);
		foreach ($matches[0] as $v) {
			$str_offset = 0;
			$count_symbols = substr_count($str, $v);
			//в случае дублирования поисковых фраз
			for ($d = 1; $d <= $count_symbols; $d++) {
				//позиция поисковой фразы
				$pos_digit = stripos($str, $v, $str_offset);
				if ($pos_digit !== false) {
					$arr_convStr[$pos_digit] = $v;
					$str_offset = $pos_digit + strlen($v);
					//echo $str_offset.PHP_EOL;
				}					
			}
		}
		
		//положение разделителей в массиве
		$arr_separators = array(",", ";");  //список разделителей
		foreach ($arr_separators as $val) {
			$str_offset = 0;
			$count_symbols = substr_count($str, $val);
			//в случае дублирования разделителей
			for ($s = 1; $s <= $count_symbols; $s++) {
				//позиция разделителя
				$pos_separator = strpos($str, $val, $str_offset);
				if ($pos_separator !== false) {
					$arr_convStr[$pos_separator] = $val;
					$str_offset = $pos_separator + 1;
					//echo $str_offset.PHP_EOL;
				}					
			}
		}
		//var_dump($arr_convStr);
		
		if (empty($arr_convStr)) {
			$str_translated = null;
		} else {
			ksort($arr_convStr);
			$str_translated = implode(" ", $arr_convStr);  //объединяем элементы массива в строку
			$pattern = '/\s+('.implode("|", $arr_separators).')\s+/i';
			$replacement = '$1 ';
			$str_translated = preg_replace($pattern, $replacement, $str_translated);  //удаляем пробел перед знаком разделителя
			$str_translated = strtoupper(substr($str_translated, 0, 2)).substr($str_translated, 2);  //регистр первой буквы на UTF-8
		}
		return $str_translated;
	}

	public function convParam() {
		//Пост обработка результатов, полученных из родительского класса
		//DATAS` date NOT NULL DEFAULT '1000-01-01'
		//echo "\n\n"."Дата получения данных"."\n";
		//$this->observed_date = $this->convDate($this->observed_date);

		//TIMES` time NOT NULL DEFAULT '00:00:00'
		//echo "\n"."Срок наблюдения, UTC"."\n";
		//$this->observed_time = $this->convTime($this->observed_time);

		//DateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		//echo "\n"."Дата и время"."\n";
		//echo $this->observed_date;
		//$this->observed_date_time = $this->observed_date." ".$this->observed_time;

		//Speed` varchar(5) DEFAULT NULL
		//echo "\n"."Скорость ветра, м/с"."\n";
		$this->wind_speed = $this->convWindSpeed($this->wind_speed);

		//Dir` enum('Северный','Южный','Западный','Восточный','С-З','С-В','Ю-З','Ю-В','Переменный') DEFAULT NULL
		//echo "\n"."Направление ветра"."\n";
		//преобразование угла в направление ветра
		$this->wind_direction = $this->convWindDir($this->wind_direction);

		//TempAir` DEFAULT NULL
		//echo "\n"."Температура воздуха (выставляем знак перед числом - ошибка прошлого), °C"."\n";
		//добавляем знак перед числом
		$this->temperature_sign = $this->convTemp($this->temperature);

		//TempAir` DEFAULT NULL
		//echo "\n"."Температура воздуха (выставляем знак перед числом - ошибка прошлого), °C"."\n";
		//добавляем знак перед числом
		$this->t_max_sign = $this->convTemp($this->t_max);

		//TempAir` DEFAULT NULL
		//echo "\n"."Температура воздуха (выставляем знак перед числом - ошибка прошлого), °C"."\n";
		//добавляем знак перед числом
		$this->t_min_sign = $this->convTemp($this->t_min);

		//TempAir` DEFAULT NULL
		//echo "\n"."Температура воздуха (выставляем знак перед числом - ошибка прошлого), °C"."\n";
		//добавляем знак перед числом
		$this->t_ground_section5_sign = $this->convTemp($this->t_ground_section5);

		//TempAir` DEFAULT NULL
		//echo "\n"."Температура воздуха (выставляем знак перед числом - ошибка прошлого), °C"."\n";
		//добавляем знак перед числом
		$this->t_min_2cm_night_sign = $this->convTemp($this->t_min_2cm_night);

		//Humidity` varchar(5) DEFAULT NULL
		//echo "\n"."Влажность, %"."\n";
		$this->humidity = $this->convHumidity($this->humidity, $this->temperature, $this->dew_point);

		//Clouds` enum('Малооблачно','Переменная облачность','Облачно с прояснениями','Сплошная облачность','') NOT NULL DEFAULT ''
		//echo "\n"."Облачность"."\n";
		//echo $this->clouds[0]['amount'];
		if (isset($this->clouds[0]['amount'])) {
			$this->desc_clouds = $this->convClouds($this->clouds[0]['amount']);
		}

		//weather` varchar(64) DEFAULT NULL
		//echo "\n"."Метеорологическое явление"."\n";
		//echo $this->present_weather[0]['types'][0];
		/*
		if (isset($this->present_weather[0]['types'][0])) {
			$this->desc_weather = $this->convWeather($this->present_weather[0]['types'][0]);
		}
		*/

		//Visib` varchar(5) DEFAULT NULL
		//echo "\n"."Видимость, км"."\n";
		//$this->visibility = $this->convVisibility($this->visibility, $this->raw);
		$this->visibility = $this->convVisibility($this->visibility);

		//Trend` varchar(75) DEFAULT NULL
		//echo "\n"."Изменение давления"."\n";
		$this->barometer = $this->convPressure($this->barometer);

		//Trend` varchar(75) DEFAULT NULL
		//echo "\n"."Изменение давления"."\n";
		$this->barometer_trend = $this->convPressureTrend($this->barometer_trend);

		//Trend` varchar(75) DEFAULT NULL
		//echo "\n"."Изменение погоды"."\n";
		$this->clouds_report = $this->convCloudsReport($this->clouds_report);
	}
}
?>