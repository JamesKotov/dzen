<?php
/**
 * @author Strigo E. V.
 * @copyright 2010
 */

$balance_name_string = 'Баланс';

$trasaction_type = array('Транзакций нет', 'Зарплата', 'Премия', 'Аванс',
	'Получение кредита', 'Взял взаймы', 'Проезд', 'Продукты', 'Квартплата',
	'Отдал занятое', 'Выплата кредита', 'Кафе и рестораны', 'Подарки');

$days_name = array('Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб');

$month_name = array('', 'янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг',
	'сен', 'окт', 'ноя', 'дек');

$begin_timestamp = mktime(0, 0, 0, 11, 1, 2009); // 1 нобяря 2009 г.

$begin_balance = -100;

define('STAMP_24H', 86400);

$output = array();

if (isset($_REQUEST['balance']) && $_REQUEST['balance'] > 0)
{
	$balance_value = $_REQUEST['balance'];
} else
{
	$balance_value = $begin_balance;
}

if (isset($_REQUEST['last']) && $_REQUEST['last'] >= $begin_timestamp)
{
	$begin = $_REQUEST['last'] + STAMP_24H;
} else
{
	$begin = $begin_timestamp;
}

if (isset($_REQUEST['count']) && $_REQUEST['count'] > 0)
{
	$count = $_REQUEST['count'];
} else
{
	$count = 10;
}

$generation_begin_from = ($begin - $begin_timestamp) / STAMP_24H;

/* генерация массивов дней. */
for ($i = $generation_begin_from; $i <= $generation_begin_from + $count; $i++)
{

	$on_date = $begin_timestamp + STAMP_24H * $i;
	if ($on_date > time())
		break;

	$day = array();

	$day['id'] = $on_date;

	$day_number = date('w', $on_date);
	$day['day'] = $days_name[$day_number];
	if ($day_number == 0 || $day_number == 6)
		$day['holiday'] = true;
	else
		$day['holiday'] = false;

	$day['date'] = date('j', $on_date) . ' ' . $month_name[date('n', $on_date)];

	$balance = array();
	$balance['name'] = $balance_name_string;
	$balance['value'] = $balance_value;
	$day['balance'] = $balance;

	$trans_on_day = mt_rand(0, 4);
	$used_trans_types = array();
	$day_balance = 0;
	$transactions = array();

	for ($j = 1; $j <= $trans_on_day; $j++)
	{
		$transaction = array();
		do
		{
			$trans_type = mt_rand(1, count($trasaction_type) - 1);
		} while (in_array($trans_type, $used_trans_types));
		$used_trans_types[] = $trans_type;

		$trans_value = round(mt_rand(50, (100000 / ($trans_type + 1))) / (($trans_type +
					1) / 3));

		if ($trans_type > 5)
			$trans_value = (0 - $trans_value);

		$transaction['name'] = $trasaction_type[$trans_type];
		$transaction['value'] = $trans_value;

		$day_balance += $trans_value;

		$transactions[] = $transaction;
	}

	if (count($transactions) == 0)
	{
		$transaction = array();
		$transaction['name'] = $trasaction_type[0];
		$transaction['value'] = '';
		$transactions[] = $transaction;
	}

	$day['transactions'] = $transactions;
	$day['day_balance'] = $day_balance;

	$balance_value += $day_balance;

	$output[] = $day;
}
/* конец генерации массивов дней. */

$json_string = json_encode($output);

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache');
echo $json_string;
?>
