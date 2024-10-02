<?php
/*
	===========================
	HYDRO Parser Class
	===========================

	Version: 1.0
	
	This library is based on GetWx script by Mark Woodward.

	(c) 2024, Spin Opel (https://spinopel.top/)
	(c) 2021, Arctic Code Vault Contributor (https://github.com/BENR0/python_synop/)
	(c) 2013-2020, Information Networks, Ltd. (http://www.hsdn.org/)
	(c) 2001-2006, Mark Woodward (http://woody.cowpi.com/phpscripts/)

		This script is a PHP library which allows to parse the synoptical code
	in format KN-01, and convert it to an array of data parameters. KN-01 code
	parsed using the syntactic analysis and regular expressions. It solves
	the problem of parsing the data in the presence of any error in the code KN-01.
	In addition to the return parameters, the script also displays the interpreted
	(easy to understand) information of these parameters.
*/

/*	
	WMO manual on codes:
	https://community.wmo.int/activity-areas/wis/wis-manuals

	# Syntax description
	# section 0
	# MMMM D....D YYGGggi 99LLL QLLLL

	# section 1
	# IIiii oder IIIII iihVV Nddff 00fff 1sTTT 2sTTT 3PPPP 4PPPP 5appp 6RRRt 7wwWW 8NCCC 9GGgg

	# section 2
	# 222Dv 0sTTT 1PPHH 2PPHH 3dddd 4PPHH 5PPHH 6IEER 70HHH 8sTTT

	# 333 0.... 1sTTT 2sTTT 3EsTT 4E'sss 55SSS 2FFFF 3FFFF 4FFFF 553SS 2FFFF 3FFFF 4FFFF 6RRRt 7RRRR 8NChh
	# 9SSss
	# 444 N'C'H'H'C
	# 555 0sTTT 1RRRr 2sTTT 22fff 23SS 24Wt 25ww 26fff 3LLLL 5ssst 7hhZD 8N/hh 910ff 911ff 912ff  PIC IN  BOT hsTTT
	# 80000 1RRRRW 2SSSS 3fff 4fff 5RR 6VVVVVV 7sTTT 8sTTT 9sTTTs
	# 666 1sTTT 2sTTT 3sTTT 6VVVV/VVVV 7VVVV
	# 80000 0RRRr 1RRRr 2RRRr 3RRRr 4RRRr 5RRRr
	# 999 0ddff 2sTTT 3E/// 4E'/// 7RRRz
*/

/***********BEGIN SYNOP WMO CODES STRUCTURE ******************************************************/
	# section 0
	# MMMM D....D YYGGggi 99LLL QLLLL

	# section 1
	# IIiii oder IIIII iihVV Nddff 00fff 1sTTT 2sTTT 3PPPP 4PPPP 5appp 6RRRt 7wwWW 8NCCC 9GGgg

	# section 2
	# 222Dv 0sTTT 1PPHH 2PPHH 3dddd 4PPHH 5PPHH 6IEER 70HHH 8sTTT

	# 333 0.... 1sTTT 2sTTT 3EsTT 4E'sss 55SSS 2FFFF 3FFFF 4FFFF 553SS 2FFFF 3FFFF 4FFFF 6RRRt 7RRRR 8NChh
	# 9SSss
	# 444 N'C'H'H'C
	# 555 0sTTT 1RRRr 2sTTT 22fff 23SS 24Wt 25ww 26fff 3LLLL 5ssst 7hhZD 8N/hh 910ff 911ff 912ff  PIC IN  BOT hsTTT
	# 80000 1RRRRW 2SSSS 3fff 4fff 5RR 6VVVVVV 7sTTT 8sTTT 9sTTTs
	# 666 1sTTT 2sTTT 3sTTT 6VVVV/VVVV 7VVVV
	# 80000 0RRRr 1RRRr 2RRRr 3RRRr 4RRRr 5RRRr
	# 999 0ddff 2sTTT 3E/// 4E'/// 7RRRz

/***********END SYNOP WMO CODES STRUCTURE ******************************************************/

/***********BEGIN SYNOP KH-01 STRUCTURE ******************************************************/
	# section 0
	# MMMM YYGGi IIiii

	# section 1
	# iihVV Nddff 1sTTT 2sTTT 3PPPP (4PPPP or 4ahhh) 5appp 6RRRt 7wwWW 8NCCC

	# section 3
	# 333 1sTTT 2sTTT 3EsTT 4E'sss 55SSS 6RRRt 8NChh 9SSss
	
	# section 5
	# 555 1EsTT (5sTTT) (52sTT) (530ff) 7RRR/ 88RRR

/***********END SYNOP KH-01 STRUCTURE ******************************************************/

class Synop
{
	/*
	 * Array of decoded result, by default all parameters is null.
	*/
	private $result = array
	(
		'raw'                      => NULL,
		/*
		'observed_date'            => NULL,
		'observed_day'             => NULL,
		'observed_time'            => NULL,
		'station_report'           => NULL,
		'monthdayr'                => NULL,
		'hourr'                    => NULL,
		*/
		'wind_unit'                => NULL,
		'station_id'               => NULL,
		'precip_group'             => NULL,
		'station_operation'        => NULL,
		'cloud_height'             => NULL,
		'visibility'               => NULL,
		'cloud_cover_tot'          => NULL,
		'wind_direction'           => NULL,
		'wind_speed'               => NULL,
		'temperature'              => NULL,
		'dew_point'                => NULL,
		'humidity'                 => NULL,
		'barometer_st'             => NULL,
		'barometer'                => NULL,
		'barometer_trend'          => NULL,
		'barometer_diff'           => NULL,
		'precip'                   => NULL,
		'precip_ref_time'          => NULL,
		'current_weather'          => NULL,
		'w_course1'                => NULL,
		'w_course2'                => NULL,
		'cloud_cover_lowest'       => NULL,
		'cloud_type_low'           => NULL,
		'cloud_type_medium'        => NULL,
		'cloud_type_high'          => NULL,
		'section_3'                => NULL,
		't_max'                    => NULL,
		't_min'                    => NULL,
		'grd_conditions'           => NULL,
		't_ground'                 => NULL,
		'conditions_snow'          => NULL,
		'snow_height'              => NULL,
		'sunshine_dur'             => NULL,
		'precip_section3'          => NULL,
		'precip_ref_time_section3' => NULL,
		'amount_cloudiness'        => NULL,
		'clouds_shape'             => NULL,
		'cld_low_height'           => NULL,
		'weather_addon1'           => NULL,
		'weather_addon2'           => NULL,
		'gust'                     => NULL,
		'section_5'                => NULL,
		'grd_conditions_section5'  => NULL,
		't_ground_section5'        => NULL,
		't_avg_last'               => NULL,
		't_min_2cm_night'          => NULL,
		'wind_gust_max_last'       => NULL,
		'precip_last'              => NULL,
		'precip_last_30mm'         => NULL
	);

	/*
	 * Methods used for parsing in the order of data
	*/
	private $method_names = array
	(
		'station_id',
		'station_clouds_visib',
		'clouds_wind',
		'temperature',
		'dew_point',
		'pressure_st',
		'pressure',
		'pressure_trend',
		'precipitation',
		'recent_weather',
		'clouds',
		'section_3',
		't_max',
		't_min',
		't_ground',
		'snow_report',
		'sunshine_dur',
		'precipitation_section3',
		'cloud_report',
		'w_conditions',
		'section_5',
		't_ground_section5',
		't_avg_last',
		't_min_2cm_night',
		'wind_gust_max_last',
		'precip_last',
		'precip_last_30mm'
	);

/***********BEGIN SYNOP OPTIONS ******************************************************/
	# section 0 - MMMM group
	/*
	 * Interpretation station type codes.
	*/
	/*
	private $STATION_TYPE_CODE = array
	(
		'AAXX' => 'Landstation (FM 12)',
		'BBXX' => 'Seastation (FM 13)',
		'OOXX' => 'Mobile landstation (FM 14)'
	);
	*/

	# section 0 - YYGGi group
	private $WIND_UNIT_CODE = array
	(
		"0" => "meters per second estimate",	// м/с (расчетная оценка)
		"1" => "meters per second measured",	// м/с (инструментальное измерение)
		"3" => "knots estimate",				// узлы (расчетная оценка)
		"4" => "knots measured"					// узлы (инструментальное измерение)
	);

	# section 1 - iihVV group
	private $PRECIP_GROUP_CODE = array
	(
		"0" => "Niederschlag wird in den Abschnitten 1 und 3 gemeldet",
		"1" => "Niederschlag wird nur in Abschnitt 1 gemeldet",
		"2" => "Niederschlag wird nur in Abschnitt 3 gemeldet",
		"3" => "Niederschlag nicht gemeldet -- kein Niederschlag vorhanden",
		"4" => "Niederschlag nicht gemeldet -- Niederschlagsmessung nicht durchgeführt oder nicht vorgesehen",
		"6" => "Niederschlag wird nur in Abschnitt 1 gemeldet",
		"7" => "Niederschlag wird nur in Abschnitt 3 gemeldet",
		"8" => "Niederschlag nicht gemeldet -- Niederschlagsmessung nicht durchgeführt oder nicht vorgesehen",
		"/" => NULL
	);

	# section 1 - iihVV group
	private $STATION_OPERATION_TYPE_CODE = array
	(
		"0" => NULL,
		"1" => "bemannte Station -- Wettergruppe wird gemeldet",
		"2" => "bemannte Station -- Wettergruppe nicht gemeldet -- kein signifikantes Wetter",
		"3" => "bemannte Station -- Wettergruppe nicht gemeldet -- Wetterbeobachtung nicht durchgeführt",
		"4" => "automatische Station, Typ 1 -- Wettergruppe gemeldet",
		"5" => "automatische Station, Typ 1 -- Wettergruppe nicht gemeldet -- kein signifikantes Wetter",
		"6" => "automatische Station, Typ 2 -- Wettergruppe nicht gemeldet -- Wetter nicht feststellbar",
		"7" => "automatische Station, Typ 2 -- Wettergruppe wird gemeldet"
	);

	# section 1 - iihVV group
	private $CLOUD_HEIGHT_0_CODE = array
	(
		"0" => "0 bis 49 m (0 bis 166 ft)",
		"1" => "50 bis 99 m (167 - 333 ft)",
		"2" => "100 bis 199 m (334 - 666 ft)",
		"3" => "200 bis 299 m (667 - 999 ft)",
		"4" => "300 bis 599 m (1000 - 1999 ft)",
		"5" => "600 bis 999 m (2000 - 3333 ft)",
		"6" => "1000 bis 1499 m (3334 - 4999 ft)",
		"7" => "1500 bis 1999 m (5000 - 6666 ft)",
		"8" => "2000 bis 2499 m (6667 - 8333 ft)",
		"9" => "2500 m oder höher (> 8334 ft) oder wolkenlos",
		"/" => NULL  //unbekannt
	);

	# section 1 - Nddff group, section 1 or 3 - 8NCCC group
	private $CLOUD_COVER_CODE = array
	(
		"0" => 0,		// "0/8 (wolkenlos)",
		"1" => 1,		// "1/8 oder weniger (fast wolkenlos)",
		"2" => 3,		// "2/8 (leicht bewölkt)",
		"3" => 4,		// "3/8",
		"4" => 5,		// "4/8 (wolkig)",
		"5" => 6,		// "5/8",
		"6" => 8,		// "6/8 (stark bewölkt)",
		"7" => 9,		// "7/8 oder mehr (fast bedeckt)",
		"8" => 10,		// "8/8 (bedeckt)",
		"9" => NULL,	// "Himmel nicht erkennbar",
		"/" => NULL		// "nicht beobachtet"
	);
	
	# section 1 - Nddff group
	private $WIND_DIR_DEGREES = array
	(
		"01" => "05-14",
		"02" => "15-24",
		"03" => "25-34",
		"04" => "35-44",
		"05" => "45-54",
		"06" => "55-64",
		"07" => "65-74",
		"08" => "75-84",
		"09" => "85-94",
		"10" => "95-104",
		"11" => "105-114",
		"12" => "115-124",
		"13" => "125-134",
		"14" => "135-144",
		"15" => "145-154",
		"16" => "155-164",
		"17" => "165-174",
		"18" => "175-184",
		"19" => "185-194",
		"20" => "195-204",
		"21" => "205-214",
		"22" => "215-224",
		"23" => "225-234",
		"24" => "235-244",
		"25" => "245-254",
		"26" => "255-264",
		"27" => "265-274",
		"28" => "275-284",
		"29" => "285-294",
		"30" => "295-304",
		"31" => "305-314",
		"32" => "315-324",
		"33" => "325-334",
		"34" => "335-344",
		"35" => "345-354",
		"36" => "355-04",
		"99" => "VRB",  //wind direction varies
		"00" => "calm wind",
		"//" => NULL	
	);
	
	# section 1 - Nddff group
	private $WIND_DIR_COMPASS = array
	(
		"01" => "NNE",
		"02" => "NNE",
		"03" => "NNE",
		"04" => "NNE",
		"05" => "NE",
		"06" => "ENE",
		"07" => "ENE",
		"08" => "ENE",
		"09" => "E",
		"10" => "ESE",
		"11" => "ESE",
		"12" => "ESE",
		"13" => "ESE",
		"14" => "SE",
		"15" => "SSE",
		"16" => "SSE",
		"17" => "SSE",
		"18" => "S",
		"19" => "SSW",
		"20" => "SSW",
		"21" => "SSW",
		"22" => "SSW",
		"23" => "SW",
		"24" => "WSW",
		"25" => "WSW",
		"26" => "WSW",
		"27" => "W",
		"28" => "WNW",
		"29" => "WNW",
		"30" => "WNW",
		"31" => "WNW",
		"32" => "NW",
		"33" => "NNW",
		"34" => "NNW",
		"35" => "NNW",
		"36" => "N",
		"99" => "VRB",  //wind direction varies
		"00" => "calm wind",
		"//" => NULL	
	);

	# section 1 - 5appp group
	private $A_CODE = array
	(
		"0" => 0,  //"erst steigend, dann fallend -- resultierender Druck gleich oder höher als zuvor",          //increasing
		"1" => 1,  //"erst steigend, dann gleichbleibend -- resultierender Druck höher als zuvor",               //increasing
		"2" => 2,  //"konstant steigend -- resultierender Druck höher als zuvor",                                //increasing
		"3" => 3,  //"erst fallend oder gleichbleibend, dann steigend -- resultierender Druck höher als zuvor",  //increasing
		"4" => 4,  //"gleichbleibend -- resultierender Druck unverändert",                                       //no tendency
		"5" => 5,  //"erst fallend, dann steigend -- resultierender Druck gleich oder tiefer als zuvor",         //decreasing
		"6" => 6,  //"erst fallend, dann gleichbleibend -- resultierender Druck tiefer als zuvor",               //decreasing
		"7" => 7,  //"konstant fallend -- resultierender Druck tiefer als zuvor",                                //decreasing
		"8" => 8,  //"erst steigend oder gleichbleibend, dann fallend -- resultierender Druck tiefer als zuvor", //decreasing
		""  => NULL
	);

	# section 1 or 3 - 6RRRt group
	private $T_CODE = array
	(
		"0" => "nicht aufgeführter oder vor dem Termin endender Zeitraum",
		"1" => "6 Stunden",
		"2" => "12 Stunden",
		"3" => "18 Stunden",
		"4" => "24 Stunden",
		"5" => "1 Stunde bzw. 30 Minuten (bei Halbstundenterminen)",
		"6" => "2 Stunden",
		"7" => "3 Stunden",
		"8" => "9 Stunden",
		"9" => "15 Stunden",
		"/" => "Sondermessung",
		"" => NULL
	);

	# section 1 - 7wwWW group
	private $CURRENT_WEATHER_CODE = array
	(
		"00" => NULL,		//"Bewölkungsentwicklung nicht beobachtet",
		"01" => NULL,		//"Bewölkung abnehmend",
		"02" => NULL,		//"Bewölkung unverändert",
		"03" => NULL,		//"Bewölkung zunehmend",
		"04" => "FU",		//"Sicht durch Rauch oder Asche vermindert",
		"05" => "BR",		//"trockener Dunst (relative Feuchte < 80 %)",
		"06" => "DU",		//"verbreiteter Schwebstaub, nicht vom Wind herangeführt",
		"07" => "DU",		//"Staub oder Sand bzw. Gischt, vom Wind herangeführt",
		"08" => "PO",		//"gut entwickelte Staub- oder Sandwirbel",
		"09" => "SS",		//"Staub- oder Sandsturm im Gesichtskreis, aber nicht an der Station",
		"10" => "BR",		//"feuchter Dunst (relative Feuchte > 80 %)",
		"11" => "FG",		//"Schwaden von Bodennebel",
		"12" => "FG",		//"durchgehender Bodennebel",
		"13" => "VC",		//"Wetterleuchten sichtbar, kein Donner gehört",
		"14" => "VC",		//"Niederschlag im Gesichtskreis, nicht den Boden erreichend",
		"15" => "VC",		//"Niederschlag in der Ferne (> 5 km), aber nicht an der Station",
		"16" => "VC",		//"Niederschlag in der Nähe (< 5 km), aber nicht an der Station",
		"17" => "TS",		//"Gewitter (Donner hörbar), aber kein Niederschlag an der Station",
		"18" => "SQ",		//"Markante Böen im Gesichtskreis, aber kein Niederschlag an der Station",
		"19" => "FC",		//"Tromben (trichterförmige Wolkenschläuche) im Gesichtskreis",
		"20" => "SG",		//"nach Sprühregen oder Schneegriesel",
		"21" => "RA",		//"nach Regen",
		"22" => "SN",		//"nach Schneefall",
		"23" => "FZ",		//"nach Schneeregen oder Eiskörnern",
		"24" => "FZ",		//"nach gefrierendem Regen",
		"25" => "SHRA",		//"nach Regenschauer",
		"26" => "SHSN",		//"nach Schneeschauer",
		"27" => "GR",		//"nach Graupel- oder Hagelschauer",
		"28" => "FG",		//"nach Nebel",
		"29" => "TS",		//"nach Gewitter",
		"30" => "SS",		//"leichter oder mäßiger Sandsturm, an Intensität abnehmend",
		"31" => "SS",		//"leichter oder mäßiger Sandsturm, unveränderte Intensität",
		"32" => "SS",		//"leichter oder mäßiger Sandsturm, an Intensität zunehmend",
		"33" => "SS",		//"schwerer Sandsturm, an Intensität abnehmend",
		"34" => "SS",		//"schwerer Sandsturm, unveränderte Intensität",
		"35" => "SS",		//"schwerer Sandsturm, an Intensität zunehmend",
		"36" => "DR",		//"leichtes oder mäßiges Schneefegen, unter Augenhöhe",
		"37" => "DR",		//"starkes Schneefegen, unter Augenhöhe",
		"38" => "BL",		//"leichtes oder mäßiges Schneetreiben, über Augenhöhe",
		"39" => "BL",		//"starkes Schneetreiben, über Augenhöhe",
		"40" => "FG",		//"Nebel in einiger Entfernung",
		"41" => "FG",		//"Nebel in Schwaden oder Bänken",
		"42" => "FG",		//"Nebel, Himmel erkennbar, dünner werdend",
		"43" => "FG",		//"Nebel, Himmel nicht erkennbar, dünner werdend",
		"44" => "FG",		//"Nebel, Himmel erkennbar, unverändert",
		"45" => "FG",		//"Nebel, Himmel nicht erkennbar, unverändert",
		"46" => "FG",		//"Nebel, Himmel erkennbar, dichter werdend",
		"47" => "FG",		//"Nebel, Himmel nicht erkennbar, dichter werdend",
		"48" => "FG",		//"Nebel mit Reifansatz, Himmel erkennbar",
		"49" => "FG",		//"Nebel mit Reifansatz, Himmel nicht erkennbar",
		"50" => "DZ",		//"unterbrochener leichter Sprühregen",
		"51" => "DZ",		//"durchgehend leichter Sprühregen",
		"52" => "DZ",		//"unterbrochener mäßiger Sprühregen",
		"53" => "DZ",		//"durchgehend mäßiger Sprühregen",
		"54" => "DZ",		//"unterbrochener starker Sprühregen",
		"55" => "DZ",		//"durchgehend starker Sprühregen",
		"56" => "DZ",		//"leichter gefrierender Sprühregen",
		"57" => "DZ",		//"mäßiger oder starker gefrierender Sprühregen",
		"58" => "DZ",		//"leichter Sprühregen mit Regen",
		"59" => "DZ",		//"mäßiger oder starker Sprühregen mit Regen",
		"60" => "RA",		//"unterbrochener leichter Regen oder einzelne Regentropfen",
		"61" => "RA",		//"durchgehend leichter Regen",
		"62" => "RA",		//"unterbrochener mäßiger Regen",
		"63" => "RA",		//"durchgehend mäßiger Regen",
		"64" => "RA",		//"unterbrochener starker Regen",
		"65" => "RA",		//"durchgehend starker Regen",
		"66" => "RA",		//"leichter gefrierender Regen",
		"67" => "RA",		//"mäßiger oder starker gefrierender Regen",
		"68" => "SNRA",		//"leichter Schneeregen",
		"69" => "SNRA",		//"mäßiger oder starker Schneeregen",
		"70" => "SN",		//"unterbrochener leichter Schneefall oder einzelne Schneeflocken",
		"71" => "SN",		//"durchgehend leichter Schneefall",
		"72" => "SN",		//"unterbrochener mäßiger Schneefall",
		"73" => "SN",		//"durchgehend mäßiger Schneefall",
		"74" => "SN",		//"unterbrochener starker Schneefall",
		"75" => "SN",		//"durchgehend starker Schneefall",
		"76" => "IC",		//"Eisnadeln (Polarschnee)",
		"77" => "SG",		//"Schneegriesel",
		"78" => "SG",		//"Schneekristalle",
		"79" => "RA",		//"Eiskörner (gefrorene Regentropfen)",
		"80" => "SHRA",		//"leichter Regenschauer",
		"81" => "SHRA",		//"mäßiger oder starker Regenschauer",
		"82" => "SHRA",		//"äußerst heftiger Regenschauer",
		"83" => "SNRA",		//"leichter Schneeregenschauer",
		"84" => "SNRA",		//"mäßiger oder starker Schneeregenschauer",
		"85" => "SHSN",		//"leichter Schneeschauer",
		"86" => "SHSN",		//"mäßiger oder starker Schneeschauer",
		"87" => "PE",		//"leichter Graupelschauer",
		"88" => "GS",		//"mäßiger oder starker Graupelschauer",
		"89" => "GR",		//"leichter Hagelschauer",
		"90" => "GR",		//"mäßiger oder starker Hagelschauer",
		"91" => "TS",		//"Gewitter in der letzten Stunde, zurzeit leichter Regen",
		"92" => "TS",		//"Gewitter in der letzten Stunde, zurzeit mäßiger oder starker Regen",
		"93" => "TS",		//"Gewitter in der letzten Stunde, zurzeit leichter Schneefall/Schneeregen/Graupel/Hagel",
		"94" => "TS",		//"Gewitter in der letzten Stunde, zurzeit mäßiger oder starker Schneefall/Schneeregen/Graupel/Hagel",
		"95" => "TS",		//"leichtes oder mäßiges Gewitter mit Regen oder Schnee",
		"96" => "TS",		//"leichtes oder mäßiges Gewitter mit Graupel oder Hagel",
		"97" => "TS",		//"starkes Gewitter mit Regen oder Schnee",
		"98" => "TS",		//"starkes Gewitter mit Sandsturm",
		"99" => "TS",		//"starkes Gewitter mit Graupel oder Hagel",
		"" => NULL
	);

	# section 1 - 7wwWW group
	private $WEATHER_COURSE_CODE = array
	(
		"0" => "Wolkendecke stets weniger als oder genau die Hälfte bedeckend (0-4/8)",
		"1" => "Wolkendecke zeitweise weniger oder genau, zeitweise mehr als die Hälfte bedeckend (</> 4/8)",
		"2" => "Wolkendecke stets mehr als die Hälfte bedeckend (5-8/8)",
		"3" => "Staubsturm, Sandsturm oder Schneetreiben",
		"4" => "Nebel oder starker Dunst",
		"5" => "Sprühregen",
		"6" => "Regen",
		"7" => "Schnee oder Schneeregen",
		"8" => "Schauer",
		"9" => "Gewitter",
		"" => NULL
	);


	# section 1 - 8NCCC group
	private $LOW_CLOUDS_CODE = array
	(
		"0" => "keine tiefen Wolken",
		"1" => "Cumulus humilis oder fractus (keine vertikale Entwicklung)",
		"2" => "Cumulus mediocris oder congestus (mäßige vertikale Entwicklung)",
		"3" => "Cumulonimbus calvus (keine Umrisse und kein Amboß)",
		"4" => "Stratocumulus cumulogenitus (entstanden durch Ausbreitung von Cumulus)",
		"5" => "Stratocumulus",
		"6" => "Stratus nebulosus oder fractus (durchgehende Wolkenfläche)",
		"7" => "Stratus fractus oder Cumulus fractus (Fetzenwolken bei Schlechtwetter)",
		"8" => "Cumulus und Stratocumulus (in verschiedenen Höhen)",
		"9" => "Cumulonimbus capillatus (mit Amboß)",
		"/" => "tiefe Wolken nicht erkennbar wegen Nebel, Dunkel- oder Verborgenheit",
		"" => NULL
	);

	# section 1 - 8NCCC group
	private $MEDIUM_CLOUDS_CODE = array
	(
		"0" => "keine mittelhohen Wolken",
		"1" => "Altostratus translucidus (meist durchsichtig)",
		"2" => "Altostratus opacus oder Nimbostratus",
		"3" => "Altocumulus translucidus (meist durchsichtig)",
		"4" => "Bänke von Altocumulus (unregelmäßig, lentikular)",
		"5" => "Bänder von Altocumulus (den Himmel fortschreitend überziehend)",
		"6" => "Altocumulus cumulogenitus (entstanden durch Ausbreitung von Cumulus)",
		"7" => "Altocumulus (mehrschichtig oder zusammen mit Altostratus/Nimbostratus)",
		"8" => "Altocumulus castellanus oder floccus (cumuliforme Büschel aufweisend)",
		"9" => "Altocumulus eines chaotisch aussehenden Himmels",
		"/" => "mittelhohe Wolken nicht erkennbar wegen Nebel, Dunkel- oder Verborgenheit",
		"" => NULL
	);

	# section 1 - 8NCCC group
	private $HIGH_CLOUDS_CODE = array
	(
		"0" => "keine hohen Wolken",
		"1" => "Cirrus fibratus oder uncinus (büschelartig)",
		"2" => "Cirrus spissatus, castellanus oder floccus (dicht, in Schwaden)",
		"3" => "Cirrus spissatus cumulogenitus (aus einem Amboß entstanden)",
		"4" => "Cirrus uncinus oder fibratus (den Himmel zunehmend oder fortschreitend überziehend)",
		"5" => "Bänder von zunehmendem Cirrus oder Cirrostratus (nicht höher als 45 Grad über dem Horizont)",
		"6" => "Bänder von zunehmendem Cirrus oder Cirrostratus (mehr als 45 Grad über dem Horizont, den Himmel nicht ganz bedeckend)",
		"7" => "Cirrostratus (den Himmel stets ganz bedeckend)",
		"8" => "Cirrostratus (den Himmel nicht ganz bedeckend, aber auch nicht zunehmend)",
		"9" => "Cirrocumulus",
		"/" => "hohe Wolken nicht erkennbar wegen Nebel, Dunkel- oder Verborgenheit",
		"" => NULL
	);

	# section 3 - 3EsTT group, section 5 - 1EsTT group
	private $ESTT_GROUND_CONDITIONS_CODE = array
	(
		"0" => "trocken",
		"1" => "feucht",
		"2" => "naß",
		"3" => "überflutet",
		"4" => "gefroren",
		"5" => "Glatteis oder Eisglätte (mindestens 50 % des Erdbodens bedeckend)",
		"6" => "loser, trockener Sand, den Boden nicht vollständig bedeckend",
		"7" => "geschlossene dünne Sandschicht, den Boden vollständig bedeckend",
		"8" => "geschlossene dicke Sandschicht, den Boden vollständig bedeckend",
		"9" => "extrem trockener Boden mit Rissen",
		"/" => NULL
	);

	# section 3 - 4E'sss group
	private $ESSS_GROUND_CONDITIONS_CODE = array
	(
		"0" => "vorwiegend (> 50 %) mit Eis bedeckt (Hagel-/Graupel-/Grieseldecke)",
		"1" => "kompakter oder nasser Schnee, weniger als die Hälfte des Bodens bedeckend (Fl)",
		"2" => "kompakter oder nasser Schnee, mehr als die Hälfte, aber den Boden nicht vollständig bedeckend (dbr)",
		"3" => "ebene Schicht kompakten oder nassen Schnees, den gesamten Boden bedeckend",
		"4" => "unebene Schicht kompakten oder nassen Schneess, den gesamten Boden bedeckend",
		"5" => "loser, trockener Schnee, weniger als die Hälfte des Bodens bedeckend (Fl)",
		"6" => "loser, trockener Schnee, mehr als die Hälfte, aber den Boden nicht vollständig bedeckend (dbr)",
		"7" => "ebene Schicht losen, trockenen Schnees, den gesamten Boden bedeckend",
		"8" => "unebene Schicht losen, trockenen Schnees, den gesamten Boden bedeckend",
		"9" => "vollständig geschlossene Schneedecke mit hohen Verwehungen (> 50 cm)",
		"/" => "Reste (< 10 %) von Schnee oder Eis (Hagel/Graupel/Griesel)",
		"" => NULL
	);

	# section 3 - 8NChh group
	private $CLOUD_TYPE_CODE = array
	(
		"0" => "Cirrus (Ci)",
		"1" => "Cirrocumulus (Cc)",
		"2" => "Cirrostratus (Cs)",
		"3" => "Altocumulus (Ac)",
		"4" => "Altostratus (As)",
		"5" => "Nimbostratus (Ns)",
		"6" => "Stratocumulus (Sc)",
		"7" => "Stratus (St)",
		"8" => "Cumulus (Cu)",
		"9" => "Cumulonimbus (Cb)",
		"/" => "Wolkengattung nicht erkennbar"
	);
	
	/*
	# section 3 - 8NChh group
	private $CLOUD_HEIGHT_CLASSES = array
	(
		90 => 0,  //<50
		91 => 50,
		92 => 100,
		93 => 200,
		94 => 300,
		95 => 600,
		96 => 1000,
		97 => 1500,
		98 => 2000,
		99 => 2500
	);
	*/
/***********END SYNOP OPTIONS ******************************************************/

	/*
	 * Debug and parse errors information.
	*/
	private $errors = NULL;
	private $debug  = NULL;
	private $debug_enabled;

	/*
	 * Other variables.
	*/
	private $raw;
	private $raw_parts = array();
	private $method    = 0;
	private $part      = 0;


	/**
	 * This method provides SYNOP information, you want to parse.
	 *
	 * Examples of raw SYNOP for test:
	 * 201809051400 AAXX 10001 33041 42968 00000 10013 21028 30093 40253 52001 555 14100
	 * 201809051400 AAXX 10001 34363 62597 42005 10051 20001 39957 40108 52008 69932 82530 333 20048 31003 46997 55080 86714 91111 555 19020 50057 52001 53012 7035/ 88036=
	 * 201809051400 AAXX 10001 13624 41660 81402 10048 20041 30102 40142 52010 76162 8453/ 333 84633 84360=
	 * 201809051400 AAXX 10001 38799 13/// ///// 5//// 6//// 333 2//// 3//// 555 5//// 52/// 530// 7////=
	*/
	public function __construct($raw, $debug = FALSE)
	{
		$this->debug_enabled = $debug;
		
		if (empty($raw))
		{
			throw new Exception('The SYNOP information is not presented.');
		}

		$raw_lines = explode("\n", $raw, 2);

		if (isset($raw_lines[1]))
		{
			$raw = trim($raw_lines[1]);

			// Get observed time from a file data
			/*
			$observed_time = strtotime(trim($raw_lines[0]));

			if ($observed_time != 0)
			{
				$this->set_observed_date($observed_time);

				$this->set_debug('Observation date is set from the SYNOP in first line of the file content: '.trim($raw_lines[0]));
			}
			*/
		}
		else
		{
			$raw = trim($raw_lines[0]);
		}

		$this->raw = rtrim(trim(preg_replace('/[\s\t]+/s', ' ', $raw)), '=');

		/*
		if ($taf)
		{
			$this->set_debug('Infromation presented as TAF or trend.');
		}
		else
		{
			$this->set_debug('Infromation presented as SYNOP.');
		}
		*/
		$this->set_debug('Infromation presented as SYNOP.');

		//$this->set_result_value('taf', $taf);
		$this->set_result_value('raw', $this->raw);
	}

	/**
	 * Gets the value from result array as class property.
	*/
	public function __get($parameter)
	{
		if (isset($this->result[$parameter]))
		{
			return $this->result[$parameter];
		}

		return NULL;
	}

	/**
	 * Parses the SYNOP information and returns result array.
	*/
	public function parse()
	{
		$this->raw_parts = explode(' ', $this->raw);

		$current_method = 0;

		// See parts
		while ($this->part < sizeof($this->raw_parts))
		{
			$this->method = $current_method;

			// See methods
			while ($this->method < sizeof($this->method_names))
			{
				$method = 'get_'.$this->method_names[$this->method];
				$token  = $this->raw_parts[$this->part];

				if ($this->$method($token) === TRUE)
				{
					$this->set_debug('Token "'.$token.'" is parsed by method: '.$method.', '.
						($this->method - $current_method).' previous methods skipped.');

					$current_method = $this->method;

					$this->method++;

					break;
				}

				$this->method++;
			}

			if ($current_method != $this->method - 1)
			{
				$this->set_error('Unknown token: '.$this->raw_parts[$this->part]);
				$this->set_debug('Token "'.$this->raw_parts[$this->part].'" is NOT PARSED, '.
						($this->method - $current_method).' methods attempted.');
			}

			$this->part++;
		}

		// Delete null values from the TAF report
		/*
		if ($this->result['taf'] === TRUE)
		{
			foreach ($this->result as $parameter => $value)
			{
				if (is_null($value))
				{
					unset($this->result[$parameter]);
				}
			}
		}
		*/

		return $this->result;
	}

	/**
	 * Returns array with debug information.
	*/
	public function debug()
	{
		return $this->debug;
	}

	/**
	 * Returns array with parse errors.
	*/
	public function errors()
	{
		return $this->errors;
	}

	/**
	 * Sets the new value to parameter in result array.
	*/
	private function set_result_value($parameter, $value, $only_is_null = FALSE)
	{
		if ($only_is_null)
		{
			if (is_null($this->result[$parameter]))
			{
				$this->result[$parameter] = $value;

				$this->set_debug('Set value "'.$value.'" ('.gettype($value).') for null parameter: '.$parameter);
			}
		}
		else
		{
			$this->result[$parameter] = $value;

			$this->set_debug('Set value "'.$value.'" ('.gettype($value).') for parameter: '.$parameter);
		}
	}

	/**
	 * Sets the data group to parameter in result array.
	*/
	private function set_result_group($parameter, $group)
	{
		if (is_null($this->result[$parameter]))
		{
			$this->result[$parameter] = array();
		}

		array_push($this->result[$parameter], $group);

		$this->set_debug('Add new group value ('.gettype($group).') for parameter: '.$parameter);
	}

	/**
	 * Sets the report text to parameter in result array.
	*/
	private function set_result_report($parameter, $report, $separator = ';')
	{
		$this->result[$parameter] .= $separator.' '.$report;

		if (!is_null($this->result[$parameter]))
		{
			$this->result[$parameter] = ucfirst(ltrim($this->result[$parameter], ' '.$separator));
		}

		$this->set_debug('Add group report value "'.$report.'" for parameter: '.$parameter);
	}

	/**
	 * Adds the debug text to debug information array.
	*/
	private function set_debug($text)
	{
		if ($this->debug_enabled)
		{
			if (is_null($this->debug))
			{
				$this->debug = array();
			}

			array_push($this->debug, $text);
		}
	}

	/**
	 * Adds the error text to parse errors array.
	*/
	private function set_error($text)
	{
		if (is_null($this->errors))
		{
			$this->errors = array();
		}

		array_push($this->errors, $text);
	}

	// --------------------------------------------------------------------
	// Methods for parsing raw parts
	// --------------------------------------------------------------------

	/**
	 * Decodes observation time.
	 * Format is YYYYMMddhhmm where YYYY = year, MM = month, dd = day, hh = hours, mm = minutes in UTC time.
	*/
	/*
	private function get_time($part)
	{
		//if (!preg_match('@^([0-9]{2})([0-9]{2})([0-9]{2})Z$@', $part, $found))
		if (!preg_match('@^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$@', $part, $found))
		{
			return FALSE;
		}

		$year   = intval($found[1]);
		$month  = intval($found[2]);
		$day    = intval($found[3]);
		$hour   = intval($found[4]);
		$minute = intval($found[5]);

		if (is_null($this->result['observed_date']))
		{
			// Get observed time from a SYNOP part
			//$observed_time = mktime($hour, $minute, 0, date('n'), $day, date('Y'));
			$observed_time = mktime($hour, $minute, 0, 1*$month, $day, $year);

			// Take one month, if the observed day is greater than the current day
			if ($day > date('j'))
			{
				$observed_time = strtotime('-1 month');
			}

			$this->set_observed_date($observed_time);

			$this->set_debug('Observation date is set from the SYNOP information (presented in format: YYYYMMddhhmm)');
		}

		$this->set_result_value('observed_day', $day);
		$this->set_result_value('observed_time', $hour.':'.$minute.' UTC');

		$this->method++;

		return TRUE;
	}
	*/

	/**
	 * Decodes station type code.
	 * section 0 - MMMM group
	 * A SYNOP report (FM12) from a ﬁxed land station is identiﬁed by the symbolic letters MiMiMjMj = AAXX.
	 * A SHIP report (FM13) from a sea station is identiﬁed by the symbolic letters MiMiMjMj = BBXX.
	 * A SYNOP MOBIL (FM14) report from a mobile land station is identiﬁed by the symbolic letters MiMiMjMj = OOXX.
	 
	 * Parameters
	 * ----------
	 * MMMM: 
	*/
	/*
	private function get_station($part)
	{
		if (!preg_match('@^(AAXX|BBXX|OOXX)$@', $part, $found))
		{
			return FALSE;
		}

		$this->set_result_value('station_report', $this->STATION_TYPE_CODE[$found[1]]);

		$this->method++;

		return TRUE;
	}
	*/

	/**
	 * Ignore station type if present.
	*/
	/*
	private function get_station_type($part)
	{
		if ($part != 'AUTO' AND $part != 'COR')
		{
			return FALSE;
		}

		$this->method++;

		return TRUE;
	}
	*/
	
	/**
	 * Decodes .
	 * section 0 - YYGGi group
	 
	 * Parameters
	 * ----------
	 * YY: 
	 * GG: 
	 * i: 
	*/
	/*
	private function get_station_type($part)
	{
		if (!preg_match('@^([0-9]{2})([0-9]{2})([0-9]{1})$@', $part, $found))
		{
			return FALSE;
		}

		$wind_unit = $this->WIND_UNIT_CODE[$found[3]];

		$this->set_result_value('monthdayr', $found[1]);
		$this->set_result_value('hourr', $found[2]);
		$this->set_result_value('wind_unit', $wind_unit);

		$this->method++;

		return TRUE;
	}
	*/
	
	/**
	 * Decodes station id.
	 * section 0 - IIiii group
	 
	 * Parameters
	 * ----------
	 * II: 
	 * iii: 
	*/
	private function get_station_id($part)
	{
		if (!preg_match('@^([0-9]{5})$@', $part, $found))
		{
			return FALSE;
		}

		$this->set_result_value('station_id', $found[1]);

		$this->method++;

		return TRUE;
	}
	
	/**
	 * Decodes horizontal visibility in km.
	 * section 1 - iihVV group
	 
	 * Parameters
	 * ----------
	 * VV: horizontal visibility in km
	*/
    private function set_visibility($code) {
        // Decode visibility of synop report
		// Visibility in km
		
		//visibility assessed visually 
		$vislut = array(
			90 => 0,
			91 => 0.05,
			92 => 0.2,
			93 => 0.5,
			94 => 1,
			95 => 2,
			96 => 4,
			97 => 10,
			98 => 20,
			99 => 50
		);

        if ($code == "//") {
            $code = NULL;  //not observed
		}
        else {
			$code = intval($code);
		}
			
		//not used 51-55
        if ($code <= 50) {
            $dist = 0.1 * $code;
		}
        elseif ($code >= 51 and $code <= 55) {
            $dist = NULL;
		}
        elseif ($code >= 56 and $code <= 80) {
            $dist = 6 + ($code - 56) * 1;
		}
        elseif ($code >= 81 and $code <= 89) {
            $dist = 35 + ($code - 81) * 5;
		}
        else {
            $dist = $vislut[$code];
		}
		
        return $dist;
	}
	
	/**
	 * Decodes .
	 * section 1 - iihVV group
	 
	 * Parameters
	 * ----------
	 * ir: check 6RRRt group, precipitation group indicator
	 * ix: check 7wwWW group, station type, weather group indicator
	 * h: cloud base of lowest observed cloud
	 * VV: horizontal visibility in km
	*/
	private function get_station_clouds_visib($part)
	{
		if (!preg_match('@^([0-9/]{1})([0-7]{1})([0-9/]{1})([0-9/]{2})$@', $part, $found))
		{
			return FALSE;
		}
		
		$precip_group = $this->PRECIP_GROUP_CODE[$found[1]];
		$station_operation = $this->STATION_OPERATION_TYPE_CODE[$found[2]];
		$cloud_height = $this->CLOUD_HEIGHT_0_CODE[$found[3]];
		$visibility = $this->set_visibility($found[4]);

		$this->set_result_value('precip_group', $precip_group);
		$this->set_result_value('station_operation', $station_operation);
		$this->set_result_value('cloud_height', $cloud_height);
		$this->set_result_value('visibility', $visibility);

		$this->method++;

		return TRUE;
	}
	
	/**
	 * Decodes total cloud cover, wind direction and speed information.
	 * section 1 - Nddff group
	 
	 * Parameters
	 * ----------
	 * N: total cloud cover in okta
	 * dd: wind direction in dekadegree (10 minute mean)
	 * ff: wind speed (10 minute mean)
	*/
	private function get_clouds_wind($part)
	{
		if (!preg_match('@^([0-9/]{1})([0-9/]{2})([0-9/]{2})$@', $part, $found))
		{
			return FALSE;
		}
		
		$cloud_cover = $this->CLOUD_COVER_CODE[$found[1]];

		$wind_dir_code = $found[2];
        if (isset($this->WIND_DIR_COMPASS[$wind_dir_code])) {
			//$wind_dir = $this->WIND_DIR_DEGREES[$wind_dir_code]);  // in degrees
			$wind_dir = $this->WIND_DIR_COMPASS[$wind_dir_code];  // in rhumb
		}
        else {
            $wind_dir = $this->WIND_DIR_COMPASS[99];  //wind direction varies
		}

        //wind speed is greater than 99 units and this group is directly followed by the 00fff group
		$wind_speed_code = $found[3];
        if ($wind_speed_code == "" || $wind_speed_code == "//") {
            $wind_speed = NULL;  //not observed
		}
        else {
			$wind_speed = intval($wind_speed_code);  // in mps
		}

		$this->set_result_value('cloud_cover_tot', $cloud_cover);
		$this->set_result_value('wind_direction', $wind_dir);
		$this->set_result_value('wind_speed', $wind_speed);

		$this->method++;

		return TRUE;
	}
	
	/**
	 * Decodes temperature and dew point information. All units are
	 * in Celsius. A 's' preceeding the TTT indicates a sign of temperature.
	 * section 1 - 1sTTT 2sTTT group
	 
	 * Parameters
	 * ----------
	 * code : str
	 * 	Temperature with first charater defining the sign or
	 * 	type of unit (°C or relative humidity in % for dewpoint)

	 * Returns
	 * -------
	 * float
	 * 	Temperature in degree Celsius
	*/
	private function set_sTTT($code) {
        if (strpos($code, "/") !== false) {
            return NULL;
		}
        else {
            $sign = intval(substr($code, 0, 1));
            $value = intval(substr($code, 1));

            if ($sign == 0) {
                $sign = 1;
			}
            elseif ($sign == 1) {
                $sign = -1;
			}
            //relative humidity in section 1 - 2sTTT group
            elseif ($sign == 9) {
                return $value;
			} else {
				return NULL;
			}

			// if sTTT
            if (strlen($code) == 4) {
				$value = $sign * $value * 0.1;
			}
			// if sTT
			else {
				$value = $sign * $value;
			}

            return $value;
		}
	}
	
	/**
	 * Decodes temperature information.
	 * section 1 - 1sTTT group
	 
	 * Parameters
	 * ----------
	 * s: sign of the data, and relative humidity indicator
	 * TTT: temperature value without of sign
	 
	 * Returns
	 * -------
	 * float type
	 * 	Temperature in degree Celsius
	*/
	private function get_temperature($part)
	{
		if (!preg_match('@^1([01]{1}[0-9]{3})$@', $part, $found))
		{
			return FALSE;
		}
		
		$temperature = $this->set_sTTT($found[1]);

		$this->set_result_value('temperature', $temperature);

		$this->method++;

		return TRUE;
	}
	
	/**
	 * Decodes dew point or humidity information.
	 * Some stations do not report dew point or humidity.
	 * section 1 - 2sTTT group
	 
	 * Parameters
	 * ----------
	 * s: sign of the data, and relative humidity indicator
	 * TTT: temperature value without of sign or humidity in percent
	 
	 * Returns
	 * -------
	 * float type
	 * 	Temperature in degree Celsius or humidity in percent
	*/
	private function get_dew_point($part)
	{
		if (!preg_match('@^2([019]{1}[0-9]{3})$@', $part, $found))
		{
			return FALSE;
		}
		
		if (intval(substr($found[1], 0, 1)) == 9) {
			$dew_point = NULL;
			$humidity = $this->set_sTTT($found[1]);
			if ($humidity > 100) {
				$humidity = NULL;
			}
		}
		else {
			$dew_point = $this->set_sTTT($found[1]);
			$humidity = NULL;
		}

		$this->set_result_value('dew_point', $dew_point);
		$this->set_result_value('humidity', $humidity);

		$this->method++;

		return TRUE;
	}
	
	/**
	 * Decode pressure.
	 * section 1 - 3PPPP 4PPPP group

	 * Parameters
	 * ----------
	 * code : str
	 *     Pressure code without thousands in  1/10 Hectopascal.
	 *     If last character of code is "/" pressure is given as
	 *     full Hectopascal.

	 * Returns
	 * -------
	 * float
	 * 	Pressure in Hectopascal
	*/
	private function set_PPPP($code) {
        if ($code == "") {
            return NULL;
		}
        else {
            if (substr($code, -1) == "/") {
                $value = intval(substr($code, 0, -1));
			}
            else {
                $value = intval($code) * 0.1;
			}

			// if code "0218", pressure equal 1021.8 gPa
			// if code "9999", pressure equal 999.9 gPa
            if (strlen($code) == 4 && substr($code, 0, 1) == 0) {
				$value = 1000 + $value;
			}

            return $value;
		}
	}

	/**
	 * Decodes altimeter or barometer information regarding the station.
	 * section 1 - 3PPPP group
	 
	 * Some other common conversion factors:
	 *   1 millibar = 1 hPa
	 *   1 in Hg    = 0.02953 hPa
	 *   1 mm Hg    = 25.4 in Hg     = 0.750062 hPa
	 *   1 lb/sq in = 0.491154 in Hg = 0.014504 hPa
	 *   1 atm      = 0.33421 in Hg  = 0.0009869 hPa
	 
		//$this->set_result_value('barometer', $pressure); // units are hPa
		//$this->set_result_value('barometer_in', round(0.02953 * $pressure, 2)); // convert to in Hg
	*/
	private function get_pressure_st($part)
	{
		if (!preg_match('@^3([0-9]{4})$@', $part, $found))
		{
			return FALSE;
		}
		
		$barometer_st = $this->set_PPPP($found[1]);

		$this->set_result_value('barometer_st', $barometer_st);

		$this->method++;

		return TRUE;
	}

	/**
	 * Decodes altimeter or barometer information regarding the sea level.
	 * section 1 - 4PPPP group
	 
	 * Some other common conversion factors:
	 *   1 millibar = 1 hPa
	 *   1 in Hg    = 0.02953 hPa
	 *   1 mm Hg    = 25.4 in Hg     = 0.750062 hPa
	 *   1 lb/sq in = 0.491154 in Hg = 0.014504 hPa
	 *   1 atm      = 0.33421 in Hg  = 0.0009869 hPa
	 
		//$this->set_result_value('barometer', $pressure); // units are hPa
		//$this->set_result_value('barometer_in', round(0.02953 * $pressure, 2)); // convert to in Hg
	*/
	private function get_pressure($part)
	{
		if (!preg_match('@^4([0-9]{4})$@', $part, $found))
		{
			return FALSE;
		}
		
		$barometer = $this->set_PPPP($found[1]);

		$this->set_result_value('barometer', $barometer);

		$this->method++;

		return TRUE;
	}

	/**
	 * Interpretation of tendency codes. 3 hourly tendency of station air pressure.
	 * section 1 - 5appp group
	
	 * Parameters
	 * ----------
	 * a: type of pressure tendency
	 * ppp: absolute pressure change over last three hours in 1/10 Hectopascal
	*/
	private function get_pressure_trend($part)
	{
		if (!preg_match('@^5([0-9]{1})([0-9]{3})$@', $part, $found))
		{
			return FALSE;
		}
		
		$barometer_trend = $this->A_CODE[$found[1]];
		$barometer_diff = $this->set_PPPP($found[2]);

		$this->set_result_value('barometer_trend', $barometer_trend);
		$this->set_result_value('barometer_diff', $barometer_diff);

		$this->method++;

		return TRUE;
	}

	/**
	 * Decodes amount of melted precipitation.
	 * section 1 - 6RRRt group
	 
	 * Parameters
	 * ----------
	 * RRR: precipitation amount in mm
	 * t: reference time
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_precipitation($part)
	{
		if (!preg_match('@^6([0-9/]{3})([0-9]{1})$@', $part, $found))
		{
			return FALSE;
		}
		
        if ($found[1] == "///") {
            $precip = NULL;
		}
        else {
			$precip = intval($found[1]);
			if ($precip >= 990 && $precip <= 999) {
				$precip = ($precip - 990) * 0.1;
				if ($precip == 0) {
					//only traces of precipitation not measurable < 0.05
					$precip = 0.05;
				}
			}
		}
		$precip_ref_time = $this->T_CODE[$found[2]];

		$this->set_result_value('precip', $precip);
		$this->set_result_value('precip_ref_time', $precip_ref_time);

		$this->method++;

		return TRUE;
	}

	/**
	 * Decodes current weather and weather course.
	 * section 1 - 7wwWW group
	 
	 * Parameters
	 * ----------
	 * ww: current weather
	 * W: weather course (W1)
	 * W: weather course (W2)
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_recent_weather($part)
	{
		if (!preg_match('@^7([0-9]{2})([0-9]{1})([0-9]{1})$@', $part, $found))
		{
			return FALSE;
		}
		
		$current_weather = $this->CURRENT_WEATHER_CODE[$found[1]];
		$w_course1 = $this->WEATHER_COURSE_CODE[$found[2]];
		$w_course2 = $this->WEATHER_COURSE_CODE[$found[3]];

		$this->set_result_value('current_weather', $current_weather);
		$this->set_result_value('w_course1', $w_course1);
		$this->set_result_value('w_course2', $w_course2);

		$this->method++;

		return TRUE;
	}

	/**
	 * Decodes information about cloud types.
	 * section 1 - 8NCCC group
	 
	 * Parameters
	 * ----------
	 * N: cover of low clouds if not present amount of medium high clouds
	 * C: type of low clouds (CL)
	 * C: type of medium clouds (CM)
	 * C: type of high clouds (CH)
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_clouds($part)
	{
		if (!preg_match('@^8([0-9]{1})([0-9/]{1})([0-9/]{1})([0-9/]{1})$@', $part, $found))
		{
			return FALSE;
		}
		
		$cloud_cover_lowest = $this->CLOUD_COVER_CODE[$found[1]];
		$cloud_type_low = $this->LOW_CLOUDS_CODE[$found[2]];
		$cloud_type_medium = $this->MEDIUM_CLOUDS_CODE[$found[3]];
		$cloud_type_high = $this->HIGH_CLOUDS_CODE[$found[4]];

		$this->set_result_value('cloud_cover_lowest', $cloud_cover_lowest);
		$this->set_result_value('cloud_type_low', $cloud_type_low);
		$this->set_result_value('cloud_type_medium', $cloud_type_medium);
		$this->set_result_value('cloud_type_high', $cloud_type_high);

		$this->method++;

		return TRUE;
	}

	/**
	 * Interpretation section 3.
	 * section 3 - 333 1sTTT 2sTTT 3EsTT 4E'sss 55SSS 6RRRt 8NChh 9SSss
	 
	 * Parameters
	 * ----------
	 * 
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_section_3($part)
	{
		if (!preg_match('@^333$@', $part, $found))
		{
			return FALSE;
		}
		
		$this->set_result_value('section_3', TRUE); // section 3 exists
		
		$this->method++;

		return TRUE;
	}
	
	/**
	 * Decodes maximum temperature information of the day.
	 * section 3 - 1sTTT group
	 
	 * Parameters
	 * ----------
	 * s: sign of the data, and relative humidity indicator
	 * TTT: 
	 
	 * Returns
	 * -------
	 * float type
	 * 	Temperature in degree Celsius
	*/
	private function get_t_max($part)
	{
		if ($this->result['section_3'] === TRUE) {
			if (!preg_match('@^1([0-9]{4})$@', $part, $found))
			{
				return FALSE;
			}
			
			$temperature = $this->set_sTTT($found[1]);

			$this->set_result_value('t_max', $temperature);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes minimum temperature information of the night.
	 * section 3 - 2sTTT group
	 
	 * Parameters
	 * ----------
	 * s: sign of the data, and relative humidity indicator
	 * TTT: 
	 
	 * Returns
	 * -------
	 * float type
	 * 	Temperature in degree Celsius
	*/
	private function get_t_min($part)
	{
		if ($this->result['section_3'] === TRUE) {
			if (!preg_match('@^2([0-9]{4})$@', $part, $found))
			{
				return FALSE;
			}
			
			$temperature = $this->set_sTTT($found[1]);

			$this->set_result_value('t_min', $temperature);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes ground temperature information of the night.
	 * 12 respectively 15 hour minimum temperature 5cm above ground/snow cover.
	 * section 3 - 3EsTT group
	 
	 * Parameters
	 * ----------
	 * E: condition of the ground without the snow respectively
	 * s: sign of the data, and relative humidity indicator
	 * TT: temperature only reported at 06, 09, and 18 UTC. /// at 00 and 12 UTC
	 
	 * Returns
	 * -------
	 * float type
	 * 	Temperature in degree Celsius
	*/
	private function get_t_ground($part)
	{
		if ($this->result['section_3'] === TRUE) {
			if (!preg_match('@^3([0-9/]{1})([0-9]{3})$@', $part, $found))
			{
				return FALSE;
			}
			
			$grd_conditions = $this->ESTT_GROUND_CONDITIONS_CODE[$found[1]];
			$temperature = $this->set_sTTT($found[2]);

			$this->set_result_value('grd_conditions', $grd_conditions);
			$this->set_result_value('t_ground', $temperature);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes snow height and condition infromation.
	 * Only reported at 00, 06, 12, 18 UTC.
	 * section 3 - 4E'sss group
	 
	 * Parameters
	 * ----------
	 * E: condition of the ground with snow/ice
	 * sss: snow height in cm
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_snow_report($part)
	{
		if ($this->result['section_3'] === TRUE) {
			if (!preg_match('@^4([0-9/]{1})([0-9]{3})$@', $part, $found))
			{
				return FALSE;
			}
			
			$conditions_snow = $this->ESSS_GROUND_CONDITIONS_CODE[$found[1]];
			$snow_height = NULL;
			if ($found[2] != "") {
				$snow_height = intval($found[2]);
				if ($snow_height == 997) {
					$snow_height = 0.05;  //less 0.05 cm
				}
				elseif ($snow_height >= 998 && $snow_height <= 999) {
					$snow_height = NULL;
				}
			}

			$this->set_result_value('conditions_snow', $conditions_snow);
			$this->set_result_value('snow_height', $snow_height);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes sunshine duration of the previous day in 1/10 hours (only reported at 06 UTC).
	 * 55SSS: Sunshine duration of the previous day in 1/10 hours (only reported at 06 UTC)
	 * 0FFFF: positive net radiation in J/m^⁻2
	 * 1FFFF: negative net radiation in J/m^⁻2
	 * 2FFFF: sum of global radiation in J/m^⁻2
	 * 3FFFF: sum of diffuse sky radiation in J/m^⁻2
	 * 4FFFF: sum of downward long-wave radiation radiation in J/m^⁻2
	 * 5FFFF: sum of upward long-wave radiation radiation in J/m^⁻2
	 * 6FFFF: sum of short-wave radiation radiation in J/m^⁻2
	 * section 3 - 55SSS group
	 
	 * Parameters
	 * ----------
	 * SSS: hours
	 * FFF: radiation in J/m^-2
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_sunshine_dur($part)
	{
		if ($this->result['section_3'] === TRUE) {
			if (!preg_match('@^55([0-9]{3})$@', $part, $found))
			{
				return FALSE;
			}
			
			$sunshine_dur = intval($found[1]) * 0.1;;

			$this->set_result_value('sunshine_dur', $sunshine_dur);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Decodes amount of melted precipitation.
	 * section 3 - 6RRRt group
	 
	 * Parameters
	 * ----------
	 * RRR: precipitation amount in mm
	 * t: reference time
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_precipitation_section3($part)
	{
		if ($this->result['section_3'] === TRUE) {
			if (!preg_match('@^6([0-9/]{3})([0-9]{1})$@', $part, $found))
			{
				return FALSE;
			}
			
			if ($found[1] == "///") {
				$precip = NULL;
			}
			else {
				$precip = intval($found[1]);
				if ($precip >= 990 && $precip <= 999) {
					$precip = ($precip - 990) * 0.1;
					if ($precip == 0) {
						//only traces of precipitation not measurable < 0.05
						$precip = 0.05;
					}
				}
			}
			$precip_ref_time = $this->T_CODE[$found[2]];

			$this->set_result_value('precip_section3', $precip);
			$this->set_result_value('precip_ref_time_section3', $precip_ref_time);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes report of cloud layers. May be repeated up to 4 times..
	 * section 3 - 8NChh group
	 
	 * Parameters
	 * ----------
	 * N: cloud cover in okta
	 * 	If 9 obscured by fog or other meteorological phenomena.	
	 * 	If / observation not made or not possible due to phenomena other than 9
	 * C: cloud type
	 * hh: height of cloud base in m
	 
	 * Returns
	 * -------
	 * int
	 * 	Cloud height in m
	*/
    private function set_hh($code) {
        // Decode cloud height of synop report
		// cloud height in meters
		
		//cloud height assessed visually 
		$CLOUD_HEIGHT_CLASSES = array(
			90 => 0,  //<50
			91 => 50,
			92 => 100,
			93 => 200,
			94 => 300,
			95 => 600,
			96 => 1000,
			97 => 1500,
			98 => 2000,
			99 => 2500
		);
		
        if ($code == "" || $code == "//") {
            $code = NULL;  //not observed
		}
        else {
			$code = intval($code);
		}
			
		//not used 51-55
        if ($code <= 50) {
            $c_height = $code * 30;
		}
        elseif ($code >= 51 and $code <= 55) {
            $c_height = NULL;
		}
        elseif ($code >= 56 and $code <= 80) {
            $c_height = 1800 + ($code - 56) * 300;
		}
        elseif ($code >= 81 and $code <= 89) {
            $c_height = 10500 + ($code - 81) * 1500;
		}
        else {
            $c_height = $CLOUD_HEIGHT_CLASSES[$code];
		}
		
        return $c_height;
	}

	private function get_cloud_report($part)
	{
		if ($this->result['section_3'] === TRUE) {
			if (!preg_match('@^8([0-9]{1})([0-9/]{1})([0-9/]{2})$@', $part, $found))
			{
				return FALSE;
			}
			
			$cloud_cover_lowest = $this->CLOUD_COVER_CODE[$found[1]];
			$clouds_shape = $this->CLOUD_TYPE_CODE[$found[2]];
			$cld_low_height = $this->set_hh($found[3]);

			$this->set_result_value('amount_cloudiness', $cloud_cover_lowest);
			$this->set_result_value('clouds_shape', $clouds_shape);
			$this->set_result_value('cld_low_height', $cld_low_height);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Decodes adding of special weather conditions.
	 * section 3 - 9SSss group
	 
	 * Parameters
	 * ----------
	 * SS: additional information about the weather in period of observation
	 * ss: additional information about the weather between periods of observation
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_w_conditions($part)
	{
		if ($this->result['section_3'] === TRUE) {
			if (!preg_match('@^9([0-9]{2})([0-9]{2})$@', $part, $found))
			{
				return FALSE;
			}
			
			/*
			if ($found[1] == "///") {
				$precip = NULL;
			}
			else {
				$precip = intval($found[1]);
				if ($precip >= 990 && $precip <= 999) {
					$precip = ($precip - 990) * 0.1;
					if ($precip == 0) {
						//only traces of precipitation not measurable < 0.05
						$precip = 0.05;
					}
				}
			}
			$precip_ref_time = $this->T_CODE[$found[2]];
			*/

			//$this->set_result_value('weather_addon1', $precip);
			//$this->set_result_value('weather_addon2', $precip_ref_time);
			
			if ($found[1] == 10 || $found[1] == 11) {
				$gust = $found[2];
			}
			else {
				$gust = NULL;
			}
			
			$weather_addon1 = $found[1];
			$weather_addon2 = $found[2];
			
			$this->set_result_value('weather_addon1', $weather_addon1);
			$this->set_result_value('weather_addon2', $weather_addon2);
			$this->set_result_value('gust', $gust);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Interpretation section 5.
	 * section 5 - 555 1EsTT (5sTTT) (52sTT) (530ff) 7RRR/ 88RRR
	 
	 * Parameters
	 * ----------
	 * 
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_section_5($part)
	{
		if (!preg_match('@^555$@', $part, $found))
		{
			return FALSE;
		}
		
		$this->set_result_value('section_5', TRUE); // section 5 exists
		
		$this->method++;

		return TRUE;
	}
	
	/**
	 * Decodes ground temperature information of the night.
	 * 12 respectively 15 hour minimum temperature 5cm above ground/snow cover.
	 * section 5 - 1EsTT or 3EsTT group
	 
	 * Parameters
	 * ----------
	 * E: condition of the ground without the snow respectively
	 * s: sign of the data, and relative humidity indicator
	 * TT: temperature only reported at 06, 09, and 18 UTC. /// at 00 and 12 UTC
	 
	 * Returns
	 * -------
	 * float type
	 * 	Temperature in degree Celsius
	*/
	private function get_t_ground_section5($part)
	{
		if ($this->result['section_5'] === TRUE) {
			if (!preg_match('@^[13]{1}([0-9/]{1})([0-9]{3})$@', $part, $found))
			{
				return FALSE;
			}
			
			$grd_conditions = $this->ESTT_GROUND_CONDITIONS_CODE[$found[1]];
			$temperature = $this->set_sTTT($found[2]);

			$this->set_result_value('grd_conditions_section5', $grd_conditions);
			$this->set_result_value('t_ground_section5', $temperature);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes temperature information.
	 * section 5 - 5sTTT group
	 
	 * Parameters
	 * ----------
	 * s: sign of the data, and relative humidity indicator
	 * TTT: 
	 
	 * Returns
	 * -------
	 * float type
	 * 	Temperature in degree Celsius
	*/
	private function get_t_avg_last($part)
	{
		if (($this->result['section_5'] === TRUE) && preg_match('@^5([019]{1})([0-9]{3})$@', $part)) {
			if (!preg_match('@^5([0-9]{4})$@', $part, $found))
			{
				return FALSE;
			}
			
			$temperature = $this->set_sTTT($found[1]);

			$this->set_result_value('t_avg_last', $temperature);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes ground temperature information of the night.
	 * 12 respectively 15 hour minimum temperature 5cm above ground/snow cover.
	 * section 5 - 52sTT group
	 
	 * Parameters
	 * ----------
	 * E: condition of the ground without the snow respectively
	 * s: sign of the data, and relative humidity indicator
	 * TT: temperature only reported at 06, 09, and 18 UTC. /// at 00 and 12 UTC
	 
	 * Returns
	 * -------
	 * float type
	 * 	Temperature in degree Celsius
	*/
	private function get_t_min_2cm_night($part)
	{
		if ($this->result['section_5'] === TRUE) {
			if (!preg_match('@^52([0-9]{3})$@', $part, $found))
			{
				return FALSE;
			}
			
			$temperature = $this->set_sTTT($found[1]);

			$this->set_result_value('t_min_2cm_night', $temperature);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes wind gust max speed information.
	 * section 5 - 530ff group
	 
	 * Parameters
	 * ----------
	 * ff: wind gust max speed (10 minute mean)
	*/
	private function get_wind_gust_max_last($part)
	{
		if ($this->result['section_5'] === TRUE) {
			if (!preg_match('@^530([0-9/]{2})$@', $part, $found))
			{
				return FALSE;
			}

			//wind speed is greater than 99 units and this group is directly followed by the 00fff group
			$wind_speed_code = $found[1];
			if ($wind_speed_code == "" || $wind_speed_code == "//") {
				$wind_speed = NULL;  //not observed
			}
			else {
				$wind_speed = intval($wind_speed_code);  // in mps
			}

			$this->set_result_value('wind_gust_max_last', $wind_speed);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes amount of melted precipitation of the last day.
	 * section 5 - 7RRR/ group
	 
	 * Parameters
	 * ----------
	 * RRR: precipitation amount in mm
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_precip_last($part)
	{
		if ($this->result['section_5'] === TRUE) {
			if (!preg_match('@^7([0-9/]{3})\/$@', $part, $found))
			{
				return FALSE;
			}
			
			if ($found[1] == "///") {
				$precip = NULL;
			}
			else {
				$precip = intval($found[1]);
			}

			$this->set_result_value('precip_last', $precip);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Decodes amount of melted precipitation more 30 mm of the last day.
	 * section 5 - 88RRR group
	 
	 * Parameters
	 * ----------
	 * RRR: precipitation amount in mm
	 
	 * Returns
	 * -------
	 * 
	 * 	
	*/
	private function get_precip_last_30mm($part)
	{
		if ($this->result['section_5'] === TRUE) {
			if (!preg_match('@^88([0-9/]{3})$@', $part, $found))
			{
				return FALSE;
			}
			
			if ($found[1] == "///") {
				$precip = NULL;
			}
			else {
				$precip = intval($found[1]);
			}

			$this->set_result_value('precip_last_30mm', $precip);

			$this->method++;

			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>
