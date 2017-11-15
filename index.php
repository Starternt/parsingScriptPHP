<?php
// common settings
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT', dirname(__FILE__));
include_once ROOT.'/simplehtmldom/simple_html_dom.php';

class Db
{
    public static function getConnection()
    {
        $paramsPath = ROOT . '/config/db_params.php';
        $params = array(
            'host' => 'localhost',
            'dbname' => 'tasks',
            'user' => 'root',
            'password' => '',
        );

        $dsn = "mysql:host={$params['host']};dbname={$params['dbname']}";
        $db = new PDO($dsn, $params['user'], $params['password']);
        $db->exec("set names utf8"); //cp1251

        return $db;
    }

}

class Bills
{
    public function getData()
    {
        $html = file_get_html('http://www.bills.ru');
        $test = '';
        foreach ($html->find('#bizon_api_news_list') as $value) {
            $test .= iconv("windows-1251", "UTF-8", $value);
        }

        $test = str_get_html($test);
        $firstTable = $test->childNodes(0);
        $firstTable = str_get_html($firstTable);

        for ($i = 0; $i < 5; $i++) {
            $dates = $firstTable->find('.news_date');
            $j[$i] = $dates[$i];

            $trueDates = explode(" ", preg_replace('/\s+/', ' ', $j[$i]));
            $trueDates[3] = self::chooseMonth($trueDates[3]);
            $_dates[$i] = "2017-" . $trueDates[3] . "-" . $trueDates[2];
            echo $_dates[$i];
            echo "<br>";
        }

        for ($i = 0; $i < 5; $i++) {
            $title = $firstTable->find('a');
            $titles[$i] = $title[$i]->plaintext;
            echo $titles[$i];
            echo "<br>";
        }

        for ($i = 0; $i < 5; $i++) {
            $url = $firstTable->find('a');
            $urls[$i] = $url[$i]->href;
            echo $urls[$i];
            echo "<br>";
        }
        for ($i = 0; $i < 5; $i++) {
            self::insertToDb($_dates[$i], $titles[$i], $urls[$i]);
        }
    }

    private function insertToDb($dates, $title, $url)
    {
        $db = Db::getConnection();
        $sql = "INSERT INTO `bills_ru_events`(`date`, `title`, `url`) VALUES(:dates, :title, :url)";
        $result = $db->prepare($sql);
        $result->bindParam(':dates', $dates);
        $result->bindParam(':title', $title);
        $result->bindParam(':url', $url);
        $result->execute();
    }

    private function chooseMonth($elem)
    {
        switch ($elem) {
            case "янв":
                return '1';
                break;
            case "фев":
                return '2';
                break;
            case "мар":
                return '3';
                break;
            case "апр":
                return '4';
                break;
            case "май":
                return '5';
                break;
            case "июн":
                return '6';
                break;
            case "июл":
                return '7';
                break;
            case "авг":
                return '8';
                break;
            case "сен":
                return '9';
                break;
            case "окт":
                return '10';
                break;
            case "ноя":
                return '11';
                break;
            case "дек":
                return '12';
                break;
        }
    }
}

$b = new Bills();
$b->getData();