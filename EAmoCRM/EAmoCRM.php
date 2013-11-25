<?php
/**
 * EAmoCRM class file.
 *
 * @package YiiLessPhp
 * @author dZ <mail@dotzero.ru>
 * @link http://www.dotzero.ru
 * @link https://github.com/dotzero/YiiAmoCRM
 * @link https://www.amocrm.ru/add-ons/api.php
 * @license MIT
 * @version 1.0 (25-nov-2013)
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
     * @var null|string Пароль на сайте amoCRM
     */
    public $password = null;
    /**
     * @var null|string API ключ для доступа
     */
    public $hash = null;
    /**
     * @var mixed Сообщение о последней ошибке
     */
    private $lastError = null;
    /**
     * @var mixed Код последней ошибки
     */
    private $lastErrorNo = null;
    /**
     * Типы задач
     */
    const TASK_CALL = 'CALL';
    const TASK_LETTER = 'LETTER';
    const TASK_MEETING = 'MEETING';

    /**
     * Initializes the application component.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Проверка авторизации на сайте amoCRM
     *
     * @return bool
     * @throws CException
     */
    public function ping()
    {
        $result = $this->call('/private/api/auth.php');

        return (isset($result['auth']) AND $result['auth'] === 'true');
    }

    /**
     * Получение страницы со списком контактов из amoCRM
     *
     * @param null|integer $page Номер страницы
     * @param null|integer $onpage Количество выданных элементов
     * @return mixed
     * @throws CException
     */
    public function listContacts($page = null, $onpage = null)
    {
        $params = array();

        if ($page !== null) {
            $params['PAGEN_1'] = $page;
        }

        if ($onpage !== null) {
            $params['ELEMENT_COUNT'] = $onpage;
        }

        return $this->call('/private/api/contacts.php', $params);
    }

    /**
     * Поиск контактов в amoCRM
     *
     * @param string $keyword Искомое слово
     * @param null|integer $page Номер страницы
     * @param null|integer $onpage Количество выданных элементов
     * @return mixed
     * @throws CException
     */
    public function searchContacts($keyword, $page = null, $onpage = null)
    {
        $params = array(
            'SEARCH' => $keyword
        );

        if ($page !== null) {
            $params['PAGEN_1'] = $page;
        }

        if ($onpage !== null) {
            $params['ELEMENT_COUNT'] = $onpage;
        }

        return $this->call('/private/api/contact_search.php', $params);
    }

    /**
     * Получение детальной страницы контакта в amoCRM
     *
     * @param integer $id ID контакта
     * @return mixed
     * @throws CException
     */
    public function getContact($id)
    {
        $params = array(
            'ID' => $id
        );

        return $this->call('/private/api/contact_detail.php', $params);
    }

    /**
     * Добавление контакта в amoCRM
     *
     * @param array $data Структура данных
     * @example Пример структуры данных
     *  array(
     *      'person_name' => 'Фамилия Имя',
     *      'person_position' => 'Должность',
     *      'person_company_name' => 'Компания',
     *      'person_company_id' => '0',
     *      'contact_data' => array(
     *          'phone_numbers' => array(
     *              array('number' => '+7 495 123-45-67'),
     *              array('location' => 'Work'),
     *              array('number' => '+7 499 891-01-11'),
     *              array('location' => 'Mobile')
     *          ),
     *          'email_addresses' => array(
     *              array('address' => 'mail@mail.ru'),
     *              array('location' => 'Work')
     *          ),
     *          'web_addresses' => array(
     *              array('url' => 'http://example.com')
     *          ),
     *          'addresses' => array(
     *              array('street' => 'Moscow, Russia')
     *          ),
     *          'instant_messengers' => array(
     *              array('address' => 'imaddr'),
     *              array('protocol' => 'Skype')
     *          )
     *      ),
     *      'main_user_id' => '1',
     *      'tags' => 'тег, тег2, тег3'
     *  )
     * @return mixed
     * @throws CException
     */
    public function addContact($data)
    {
        $params = array(
            'ACTION' => 'ADD_PERSON',
            'contact' => serialize($data)
        );

        return $this->call('/private/api/contact_add.php', $params);
    }

    /**
     * Редактирование контакта в amoCRM
     *
     * @param integer $id ID контакта
     * @param array $data Структура данных
     * @example Пример структуры данных
     *  array(
     *      'person_name' => 'Фамилия Имя',
     *      'person_position' => 'Должность',
     *      'person_company_name' => 'Компания',
     *      'person_company_id' => '0',
     *      'contact_data' => array(
     *          'phone_numbers' => array(
     *              array('number' => '+7 495 123-45-67'),
     *              array('location' => 'Work'),
     *              array('number' => '+7 499 891-01-11'),
     *              array('location' => 'Mobile')
     *          ),
     *          'email_addresses' => array(
     *              array('address' => 'mail@mail.ru'),
     *              array('location' => 'Work')
     *          ),
     *          'web_addresses' => array(
     *              array('url' => 'http://example.com')
     *          ),
     *          'addresses' => array(
     *              array('street' => 'Moscow, Russia')
     *          ),
     *          'instant_messengers' => array(
     *              array('address' => 'imaddr'),
     *              array('protocol' => 'Skype')
     *          )
     *      ),
     *      'main_user_id' => '1',
     *      'tags' => 'тег, тег2, тег3'
     *  )
     * @return mixed
     * @throws CException
     */
    public function editContact($id, $data)
    {
        $params = array(
            'ID' => $id,
            'ACTION' => 'EDIT',
            'contact' => serialize($data)
        );

        return $this->call('/private/api/contact_add.php', $params);
    }

    /**
     * Удаление контакта из amoCRM
     *
     * @param integer $id ID контакта
     * @return mixed
     * @throws CException
     */
    public function deleteContact($id)
    {
        $params = array(
            'ID' => $id,
            'ACTION' => 'DELETE'
        );

        return $this->call('/private/api/contact_delete.php', $params);
    }

    /**
     * Получение страницы со списком сделок из amoCRM
     *
     * @param null|integer $page Номер страницы
     * @param null|integer $onpage Количество выданных элементов
     * @return mixed
     * @throws CException
     */
    public function listDeals($page = null, $onpage = null)
    {
        $params = array();

        if ($page !== null) {
            $params['PAGEN_1'] = $page;
        }

        if ($onpage !== null) {
            $params['ELEMENT_COUNT'] = $onpage;
        }

        return $this->call('/private/api/deals.php', $params);
    }

    /**
     * Поиск сделок в amoCRM
     *
     * @param string $keyword Искомое слово
     * @param null|integer $page Номер страницы
     * @param null|integer $onpage Количество выданных элементов
     * @return mixed
     * @throws CException
     */
    public function searchDeals($keyword, $page = null, $onpage = null)
    {
        $params = array(
            'SEARCH' => $keyword
        );

        if ($page !== null) {
            $params['PAGEN_1'] = $page;
        }

        if ($onpage !== null) {
            $params['ELEMENT_COUNT'] = $onpage;
        }

        return $this->call('/private/api/deal_search.php', $params);
    }

    /**
     * Добавление сделки в amoCRM
     *
     * @param array $data Структура данных
     * @example Пример структуры данных
     *  array(
     *      'name' => 'Название сделки',
     *      'status_id' => 'ID статуса сделки',
     *      'price' => 'Цена (число)',
     *      'main_user_id' => 'ID ответственного пользователя',
     *      'tags' => 'тег, тег2, тег3',
     *      'linked_contact' => 'ID связанного контакта'
     * )
     * @return mixed
     * @throws CException
     */
    public function addDeal($data)
    {
        $params = array(
            'ACTION' => 'ADD',
            'deal' => serialize($data)
        );

        return $this->call('/private/api/deal_add.php', $params);
    }

    /**
     * Редактирование сделки в amoCRM
     *
     * @param integer $id ID сделки
     * @param array $data Структура данных
     * @example Пример структуры данных
     *  array(
     *      'name' => 'Название сделки',
     *      'status_id' => 'ID статуса сделки',
     *      'price' => 'Цена (число)',
     *      'main_user_id' => 'ID ответственного пользователя',
     *      'tags' => 'тег, тег2, тег3',
     *      'linked_contact' => 'ID связанного контакта'
     * )
     * @return mixed
     * @throws CException
     */
    public function editDeal($id, $data)
    {
        $params = array(
            'ID' => $id,
            'ACTION' => 'EDIT',
            'deal' => serialize($data)
        );

        return $this->call('/private/api/deal_add.php', $params);
    }

    /**
     * Удаление сделки из amoCRM
     *
     * @param integer $id ID контакта
     * @return mixed
     * @throws CException
     */
    public function deleteDeal($id)
    {
        $params = array(
            'ID' => $id,
            'ACTION' => 'DELETE'
        );

        return $this->call('/private/api/deal_delete.php', $params);
    }

    /**
     * Добавление примечания к контакту в amoCRM
     *
     * @param integer $id ID контакта
     * @param $message Текст примечания
     * @return mixed
     * @throws CException
     */
    public function addContactNote($id, $message)
    {
        $params = array(
            'ID' => $id,
            'ACTION' => 'ADD_NOTE',
            'BODY' => $message,
            'ELEMENT_TYPE' => 1
        );

        return $this->call('/private/api/note_add.php', $params);
    }

    /**
     * Добавление примечания к сделке в amoCRM
     *
     * @param integer $id ID контакта
     * @param $message Текст примечания
     * @return mixed
     * @throws CException
     */
    public function addDealNote($id, $message)
    {
        $params = array(
            'ID' => $id,
            'ACTION' => 'ADD_NOTE',
            'BODY' => $message,
            'ELEMENT_TYPE' => 2
        );

        return $this->call('/private/api/note_add.php', $params);
    }

    /**
     * Редактирование примечания в amoCRM
     *
     * @param integer $id ID примечания
     * @param $message Текст примечания
     * @return mixed
     * @throws CException
     */
    public function editNote($id, $message)
    {
        $params = array(
            'ID' => $id,
            'ACTION' => 'EDIT_NOTE',
            'BODY' => $message
        );

        return $this->call('/private/api/note_add.php', $params);
    }

    /**
     * Удаление примечания в amoCRM
     *
     * @param integer $id ID примечания
     * @return mixed
     * @throws CException
     */
    public function deleteNote($id)
    {
        $params = array(
            'ID' => $id,
            'ACTION' => 'NOTE_DELETE',
        );

        return $this->call('/private/api/note_add.php', $params);
    }

    /**
     * Добавление простой задачи в amoCRM
     *
     * @param integer $id ID контакта исполнителя
     * @param string $message Текст задачи
     * @param string $date Дата выполнения задачи
     * @param strung $type Тип задачи (CALL / LETTER / MEETING)
     * @return mixed
     * @throws CException
     */
    public function addTask($id, $message, $date, $type)
    {
        $params = array(
            'ACTION' => 'ADD_TASK',
            'BODY' => $message,
            'END_DATE' => date('d.m.Y H:i:s', strtotime($date)),
            'MAIN_USER' => $id,
            'TASK_TYPE' => $type
        );

        return $this->call('/private/api/task_add.php', $params);
    }

    /**
     * Добавление задачи связанной с контактом в amoCRM
     *
     * @param integer $id ID контакта исполнителя
     * @param integer $contact ID контакта связанного
     * @param string $message Текст задачи
     * @param string $date Дата выполнения задачи
     * @param strung $type Тип задачи (CALL / LETTER / MEETING)
     * @return mixed
     * @throws CException
     */
    public function addContactTask($id, $contact, $message, $date, $type)
    {
        $params = array(
            'ACTION' => 'ADD_TASK',
            'BODY' => $message,
            'END_DATE' => date('d.m.Y H:i:s', strtotime($date)),
            'MAIN_USER' => $id,
            'TASK_OBJECT' => $contact,
            'ELEMENT_TYPE' => 1,
            'TASK_TYPE' => $type
        );

        return $this->call('/private/api/task_add.php', $params);
    }

    /**
     * Добавление задачи связанной со сделкой в amoCRM
     *
     * @param integer $id ID контакта исполнителя
     * @param integer $deal ID контакта связанного
     * @param string $message Текст задачи
     * @param string $date Дата выполнения задачи
     * @param strung $type Тип задачи (CALL / LETTER / MEETING)
     * @return mixed
     * @throws CException
     */
    public function addDealTask($id, $deal, $message, $date, $type)
    {
        $params = array(
            'ACTION' => 'ADD_TASK',
            'BODY' => $message,
            'END_DATE' => date('d.m.Y H:i:s', strtotime($date)),
            'MAIN_USER' => $id,
            'TASK_OBJECT' => $deal,
            'ELEMENT_TYPE' => 2,
            'TASK_TYPE' => $type
        );

        return $this->call('/private/api/task_add.php', $params);
    }

    /**
     * Редактирование простой задачи в amoCRM
     *
     * @param integer $task ID задачи
     * @param integer $id ID контакта исполнителя
     * @param string $message Текст задачи
     * @param string $date Дата выполнения задачи
     * @param strung $type Тип задачи (CALL / LETTER / MEETING)
     * @return mixed
     * @throws CException
     */
    public function editTask($task, $id, $message, $date, $type)
    {
        $params = array(
            'ID' => $task,
            'ACTION' => 'EDIT_TASK',
            'BODY' => $message,
            'END_DATE' => date('d.m.Y H:i:s', strtotime($date)),
            'MAIN_USER' => $id,
            'TASK_TYPE' => $type
        );

        return $this->call('/private/api/task_add.php', $params);
    }

    /**
     * Редактирование задачи связанной с контактом в amoCRM
     *
     * @param integer $task ID задачи
     * @param integer $id ID контакта исполнителя
     * @param integer $contact ID контакта связанного
     * @param string $message Текст задачи
     * @param string $date Дата выполнения задачи
     * @param strung $type Тип задачи (CALL / LETTER / MEETING)
     * @return mixed
     * @throws CException
     */
    public function editContactTask($task, $id, $contact, $message, $date, $type)
    {
        $params = array(
            'ID' => $task,
            'ACTION' => 'EDIT_TASK',
            'BODY' => $message,
            'END_DATE' => date('d.m.Y H:i:s', strtotime($date)),
            'MAIN_USER' => $id,
            'TASK_OBJECT' => $contact,
            'ELEMENT_TYPE' => 1,
            'TASK_TYPE' => $type
        );

        return $this->call('/private/api/task_add.php', $params);
    }

    /**
     * Редактирование задачи связанной со сделкой в amoCRM
     *
     * @param integer $task ID задачи
     * @param integer $id ID контакта исполнителя
     * @param integer $deal ID контакта связанного
     * @param string $message Текст задачи
     * @param string $date Дата выполнения задачи
     * @param strung $type Тип задачи (CALL / LETTER / MEETING)
     * @return mixed
     * @throws CException
     */
    public function editDealTask($task, $id, $deal, $message, $date, $type)
    {
        $params = array(
            'ID' => $task,
            'ACTION' => 'EDIT_TASK',
            'BODY' => $message,
            'END_DATE' => date('d.m.Y H:i:s', strtotime($date)),
            'MAIN_USER' => $id,
            'TASK_OBJECT' => $deal,
            'ELEMENT_TYPE' => 2,
            'TASK_TYPE' => $type
        );

        return $this->call('/private/api/task_add.php', $params);
    }

    /**
     * Удаление задачи из amoCRM
     *
     * @param integer $task ID задачи
     * @return mixed
     * @throws CException
     */
    public function deleteTask($task)
    {
        $params = array(
            'ID' => $task,
            'ACTION' => 'TASK_DELETE'
        );

        return $this->call('/private/api/task_add.php', $params);
    }

    /**
     * Выполнение задачи в amoCRM
     *
     * @param integer $task ID задачи
     * @return mixed
     * @throws CException
     */
    public function completeTask($task)
    {
        $params = array(
            'ID' => $task,
            'ACTION' => 'COMPLATE_TASK'
        );

        return $this->call('/private/api/task_add.php', $params);
    }

    /**
     * Обращение к API amoCRM
     *
     * @param string $url
     * @param array $params
     * @param bool $raw
     * @return mixed
     * @throws CException
     */
    private function call($url, $params = array(), $raw = false)
    {
        $this->lastError = null;

        $params['USER_LOGIN'] = $this->login;

        if ($this->hash !== null) {
            $params['USER_HASH'] = $this->hash;
        } elseif ($this->password !== null) {
            $params['USER_PASSWORD'] = $this->password;
        } else {
            throw new CException('User Password or Hash are required to authorize.');
        }

        $url = 'https://' . $this->subdomain . '.amocrm.ru' . $url;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        switch ($code) {
            case '301':
                $this->lastError = 'Ошибка. Запрошенный документ был окончательно перенесен.';
                break;
            case '400':
                $this->lastError = 'Ошибка. Сервер обнаружил в запросе клиента синтаксическую ошибку.';
                break;
            case '401':
                $this->lastError = 'Ошибка. Запрос требует идентификации пользователя.';
                break;
            case '403':
                $this->lastError = 'Ошибка. Ограничение в доступе к указанному ресурсу.';
                break;
            case '404':
                $this->lastError = 'Ошибка. Страница не найдена.';
                break;
            case '500':
                $this->lastError = 'Внутрення ошибка сервера.';
                break;
            case '502':
                $this->lastError = 'Ошибка. Неудачное выполнение.';
                break;
            case '503':
                $this->lastError = 'Ошибка. Сервер временно недоступен.';
                break;
            default:
                $this->lastError = 'Ошибка авторизации. Пожалуйста, проверьте введённые данные.';
        }

        if ($code != 200) {
            $this->lastErrorNo = $code;
            throw new CException($this->lastError, $this->lastErrorNo);
        }

        if ($raw === false) {
            $xml = simplexml_load_string($result);
            $result = @json_decode(@json_encode($xml), 1);
        }

        return $result;
    }
}
