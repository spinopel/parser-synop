<?php
/*
## Example of a line of raw SYNOP code in format KN-01

### Change the variable raw to get the result.
### The variable raw has the format KN-01.
*/

// Raw SYNOP code string (FM-12 code)
//$raw = '201809051400 AAXX 10001 33041 42968 00000 10013 21028 30093 40253 52001 555 14100';
//$raw = '201809051400 AAXX 10001 34363 62597 42005 10051 20001 39957 40108 52008 69932 82530 333 20048 31003 46997 55080 86714 91111 555 19020 50057 52001 53012 7035/ 88036=';
//$raw = '201809051400 AAXX 10001 13624 41660 81402 10048 20041 30102 40142 52010 76162 8453/ 333 84633 84360=';
//$raw = '201809051400 AAXX 10001 38799 13/// ///// 5//// 6//// 333 2//// 3//// 555 5//// 52/// 530// 7////=';

// Raw SYNOP code string (KN-01 code)
//$raw = '27277 10998 00801 10127 20081 30154 40318 52015 60002 333 20102 30012 91003 91103=';
//$raw = '38915 32997 02003 10302 10120 39765 40060 53026 333 20202=';
//$raw = '34363 62597 42005 10051 20001 39957 40108 52008 69932 82530 333 20048 31003 46997 55080 86714 91024 555 19020 50057 52001 53012 7035/ 88036=';
//$raw = '13633 12964 33208 10326 29032 30079 40114 54001 60001 83030 333       83360=';
//$raw = '60693 36/// /3503 10325 20154=';
//$raw = '60613 NIL=';
//$raw = '37235 82997 00000 19184 20175 39794 40093 57005=';
//$raw = '33001 35/72 /2703 10152 20139 39977 40171 52005 333 20131=';

//$raw = '26542 36/// ///// 10082 20051 39925 40141 57002 333 10116=';
//$raw = '26542 46/// ///// 10080 20049 39928 40144 52003=';
//$raw = '26542 46/// ///// 10077 20055 39924 40140 57004=';
//$raw = '26542 /6/// ///// 10077 20053 39926 40142 52002 555 20074 69978 7997/=';
//$raw = '26542 16/// ///// 10075 20051 39927 40143 52001 69972 333 20074=';
$raw = '26820 32463 80302 11027 21046 39903 40092 52002 886// 333 21028 43997 555 90010=';
//$raw = '33001 32361 80401 11014 21025 39882 40087 52004 886// 333 21014 42997 88709 555 90010=';

//подключаем классы для расшифровки SYNOP
require_once 'Synop.php';
require_once 'SynopConv.php';

//удалить перевод каретки и конец строки в многострочных SYNOP
$arr_new_line = array("\n", "\r\n");  //специальные символы
$raw = str_replace($arr_new_line, '', $raw);

// Create class instance for parse SYNOP string with debug output enable
$synopConv = new SynopConv($raw, TRUE);

// Parse SYNOP
$parameters = $synopConv->parse();

/*
print_r($parameters)."\n\n"; // get parsed parameters as array

// Debug information
$debug = $synopConv->debug();
print_r($debug)."\n\n"; // get debug information as array
*/

// Get all converted parameters
$synopConv->convParam();

/*
## Отображаем результаты декодирования SYNOP для наполнения БД
*/
echo "\n\n"."Представление результатов декодирования SYNOP"."\n";
echo $synopConv->raw;

//Пост обработка полученных результатов
//DATAS` date NOT NULL DEFAULT '1000-01-01'
//echo "\n\n"."Дата получения данных"."\n";
//echo $synopConv->observed_date;

//TIMES` time NOT NULL DEFAULT '00:00:00'
//echo "\n"."Срок наблюдения, UTC"."\n";
//echo $synopConv->observed_time;

//DateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
//echo "\n"."Дата и время"."\n";
//echo $synopConv->observed_date_time;

//STATION_TYPE_CODE` varchar(20) NOT NULL DEFAULT ''
//echo "\n"."Тип станции"."\n";
//echo $synopConv->station_report;

//ID_STATION` varchar(5) NOT NULL DEFAULT ''
echo "\n"."Индекс станции"."\n";
echo $synopConv->station_id;

//Weather group indicator
echo "\n"."Указатель типа станции (обслуживаемая персоналом или автоматическая)"."\n";
echo $synopConv->station_operation;

//Cloud_height, cloud base of lowest observed cloud
//echo "\n"."Высота нижней границы самых низких облаков"."\n";
echo "\n"."Высота облаков, м"."\n";
echo $synopConv->cloud_height;

//Horizontal visibility in km
echo "\n"."Видимость, км"."\n";
echo $synopConv->visibility;

//Total cloud cover in okta
echo "\n"."Общее количество облаков"."\n";
echo $synopConv->cloud_cover_tot;

//wind direction in dekadegree (10 minute mean)
//Dir` enum('Северный','Южный','Западный','Восточный','С-З','С-В','Ю-З','Ю-В','Переменный') DEFAULT NULL
echo "\n"."Направление ветра"."\n";
echo $synopConv->wind_direction;

//Wind speed (10 minute mean)
//Speed` varchar(5) DEFAULT NULL
echo "\n"."Скорость ветра, ";
echo $synopConv->wind_unit;
echo "\n";
echo $synopConv->wind_speed;

//Temp` varchar(5) DEFAULT NULL
echo "\n"."Температура воздуха, °C"."\n";
echo $synopConv->temperature;

//TempD` varchar(5) DEFAULT NULL
echo "\n"."Температура точки росы, °C"."\n";
echo $synopConv->dew_point;

//Moisture` varchar(5) DEFAULT NULL
echo "\n"."Влажность, %"."\n";
echo $synopConv->humidity;

//Pressure` smallint(5) unsigned DEFAULT NULL
echo "\n"."Давление на уровне станции, гПа"."\n";
echo $synopConv->barometer_st;

//Pressure` smallint(5) unsigned DEFAULT NULL
echo "\n"."Давление на уровне моря, гПа"."\n";
echo $synopConv->barometer;

//Trend` varchar(75) DEFAULT NULL
echo "\n"."Изменение давления"."\n";
echo $synopConv->barometer_trend;

//Trend` varchar(75) DEFAULT NULL
echo "\n"."Значение изменения давления, гПа"."\n";
echo $synopConv->barometer_diff;

//Precipitation amount
echo "\n"."Количество осадков, мм"."\n";
echo $synopConv->precip;

//Reference time of precipitation
echo "\n"."Срок накопления осадков"."\n";
echo $synopConv->precip_ref_time;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Текущее метеорологическое явление"."\n";
echo $synopConv->current_weather;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Прошедшее метеорологическое явление 1"."\n";
echo $synopConv->w_course1;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Прошедшее метеорологическое явление 2"."\n";
echo $synopConv->w_course2;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Количество облаков CL или CM, если облаков CL нет"."\n";
echo $synopConv->cloud_cover_lowest;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Облака вертикального развития и облака нижнего яруса (кроме слоисто-дождевых)"."\n";
echo $synopConv->cloud_type_low;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Облака среднего яруса и слоисто-дождевые облака"."\n";
echo $synopConv->cloud_type_medium;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Облака верхнего яруса"."\n";
echo $synopConv->cloud_type_high;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Максимальная температура воздуха за день, °C"."\n";
echo $synopConv->t_max;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Минимальная температура воздуха за ночь, °C"."\n";
echo $synopConv->t_min;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Состояние поверхности почвы при отсутствии снежного покрова"."\n";
echo $synopConv->grd_conditions;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Минимальная температура поверхности почвы за ночь, °C"."\n";
echo $synopConv->t_ground;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Состояние подстилающей поверхности при наличии снежного покрова"."\n";
echo $synopConv->conditions_snow;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Высота снежного покрова, см"."\n";
echo $synopConv->snow_height;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Продолжительность солнечного сияния за сутки, ч"."\n";
echo $synopConv->sunshine_dur;

//Precipitation amount in section 3
echo "\n"."Количество осадков (секция 3), мм"."\n";
echo $synopConv->precip_section3;

//Reference time of precipitation
echo "\n"."Срок накопления осадков (секция 3)"."\n";
echo $synopConv->precip_ref_time_section3;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Количество облачности, кол-во баллов"."\n";
echo $synopConv->amount_cloudiness;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Форма облаков"."\n";
echo $synopConv->clouds_shape;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Высота нижней границы облаков, м"."\n";
echo $synopConv->cld_low_height;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Дополнительная информация о погоде в срок наблюдения (требуется доработка)"."\n";
echo $synopConv->weather_addon1;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Дополнительная информация о погоде между сроками наблюдения (требуется доработка)"."\n";
echo $synopConv->weather_addon2;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Порыв ветра, м/с"."\n";
echo $synopConv->gust;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Состояние поверхности почвы при отсутствии снежного покрова (секция 5)"."\n";
echo $synopConv->grd_conditions_section5;

//weather` varchar(64) DEFAULT NULL
echo "\n"."Температура подстилающей поверхности в срок наблюдения (секция 5), °C"."\n";
echo $synopConv->t_ground_section5;

//Temp` varchar(5) DEFAULT NULL
echo "\n"."Средняя  температура  воздуха  за  прошедшие  сутки, °C"."\n";
echo $synopConv->t_avg_last;

//Temp` varchar(5) DEFAULT NULL
echo "\n"."Минимальная температура воздуха за ночь на высоте 2 см от поверхности почвы, °C"."\n";
echo $synopConv->t_min_2cm_night;

//Temp` varchar(5) DEFAULT NULL
echo "\n"."Максимальная скорость ветра при порывах за прошедшие полусутки, м/с"."\n";
echo $synopConv->wind_gust_max_last;

//Temp` varchar(5) DEFAULT NULL
echo "\n"."Количество осадков, выпавших за сутки, мм"."\n";
echo $synopConv->precip_last;

//Temp` varchar(5) DEFAULT NULL
echo "\n"."Количество осадков за сутки, составляющее 30 мм и более, мм"."\n";
echo $synopConv->precip_last_30mm;
?>