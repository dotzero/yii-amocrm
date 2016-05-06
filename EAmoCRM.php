<?php
/**
 * EAmoCRM class file.
 *
 * @package EAmoCRM
 * @version 1.0
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://github.com/dotzero/yii-amocrm
 * @link https://developers.amocrm.ru/rest_api/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * EAmoCRM это расширение для Yii PHP framework которое выступает в качестве простого прокси для обращения
 * к API сайта amoCRM. Структуры и данных для передачи нелогичны, за дополнительными разъяснениями
 * можно обратится к официальный документации amoCRM (https://www.amocrm.ru/add-ons/api.php)
 *
 * Требования:
 * Yii Framework 1.1.0 или новее
 *
 * Установка:
 * - Скопировать папку EAmoCRM в 'protected/extensions'
 * - Добавить в секцию 'components' конфигурационного файла:
 *
 *  'amocrm' => array(
 *      'class' => 'application.extensions.EAmoCRM.EAmoCRM',
 *      'subdomain' => 'example', // Персональный поддомен на сайте amoCRM
 *      'login' => 'login@mail.com', // Логин на сайте amoCRM
 *      'password' => '123456', // Пароль на сайте amoCRM
 *      'hash' => '00000000000000000000000000000000', // Вместо пароля можно использовать API ключ
 *  ),
 *
 * Пример использования:
 *
 * // Проверка авторизации на сайте amoCRM
 * $result = Yii::app()->amocrm->ping();
 *
 * // Получение 1 страницы со списком контактов, >на странице 20 записей
 * $result = Yii::app()->amocrm->listContacts(1, 20);
 */
class EAmoCRM extends CApplicationComponent
{
    /**
     * @var null|string Персональный поддомен на сайте amoCRM
     */
    public $subdomain = null;

    /**
     * @var null|string Логин на сайте amoCRM
     */
    public $login = null;

    /**
     * @var null|string API ключ для доступа
     */
    public $hash = null;

    /**
     * @var null|\AmoCRM\Client Экземпляр клиента для работы с amoCRM
     */
    private $client = null;

    /**
     * Initializes the application component.
     */
    public function init()
    {
        parent::init();

        if (!class_exists('\\AmoCRM\\Client')) {
            throw new CException('EAmoCRM cannot work without \AmoCRM\Client class. Try to include composer autoloader (vendor/autoload.php).');
        }
    }

    /**
     * Инициализация экземпляра клиента для работы с amoCRM
     *
     * @return \AmoCRM\Client
     */
    public function getClient()
    {
        if ($this->client === null) {
            $this->client = new \AmoCRM\Client($this->subdomain, $this->login, $this->hash);
        }

        return $this->client;
    }
}
