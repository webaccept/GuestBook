<?php

class ClassMain
{
    public function __construct()
    {

    }

    /**
     * @param string $Action
     * @param null $data
     * @throws Exception
     */
    public function IncludeTemplate($Action = '', $data = null)
    {
        if (is_file($file = PATH.'/tpl/'.$Action.'.php')){
            include $file;
        } else {
            throw new Exception('Template /'.$Action.'.php'.' not exist');
        }
    }

    /**
     * @param $Param
     */
    public function ajaxSet($Param)
    {
        $action       = !empty($Param['action'])? $Param['action'] : '';
        $is_login     = !empty($Param['auth'])? $Param['auth'] : 0;
        $id = (isset($Param['id']) && $Param['id'] != 'undefined') ? $Param['id'] : 0;
        $parent_id = (isset($Param['parent_id']) && $Param['parent_id'] != 'undefined') ? $Param['parent_id'] : 0;
        $publish_date = !empty($Param['publish_date'])? $Param['publish_date'] : '';
        $message      = !empty($Param['message'])? $Param['message'] : '';

        switch ($action)
        {
            case 'auth':
                $login = 'admin';
                $password = 'pass';
                if ($Param['login'] != $login || $Param['password'] != $password) {
                    echo 'false';
                }
                break;
            case 'count':
                echo self::CountXMLElements();
                break;
            case 'read':
                self::ReadXMLFile($is_login, $id, $parent_id);
                break;
            case 'new_message':
                self::AddNewMessage($id, $parent_id, $publish_date, $message);
                break;
            case 'edit_message':
                self::EditMessage($is_login, $id, $parent_id, $publish_date, $message);
                break;
            default:
                break;
        }

        die;
    }

    /**
     * @param $is_login - флаг авторизации
     * @param null $id - id записи
     * @param int $parent_id - id родительской записи
     * @throws Exception
     */
    public function ReadXMLFile ($is_login, $id = 0, $parent_id = 0)
    {
        $doc = new DOMDocument();
        $doc->validateOnParse = true;
        if (@$doc->loadHTMLFile(GB) === false) {
            throw new Exception('Cound\'t load file: "'.GB.'"!');
        }
        $doc->preserveWhiteSpace = false;

        $data = [];
        $data['is_login'] = !empty($is_login)? $is_login : 0;
        $id = (isset($id) && $id != 'undefined') ? $id : 0;
        $parent_id = (isset($parent_id) && $parent_id != 'undefined') ? $parent_id : 0;

        if (empty($id) && empty($parent_id)) {
            $books = $doc->getElementsByTagName("book");
            foreach ($books as $book) {
                $data['id'] = $book->getAttribute("id");
                $data['parent_id'] = $book->getAttribute("parent_id");

                $publishers = $book->getElementsByTagName("publish_date");
                $data['publish_date'] = $publishers->item(0)->nodeValue;

                $messages = $book->getElementsByTagName("message");
                $data['message'] = $messages->item(0)->nodeValue;
                if ($data['parent_id'] == 0) {
                    $this->IncludeTemplate('list', $data);
                }
            }
        } else {
            $book = $doc->getElementById($id);

            $data['id'] = $book->getAttribute("id");
            $data['parent_id'] = $book->getAttribute("parent_id");

            $publishers = $book->getElementsByTagName("publish_date");
            $data['publish_date'] = $publishers->item(0)->nodeValue;

            $messages = $book->getElementsByTagName("message");
            $data['message'] = $messages->item(0)->nodeValue;

            echo json_encode($data);
        }
    }

    public function SubReadXML($is_login, $id = 0, $parent_id = 0)
    {
        $doc = new DOMDocument();
        $doc->validateOnParse = true;
        if (@$doc->loadHTMLFile(GB) === false) {
            throw new Exception('Cound\'t load file: "' . GB . '"!');
        }
        $doc->preserveWhiteSpace = false;

        $data = [];
        $data['is_login'] = !empty($is_login) ? $is_login : 0;
        $id = (isset($id) && $id != 'undefined') ? $id : 0;
        $parent_id = (isset($parent_id) && $parent_id != 'undefined') ? $parent_id : 0;

        if (!empty($id)) {
            $books = $doc->getElementsByTagName("book");
            foreach ($books as $book) {
                $data['id'] = $book->getAttribute("id");
                $data['parent_id'] = $book->getAttribute("parent_id");

                $publishers = $book->getElementsByTagName("publish_date");
                $data['publish_date'] = $publishers->item(0)->nodeValue;

                $messages = $book->getElementsByTagName("message");
                $data['message'] = $messages->item(0)->nodeValue;

                if ($data['parent_id'] == $id) {
                    $this->IncludeTemplate('list', $data);
                }
            }
        }
    }

    public function EditMessage($is_login, $id, $parent_id, $publish_date, $message)
    {
        if (isset($is_login) && $is_login == 1) {

            $doc = new DOMDocument();
            $doc->validateOnParse = true;
            if (@$doc->load(GB) === false) { // loadHTMLFile
                throw new Exception('Cound\'t load file: "' . GB . '"!');
            }
            $xp = new DomXPath($doc);
            $book = $xp->query("//*[@id = '{$id}']");

            $oldmessages = $book->item(0)->getElementsByTagName("message");
            $oldmessages->item(0)->firstChild->nodeValue = $message;

            $doc->save(PATH . '/upload/gb.xml');
        }
    }

    /**
     * @param $id
     * @param $parent_id
     * @param $publish_date
     * @param $message
     * @throws Exception
     */
    public function AddNewMessage($id, $parent_id, $publish_date, $message)
    {
        $doc = new DOMDocument();
        if (@$doc->load(GB) === false) {
            throw new Exception('Cound\'t load file: "'.GB.'"!');
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

        $doc->save(PATH.'/upload/gb.xml');
    }

    /**
     * @return int
     * @throws Exception
     */
    public function CountXMLElements()
    {
        $doc = new DOMDocument();
        if (@$doc->load(GB) === false) {
            throw new Exception('Cound\'t load file: "'.GB.'"!');
        }

        return $doc->getElementsByTagName('book')->length;
    }
}

/**
 * Отладочная функция
 * @param $array
 */
function pa($array)
{
    $args = func_get_args();
    if(count($args) > 1) {
        foreach ($args as $values) {
            pa($values);
        }
    } else {
        if (is_array($array) || is_object($array)) {
            echo "<pre>";
            print_r($array);
            echo "</pre>";
        } else {
            echo $array;
        }
    }
}