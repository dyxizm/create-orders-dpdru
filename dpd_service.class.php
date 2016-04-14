<?php
class DPD_service {
	public $arMSG = array(); // массив-сообщение ('str' => текст_сообщения, 'type' => тип_сообщения (по дефолту: 0 - ошибка)
	private $IS_ACTIVE = 1; // флаг активности сервиса (0 - отключен, 1 - включен) 
	private $IS_TEST = 1; // флаг тестирования (0 - работа, 1 - тест)
	private $SOAP_CLIENT; // SOAP-клиент
	private $MY_NUMBER; // ЗАМЕНИТЬ НА СВОЙ!!! - клиентский номер в системе DPD (номер договора с DPD)
	private $MY_KEY; // ЗАМЕНИТЬ НА СВОЙ!!! - уникальный ключ для авторизации

	private $arDPD_HOST = array(
		0 => 'ws.dpd.ru/services/', //рабочий хост
		1 => 'wstest.dpd.ru/services/' //тестовый хост
	);
	private $arSERVICE = array( //сервисы: название => адрес
		'getCitiesCashPay' => 'geography', //География DPD (города доставки)
		'getTerminalsSelfDelivery' => 'geography', //список терминалов DPD (TODO)
		'getServiceCost' => 'calculator2', //Расчет стоимости
		'createOrder' => 'order2', //Создать заказ на доставку (TODO)
		'getOrderStatus' => 'order2' //Получить статус создания заказа (TODO)
	);

	/**
	 * Конструктор
	 *
	 * @access public
	 * @return void
	 */
	public function __construct($MY_NUMBER, $MY_KEY) {
		$this->MY_NUMBER = $MY_NUMBER;
		$this->MY_KEY = $MY_KEY;
		$this->IS_TEST = $this->IS_TEST ? 1 : 0;
	}
	/**
	 * Список городов доставки *
	 *
	 * @access public
	 * @return
	 */
	public function getCityList() {
		$obj = $this->_getDpdData( 'getCitiesCashPay' );
		// конверт $obj --> $arr
		$res = $this->_parceObj2Arr( $obj->return );
		return $res; 
	}
	/**
	 * Определение стоимости доставки *
	 *
	 * @access public
	 * @param array   $arData // массив входных параметров*
	 * @return
	 */
	public function getServiceCost( $arData ) {
		// куда
		if ( $arData['delivery']['cityName'] ) {
			$arData['delivery']['cityName'] = iconv( 'windows-1251', 'utf- 8', $arData['delivery']['cityName'] );
		}
		// откуда
		$arData['pickup'] = array(
			'cityId' => 48994107,
			'cityName' => iconv( 'windows-1251', 'utf-8', 'Екатеринбург' ),
			//'regionCode' => '66', //'countryCode' => 'RU',
		);
		// что делать с терминалом
		$arData['selfPickup'] = true;// Доставка ОТ терминала
		$arData['selfDelivery'] = true;// Доставка ДО терминала

		// третий параметр - флаг упаковки запроса в общее поле "request"
		$obj = $this->_getDpdData( 'getServiceCost', $arData, 'request' );
		// конверт $obj --> $arr
		$res = $this->_parceObj2Arr( $obj->return );
		return $res;
	}

	public function createOrder( $arData ) {		
		// третий параметр - флаг упаковки запроса в общее поле "request"
		$obj = $this->_getDpdData( 'createOrder', $arData, 'orders' );
		// конверт $obj --> $arr
		$res = $this->_parceObj2Arr( $obj->return );
		return $res;
	}

	public function getOrderStatus( $arData ) {
		// третий параметр - флаг упаковки запроса в общее поле "request"
		$obj = $this->_getDpdData( 'getOrderStatus', $arData, 'orderStatus' );
		// конверт $obj --> $arr
		$res = $this->_parceObj2Arr( $obj->return );
		return $res;	}


	// PRIVATE ------------------------
	/**
	 * Коннект с соответствующим сервисом *
	 *
	 * @access private
	 * @param string  $method_name
	 * свойства класса $this->arSERVICE) * @return bool
	 * Запрашиваемый метод сервиса (см. ключ
	 * Результат инициализации (если положительный - появится свойство $this->SOAP_CLIENT, иначе $this->arMSG)
	 */
	private function _connect2Dpd( $method_name ) {
		if ( !$this->IS_ACTIVE ) return false;
		if ( !$service = $this->arSERVICE[$method_name] ) {
			$this->arMSG['str'] = 'В свойствах класса нет сервиса "'.$method_name.'"';
			return false; }
		$host = $this->arDPD_HOST[$this->IS_TEST].$service.'?WSDL';
		try {
			// Soap-подключение к сервису
			$this->SOAP_CLIENT = new SoapClient( 'http://'.$host );
			if ( !$this->SOAP_CLIENT ) throw new Exception( 'XEPPP' );
		} catch ( Exception $ex ) {
			$this->arMSG['str'] = 'Не удалось подключиться к сервисам DPD '.$service; return false;
		}
		return true; }
	/**
	 * Запрос данных в методе сервиса *
	 *
	 * @access private
	 * @param string  $method_name Название метода Dpd-сервиса (см.$arSERVICE)
	 * @param array   $arData      Массив параметров, передаваемых в метод
	 * @param integer $is_request  флаг упаковки запроса в поле 'request'
	 * @return XZ_obj Объект, полученный от сервиса
	 */

	private function _getDpdData( $method_name, $arData=array(), $request=false ) {
		if ( !$this->_connect2Dpd( $method_name ) ) return false;
		// параметр запроса для аутентификации
		$arData['auth'] = array(
			'clientNumber' => $this->MY_NUMBER,
			'clientKey' => $this->MY_KEY);
		// упаковка запроса в поле 'request'
		if ( $request ) $arRequest[$request] = $arData; else $arRequest = $arData;
		try {
			//eval("\$obj = \$this->SOAP_CLIENT->\$method_name(\$arRequest);");
			$obj = $this->SOAP_CLIENT->$method_name( $arRequest );
			if ( !$obj ) throw new Exception( 'XEPPP' ); } catch ( Exception $ex ) {
			$this->arMSG['str'] = 'Не удалось вызвать метод '.$method_name.' / '.$ex;
		}
		return !empty($obj) ? $obj : false;
	}

	/**
	 * Парсер объекта в массив (рекурсия) *
	 *
	 * @access private
	 * @param object  $obj   Объект
	 * @param integer $isUTF Флаг необходимости конвертирования строк из UTF в WIN (0|1), по-дефолту "1" - конвертить
	 * @param array   $arr   Внутренний cлужебный массив для обеспечения рекурсии
	 * @return array
	 */
	private function _parceObj2Arr( $obj, $isUTF=0, $arr=array() ) {
		$isUTF = $isUTF ? 1 : 0;
		if ( is_object( $obj ) || is_array( $obj ) ) {
			$arr = array();
			for ( reset( $obj ); list( $k, $v ) = each( $obj ); ) {
				if ( $k === "GLOBALS" ) continue;
				$arr[$k] = $this->_parceObj2Arr( $v, $isUTF, $arr );
			}
			return $arr; }
		elseif ( gettype( $obj ) == 'boolean' ) {
			return $obj ? 'true' : 'false';
		} else {
			// конверт строк: utf-8 --> windows-1251
			if ( $isUTF && gettype( $obj )=='string' )
				$obj = iconv( 'utf-8', 'windows-1251', $obj );
			return $obj;}
	}
}
