<? 
// хостнейм, который будем тестить
$hostname = 'www.reg.com';

// 3 секунды - достаточно позорный результат, нет смысла ждать больше
$timeout = 3;

// собираем простейший реквест
$request = <<<REQUEST
GET / HTTP/1.1\r
Host: $hostname\r
Connection: close\r
\r
\r
REQUEST;

// получаем список А-записей
$records = dns_get_record($hostname, DNS_A);

// тестим
$requests = array();
foreach ($records as $record) {
	$ip = $record['ip'];
	echo 'Testing ' . $ip . " \t";	
	$ts = microtime(1);
	$sock = fsockopen($record['ip'], 80, $errno, $errstr, $timeout);
	if ($sock === false) {
		$response = $timeout;
	} else {
		fwrite($sock, $request);
		while (!feof($sock)) {
			fgets($sock, 128);
		}
		$dt = microtime(1) - $ts;
		$response = round($dt, 5);
		fclose($sock);
	}
	echo ($response ?: 'FAIL!') . PHP_EOL;
	$requests[$ip] = $response;
}

echo PHP_EOL;
asort($requests);
echo 'And the winner is... ' . array_shift(array_keys($requests)) . '!' . PHP_EOL;
?>
