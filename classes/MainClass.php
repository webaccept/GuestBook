<?php

use JetBrains\PhpStorm\NoReturn;

class MainClass
{
    /**
     * Действия - своеобразный контроллер
     *
     * @param mixed $param
     * @return void
     * @throws Exception
     * @throws DOMException
     */
    #[NoReturn] public function ajaxSet(mixed $param): void
    {
        $action = !empty($param['action']) ?  $param['action'] : '';
        $is_login = !empty($param['auth']) ? filter_var($param['auth'], FILTER_VALIDATE_BOOL) : false;
        $id = !empty($param['id']) ? filter_var($param['id'], FILTER_VALIDATE_INT) : 0;
        $parent_id = !empty($param['parent_id']) ? filter_var($param['parent_id'], FILTER_VALIDATE_INT) : 0;
        $publish_date = !empty($param['publish_date']) ? $param['publish_date'] : '';
        $message = !empty($param['message']) ? $param['message'] : '';

        switch ($action) {
            case 'auth':
                $login = 'admin';
                $password = md5('pass'); // Смысла нет в md5(), но все же...
                if ($param['login'] === $login && md5($param['password']) === $password) {
                    echo 'true';
                }
                break;
            case 'count':
                echo self::lastXMLElements();
                break;
            case 'read':
                self::readXMLFile($is_login, $id, $parent_id);
                break;
            case 'new_message':
                self::newMessage($id, $parent_id, $publish_date, $message);
                break;
            case 'edit_message':
                self::editMessage($is_login, $id, $message);
                break;
            default:
                break;
        }

        die;
    }

    /**
     * Подключение шаблона(ов)
     *
     * @param string $action
     * @param mixed|null $data
     * @return void
     * @throws Exception
     */
    public function IncludeTemplate(string $action = '', mixed $data = null): void
    {
        if (is_file($file = PATH . '/public/layouts/' . $action . '.php')) {
            include $file;
        } else {
            throw new Exception('Template /' . $action . '.php' . ' not exist');
        }
    }

    /**
     * Читаем файл с записями гостевой книги или запись для редактирования
     *
     * @param bool $is_login
     * @param int $id
     * @param int $parent_id
     * @return void
     * @throws Exception
     */
    public function readXMLFile(bool $is_login = false, int $id = 0, int $parent_id = 0): void
    {
        $doc = new DOMDocument();
        $doc->validateOnParse = true;
        if (@$doc->loadHTMLFile(GB) === false) {
            throw new Exception('Cound\'t load file: "' . GB . '"!');
        }
        $doc->preserveWhiteSpace = false;

        $data = [];
        $data['is_login'] = filter_var($is_login, FILTER_VALIDATE_BOOLEAN);

        if (empty($id) && empty($parent_id)) {
            $books = $doc->getElementsByTagName("book");

            foreach ($books as $book) {
                $data['id'] = $book->getAttribute("id");
                $data['parent_id'] = $book->getAttribute("parent_id");
                $data['publish_date'] = $book->getElementsByTagName("publish_date")->item(0)->nodeValue;
                $data['message'] = $book->getElementsByTagName("message")->item(0)->nodeValue;
                if ($data['parent_id'] == 0) {
                    $this->IncludeTemplate('list', $data);
                }
            }

            if (count($books) <= 0) {
                echo 'Записей ещё нет. Станьте первым! :)';
            }
        } else {
            $book = $doc->getElementById($id);

            $data['id'] = $book->getAttribute("id");
            $data['parent_id'] = $book->getAttribute("parent_id");
            $data['publish_date'] = $book->getElementsByTagName("publish_date")->item(0)->nodeValue;
            $data['message'] = $book->getElementsByTagName("message")->item(0)->nodeValue;

            echo json_encode($data);
        }
    }

    /**
     * Вывод ответа(ов) записи
     *
     * @param bool $is_login
     * @param int $id
     * @param int $parent_id
     * @return void
     * @throws Exception
     */
    public function SubReadXML(bool $is_login, int $id = 0, int $parent_id = 0): void
    {
        $doc = new DOMDocument();
        $doc->validateOnParse = true;
        if (@$doc->loadHTMLFile(GB) === false) {
            throw new Exception('Cound\'t load file: "' . GB . '"!');
        }
        $doc->preserveWhiteSpace = false;

        $data = [];
        $data['is_login'] = $is_login;

        if (!empty($id)) {
            $books = $doc->getElementsByTagName("book");

            foreach ($books as $book) {
                $data['id'] = $book->getAttribute("id");
                $data['parent_id'] = $book->getAttribute("parent_id");
                $data['publish_date'] = $book->getElementsByTagName("publish_date")->item(0)->nodeValue;
                $data['message'] = $book->getElementsByTagName("message")->item(0)->nodeValue;

                if (filter_var($data['parent_id'], FILTER_VALIDATE_INT) === $id) {
                    $this->IncludeTemplate('list', $data);
                }
            }
        }
    }

    /**
     * Редактирование существующей записи
     *
     * @param bool $is_login
     * @param int $id
     * @param string $message
     * @return void
     * @throws Exception
     */
    public function editMessage(bool $is_login, int $id, string $message): void
    {
        if (!!$is_login) {

            $doc = new DOMDocument();
            $doc->validateOnParse = true;
            if (@$doc->load(GB) === false) { // loadHTMLFile
                throw new Exception('Cound\'t load file: "' . GB . '"!');
            }
            $xp = new DomXPath($doc);
            $book = $xp->query("//*[@id = '{$id}']");

            $oldmessages = $book->item(0)->getElementsByTagName("message");
            $oldmessages->item(0)->firstChild->nodeValue = $message;

            $doc->save(GB);
        }
    }

    /**
     * Добавляем новую запись
     *
     * @param int $id
     * @param int $parent_id
     * @param string $publish_date
     * @param string $message
     * @return void
     * @throws Exception
     * @throws DOMException
     */
    public function newMessage(int $id, int $parent_id, string $publish_date, string $message): void
    {
        $doc = new DOMDocument();
        if (@$doc->load(GB) === false) {
            throw new Exception('Cound\'t load file: "' . GB . '"!');
        }

        $xpath = new DOMXPath($doc);
        $parent = $xpath->query('//catalog');
        $next = $xpath->query('//catalog/book');

        $new_book = $doc->createElement('book');

        $new_id = $doc->createAttribute("id");
        $new_parent_id = $doc->createAttribute("parent_id");
        $new_publish_date = $doc->createElement('publish_date', $publish_date);
        $new_message = $doc->createElement('message', $message);

        $new_book->appendChild($new_id);
        $new_book->appendChild($new_parent_id);

        $node_id = $doc->createTextNode($id);
        $new_id->appendChild($node_id);
        $new_book->setIdAttribute("id", true);

        $node_parent_id = $doc->createTextNode($parent_id);
        $new_parent_id->appendChild($node_parent_id);
        $new_book->setIdAttribute("parent_id", true);

        $new_book->appendChild($new_publish_date);
        $new_book->appendChild($new_message);
        $parent->item(0)->insertBefore($new_book, $next->item(0));

        $doc->save(GB);
    }

    /**
     * Получаем id последней записи
     *
     * @return int
     * @throws Exception
     */
    public function lastXMLElements(): int
    {
        $doc = new DOMDocument();
        if (@$doc->load(GB) === false) {
            throw new Exception('Cound\'t load file: "' . GB . '"!');
        }

        return $doc->getElementsByTagName('book')->length;
    }
}

/**
 * Отладочная функция
 *
 * @param $array
 * @return void
 */
function pa($array): void
{
    $args = func_get_args();
    if (count($args) > 1) {
        foreach ($args as $values) {
            pa($values);
        }
    } else {
        if (is_array($array) || is_object($array)) {
            echo '<pre>';
            print_r($array);
            echo '</pre>';
        } else {
            echo $array;
        }
    }
}