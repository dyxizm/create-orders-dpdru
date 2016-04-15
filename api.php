<?php

include_once 'dpd_service.class.php';

class testDpd{
	
	public $api;

	function __construct(){
		$this->api = new DPD_service('MY_NUMBER', 'MY_KEY');
	}
	
	public function createOrder($id, $date){
		$order = array();
		// Дата приёма груза
		$order['header']['datePickup'] = $date;

		// Адрес приёма груза
		// Название отправителя/получателя.
		$order['header']['senderAddress']['name'] = 'Иванов Сергей Петрович';
		// Код терминала.
		// Данное поле является обязательным для вариантов перевозки «ДТ», «ТД» или «ТТ»
		$order['header']['senderAddress']['terminalCode'] = 'M91';
		// Название страны
		$order['header']['senderAddress']['countryName'] = 'Россия';
		// Индекс; необязательный
		$order['header']['senderAddress']['index'] = '140012';
		// Регион; необязательный 
		$order['header']['senderAddress']['region'] = 'Московская обл.';
		// Город
		$order['header']['senderAddress']['city'] = 'Люберцы';
		// Улица (формат ФИАС)
		$order['header']['senderAddress']['street'] = 'Авиаторов';
		// Сокращения типа улицы (ул, пр-т, б-р и т.д.)
		$order['header']['senderAddress']['streetAbbr'] = 'ул';
		// Дом
		$order['header']['senderAddress']['house'] = '1';
		// Квартира; необязательный
		$order['header']['senderAddress']['flat'] = '144';
		// Контактное лицо
		$order['header']['senderAddress']['contactFio'] = 'Смирнов Игорь Николаевич';
		// Контактный телефон
		$order['header']['senderAddress']['contactPhone'] = '89165555555';
		// Контактный e-mail; необязательный
		$order['header']['senderAddress']['contactEmail'] = 'smirnov@megashop.ru';

		// Номер заказа в информационной системе клиента
		$order['order']['orderNumberInternal'] = $id;
		// Код услуги DPD. Уточните код нужной Вам услуги у своего менеджера или используйте код услуги, полученный из веб-сервиса «Калькулятор стоимости»
		$order['order']['serviceCode'] = 'CUR';
		// Вариант доставки. Доступно 4 варианта: ДД, ДТ, ТД и ТТ. Расшифровку вариантов см. в разделе «Варианты доставки».
		$order['order']['serviceVariant'] = 'ДД';
		// Количество грузомест (посылок) в отправке 
		$order['order']['cargoNumPack'] = 1;
		// Вес отправки, кг
		$order['order']['cargoWeight'] = 5;
		// Объём, м3; необязательный
		$order['order']['cargoVolume'] = 0.05;
		// Ценный груз.
		$order['order']['cargoRegistered'] = false;
		// Сумма объявленной ценности, руб.; необязательный
		$order['order']['cargoValue'] = 1000;
		// Содержимое отправки
		$order['order']['cargoCategory'] = 'Одежда';

		// Адрес доставки
		$order['order']['receiverAddress']['name'] = 'Иванов Сергей Петрович';
		// Код терминала.
		// Данное поле является обязательным для вариантов перевозки «ДТ», «ТД» или «ТТ»
		$order['order']['receiverAddress']['terminalCode'] = 'M91';
		// Название страны
		$order['order']['receiverAddress']['countryName'] = 'Россия';
		// Индекс; необязательный
		$order['order']['receiverAddress']['index'] = '140012';
		// Регион; необязательный 
		$order['order']['receiverAddress']['region'] = 'Московская обл.';
		// Город
		$order['order']['receiverAddress']['city'] = 'Люберцы';
		// Улица (формат ФИАС)
		$order['order']['receiverAddress']['street'] = 'Авиаторов';
		// Сокращения типа улицы (ул, пр-т, б-р и т.д.)
		$order['order']['receiverAddress']['streetAbbr'] = 'ул';
		// Дом
		$order['order']['receiverAddress']['house'] = '1';
		// Квартира; необязательный
		$order['order']['receiverAddress']['flat'] = '144';
		// Контактное лицо
		$order['order']['receiverAddress']['contactFio'] = 'Смирнов Игорь Николаевич';
		// Контактный телефон
		$order['order']['receiverAddress']['contactPhone'] = '89165555555';
		// Контактный e-mail; необязательный
		$order['order']['receiverAddress']['contactEmail'] = 'smirnov@megashop.ru';

		var_dump($this->api->createOrder($order));
	}


	public function getOrderStatus($id, $date=false){
		$order = array();
		// Номер заказа в информационной системе клиента
		$order['order']['orderNumberInternal'] = $id;
		if($date){
			// Дата приёма груза (на тот случай, если номер в вашей информационной системе не является уникальным)
			$order['order']['datePickup'] = $date;
		}
		var_dump($this->api->getOrderStatus($order));
	}
}


$dpd = new testDpd();

$dpd->createOrder('153453', date("Y-m-d"));
sleep(2);
$dpd->getOrderStatus('153453');























